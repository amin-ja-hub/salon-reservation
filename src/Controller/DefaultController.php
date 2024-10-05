<?php

namespace App\Controller;


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
    public function Services(EntityManagerInterface $entityManager, Request $request): Response
    {
        $perPage = 20; // Define the number of items to display per page
                
        $page = max(1, $request->query->getInt('page', 1));
        $query = $entityManager->getRepository(\App\Entity\Service::class)
            ->createQueryBuilder('p')
            ->where('p.published = 1')
            ->andWhere('p.type = :type')
            ->setParameter('type', 1)
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery();


        $paginator = new Paginator($query, true);
        $totalPages = ceil(count($paginator));

        return $this->render('default/front/Service/list.html.twig', [
            'services' => $paginator, // Change 'paginator' to 'services'
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }

    #[Route('/services/{url}', name: 'service_show')]
    public function service(EntityManagerInterface $entityManager, string $url): Response
    {
        $service = $entityManager->getRepository(\App\Entity\Service::class)->findOneBy(['url' => $url])
            ?: throw $this->createNotFoundException('The service does not exist');

        return $this->render('default/front/Service/Show.html.twig', compact('service'));
    }  

    #[Route('/articles', name: 'articles_show')]
    public function Articles(EntityManagerInterface $entityManager, Request $request): Response
    {
        $perPage = 20; // Define the number of items to display per page
        $page = max(1, $request->query->getInt('page', 1)); // Ensure valid page number

        // Build query to get paginated articles
        $query = $entityManager->getRepository(\App\Entity\Article::class)
            ->createQueryBuilder('p')
            ->where('p.published = 1')
            ->andWhere('p.type = :type')
            ->setParameter('type', 1)
            ->setFirstResult(($page - 1) * $perPage) // Calculate offset
            ->setMaxResults($perPage) // Set max results per page
            ->getQuery();

        // Use Paginator to paginate results
        $paginator = new Paginator($query, true);
        $totalArticles = count($paginator); // Total number of articles
        $totalPages = ceil($totalArticles / $perPage); // Calculate total pages

        // Pass articles and pagination data to Twig template
        return $this->render('default/front/Article/list.html.twig', [
            'articles' => $paginator, // Pass paginated articles
            'totalPages' => $totalPages, // Total number of pages
            'currentPage' => $page, // Current page number
        ]);
    }
    
    #[Route('/articles/{url}', name: 'article_show')]
    public function article(EntityManagerInterface $entityManager, string $url): Response
    {
        $article = $entityManager->getRepository(\App\Entity\Article::class)->findOneBy(['url' => $url])
            ?: throw $this->createNotFoundException('The service does not exist');

        return $this->render('default/front/Article/Show.html.twig', compact('article'));
    }      
    
    #[Route('/about-me', name: 'app-about')]
    public function about(): Response
    {
        
        return $this->render('default/front/about.html.twig');
    }
    
}
