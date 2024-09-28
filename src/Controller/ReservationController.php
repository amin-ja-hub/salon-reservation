<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\WorkSchedule;
use App\Entity\DefaultWorkSchedule;
use Morilog\Jalali\Jalalian;
use App\Entity\Service;
use App\Entity\User;

#[Route('/')]
final class ReservationController extends AbstractController
{
    #[Route('admin/reservation', name: 'app_reservation_admin', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $searchTerm = $request->query->get('search');

        $query = $entityManager->createQuery(
            'SELECT r FROM App\Entity\Reservation r
             JOIN r.user u
             WHERE (u.username LIKE :searchTerm 
             OR u.fullName LIKE :searchTerm)
             OR (r.reservationDateTime LIKE :searchTerm)'
        )->setParameter('searchTerm', '%' . $searchTerm . '%');

        $reservations = $query->getResult();

        return $this->render('reservation/admin.html.twig', [
            'reservations' => $reservations,
            'searchTerm' => $searchTerm
        ]);
    }

    #[Route('personnel/reservation', name: 'app_reservation_personnel', methods: ['GET'])]
    public function indexpersonnel(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Get the current user's ID
        $userId = $this->getUser()->getId();

        // Get the search term from the query string
        $searchTerm = $request->query->get('search', '');

        // If searchTerm is provided, add the search to the query
        if (!empty($searchTerm)) {
            // Create the query with parameters for search and userId
            $query = $entityManager->createQuery(
                'SELECT r FROM App\Entity\Reservation r
                 JOIN r.user u
                 JOIN r.personal c
                 WHERE (u.username LIKE :searchTerm 
                 OR u.fullName LIKE :searchTerm 
                 OR r.reservationDateTime LIKE :searchTerm)
                 AND c.id = :userId'
            )
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->setParameter('userId', $userId);

            // Execute the query and get results
            $reservations = $query->getResult();
        } else {
            // If no search term, fetch all reservations for the current personnel
            $reservations = $entityManager
                ->getRepository(Reservation::class)
                ->findBy(['personal' => $userId]);
        }

        // Debug output for testing

        // Render the template with reservations
        return $this->render('reservation/admin.html.twig', [
            'reservations' => $reservations,
            'searchTerm' => $searchTerm
        ]);
    }

    #[Route('user/reservation', name: 'app_reservation_user', methods: ['GET'])]
    public function indexuser(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Get the current user's ID
        $userId = $this->getUser()->getId();

        // Get the search term from the query string, default to an empty string if not provided
        $searchTerm = $request->query->get('search', '');

        if (!empty($searchTerm)) {
            // If searchTerm is provided, create a dynamic query using DQL
$query = $entityManager->createQuery(
    'SELECT r FROM App\Entity\Reservation r
     JOIN r.user u
     JOIN r.service s
     JOIN r.serviceChild sc
     WHERE (r.reservationDateTime LIKE :searchTerm
     OR s.title LIKE :searchTerm
     OR sc.title LIKE :searchTerm)
     AND u.id = :userId'
)
->setParameter('searchTerm', '%' . $searchTerm . '%')
->setParameter('userId', $userId);


            // Execute the query to get search results
            $reservations = $query->getResult();
        } else {
            // If no search term, fetch all reservations for the current user
            $reservations = $entityManager
                ->getRepository(Reservation::class)
                ->findBy(['user' => $userId]);
        }

        // Render the template with the reservations
        return $this->render('reservation/admin.html.twig', [
            'reservations' => $reservations,
            'searchTerm' => $searchTerm
        ]);
    }

    #[Route('admin/reservation/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }
    
    #[Route('admin/reservation/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('admin/reservation/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('admin/reservation/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('reserve', name: 'reserve')]
    public function reserve() {
        return $this->render('default/front/reserve.html.twig');
    }
    
    #[Route('child-services', name: 'ajax_child_services', methods: ['POST'])]
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

    #[Route('users-by-service', name: 'ajax_users_by_service', methods: ['POST'])]
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
    
    #[Route('available-times', name: 'ajax_available_times', methods: ['POST'])]
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
