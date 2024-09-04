<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $parentService = $em->getRepository(Article::class)->find($parentId);

        if (!$parentService) {
            return new JsonResponse(['error' => 'Parent service not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $childServices = $em->getRepository(Article::class)->findBy(['parent' => $parentService]);

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
        $users = $em->getRepository(User::class)->findBy(['service' => $serviceId]);

        $data = array_map(fn($user) => [
            'id' => $user->getId(),
            'fullName' => $user->getFullName()
        ], $users);

        return new JsonResponse($data);
    }
#[Route('/ajax/check-date-availability', name: 'ajax_check_date_availability', methods: ['POST'])]
public function checkDateAvailability(Request $request): JsonResponse
{
    $date = $request->request->get('date');

    // Convert the Jalali date to Gregorian for comparison (if necessary)
    // Perform date availability check (e.g., from database or a predefined list)
    $availableDates = ['2024-09-05', '2024-09-06']; // Example available dates

    $isAvailable = in_array($date, $availableDates);

    return new JsonResponse(['available' => $isAvailable]);
}


    
}
