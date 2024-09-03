<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
}
