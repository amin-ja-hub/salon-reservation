<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Service;
use App\Entity\User;
use App\Entity\Reservation;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\WorkSchedule;
use App\Entity\DefaultWorkSchedule;
use Morilog\Jalali\Jalalian;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('base.front.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    
    #[Route('/services', name: 'services_show')]
    public function Articles(EntityManagerInterface $entityManager, Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $query = $entityManager->getRepository(\App\Entity\Article::class)
            ->createQueryBuilder('p')
            ->where('p.published = 1')
            ->andWhere('p.type = :type')
            ->setParameter('type', 2)
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery();


        $paginator = new Paginator($query, true);
        $totalPages = ceil(count($paginator));

        return $this->render('default/front/service/list.html.twig', [
            'services' => $paginator, // Change 'paginator' to 'services'
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }

    #[Route('/services/{url}', name: 'service_show')]
    public function service(EntityManagerInterface $entityManager, string $url): Response
    {
        $service = $entityManager->getRepository(\App\Entity\Article::class)->findOneBy(['url' => $url])
            ?: throw $this->createNotFoundException('The service does not exist');

        return $this->render('default/front/Service/Show.html.twig', compact('service'));
    }  
    #[Route('/reserve', name: 'reserve')]
    public function reserve() {
        return $this->render('default/front/reserve.html.twig');
    }
    
    #[Route('/ajax/child-services', name: 'ajax_child_services', methods: ['POST'])]
    public function getChildServices(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $parentId = $request->request->get('parentId');
        $parentService = $em->getRepository(Service::class)->find($parentId);

        if (!$parentService) {
            return new JsonResponse(['error' => 'Parent service not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $childServices = $em->getRepository(Service::class)->findBy(['parent' => $parentService]);

        $data = array_map(fn($service) => [
            'id' => $service->getId(),
            'title' => $service->getTitle()
        ], $childServices);

        return new JsonResponse($data);
    }

    #[Route('/ajax/users-by-service', name: 'ajax_users_by_service', methods: ['POST'])]
    public function getUsersByService(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $serviceId = $request->request->get('serviceId');

        // Find the service first
        $service = $em->getRepository(Service::class)->find($serviceId);

        if (!$service) {
            return new JsonResponse(['error' => 'Service not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Query users that are associated with this service and have role 2 (ROLE_PERSONNEL)
        $users = $em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->join('u.services', 's') // Join with the services
            ->where('s.id = :serviceId')
            ->andWhere('u.role = :role')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('role', 2) // Role 2 corresponds to ROLE_PERSONNEL
            ->getQuery()
            ->getResult();

        // Transform the users into a simple array
        $data = array_map(fn($user) => [
            'id' => $user->getId(),
            'fullName' => $user->getFullName()
        ], $users);

        return new JsonResponse($data);
    }
    
    #[Route('/ajax/available-times', name: 'ajax_available_times', methods: ['POST'])]
    public function getAvailableTimes(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $personalId = $request->request->get('personalId');
        $now = new \DateTime('now', new \DateTimeZone('Asia/Tehran'));

        // Find reservations for the next 16 days for the specific personalId
        $reservations = $em->getRepository(Reservation::class)->createQueryBuilder('r')
            ->where('r.personal = :personalId')
            ->andWhere('r.reservationDateTime LIKE :dateRange')
            ->setParameter('personalId', $personalId)
            ->setParameter('dateRange', '1403%')  // Adjust the Jalali year here
            ->getQuery()
            ->getResult();

        // Parse reserved times and store them by date
        $reservedTimes = [];
        foreach ($reservations as $reservation) {
            $reservationTimes = $reservation->getReservationDateTime();
            list($reservedDate, $reservedTimeRange) = explode(' ', $reservationTimes, 2);
            $reservedTimes[$reservedDate][] = $reservedTimeRange;
        }

        // Use custom work schedules if they exist
        $customSchedules = $em->getRepository(WorkSchedule::class)->findBy(['user' => $personalId]);

        // If custom schedules exist, prioritize them; else, use default schedules
        if (!$customSchedules) {
            $defaultSchedules = $em->getRepository(DefaultWorkSchedule::class)->findAll();
        }

        $currentDate = clone $now;
        $dayOfWeekMap = [
            0 => 'شنبه',
            1 => 'یکشنبه',
            2 => 'دوشنبه',
            3 => 'سه‌شنبه',
            4 => 'چهارشنبه',
            5 => 'پنجشنبه',
            6 => 'جمعه'
        ];

        $availableTimes = [];
        for ($i = 0; $i <= 16; $i++) {
            $jalaliDate = Jalalian::fromDateTime($currentDate);
            $jalaliDayOfWeek = $jalaliDate->getDayOfWeek(); // Get current day of the week (0-6)

            // Skip Friday (6)
            if ($jalaliDayOfWeek != 6) {
                $schedules = $customSchedules ?: $defaultSchedules;

                foreach ($schedules as $schedule) {
                    if ($dayOfWeekMap[$jalaliDayOfWeek] === $schedule->getDayOfWeek()) {
                        $startTime = (clone $currentDate)->setTime(
                            $schedule->getStartTime()->format('H'),
                            $schedule->getStartTime()->format('i')
                        );
                        $endTime = (clone $currentDate)->setTime(
                            $schedule->getEndTime()->format('H'),
                            $schedule->getEndTime()->format('i')
                        );

                        $jalaliStart = Jalalian::fromDateTime($startTime);
                        $jalaliEnd = Jalalian::fromDateTime($endTime);
                        $date = $jalaliStart->format('Y-m-d');
                        $timeRange = $jalaliStart->format('H:i') . ' - ' . $jalaliEnd->format('H:i');

                        // Only add future times and non-reserved slots to availableTimes
                        if (!isset($reservedTimes[$date]) || !in_array($timeRange, $reservedTimes[$date])) {
                            if ($startTime > $now) { // Skip past times
                                if (!isset($availableTimes[$date])) {
                                    $availableTimes[$date] = [
                                        'dayOfWeek' => $dayOfWeekMap[$jalaliDayOfWeek],
                                        'times' => []
                                    ];
                                }
                                $availableTimes[$date]['times'][] = $timeRange;
                            }
                        }
                    }
                }
            }

            $currentDate->modify('+1 day'); // Move to next day
        }

        // Return available times
        return new JsonResponse($availableTimes);
    }

}
