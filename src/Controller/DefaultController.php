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

        return $this->render('default/front/Service/list.html.twig', [

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
        $perPage = 20; // Number of items per page
        $page = max(1, $request->query->getInt('page', 1)); // Get page number
        $search = $request->query->get('search', ''); // Get search term

        // Build the query for articles
        $queryBuilder = $entityManager->getRepository(\App\Entity\Article::class)
            ->createQueryBuilder('p')
            ->where('p.published = 1')
            ->andWhere('p.type = :type')
            ->setParameter('type', 1);

        // Add search filter if a term is provided
        if (!empty($search)) {
            $queryBuilder->andWhere('p.title LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        // Apply pagination
        $queryBuilder->setFirstResult(($page - 1) * $perPage)
                     ->setMaxResults($perPage);

        // Get paginated results
        $query = $queryBuilder->getQuery();
        $paginator = new Paginator($query, true);
        $totalArticles = count($paginator);
        $totalPages = ceil($totalArticles / $perPage);

        // Render the template
        return $this->render('default/front/Article/list.html.twig', [
            'articles' => $paginator,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search, // Pass search term to the template
        ]);
    }

    
    #[Route('/articles/{url}', name: 'article_show')]
    public function article(EntityManagerInterface $entityManager, string $url): Response
    {
        $article = $entityManager->getRepository(\App\Entity\Article::class)->findOneBy(['url' => $url])
            ?: throw $this->createNotFoundException('The service does not exist');

        return $this->render('default/front/Article/Show.html.twig', compact('article'));
    }      
    
    #[Route('/about-me', name: 'app_about')]
    public function about(): Response
    {
        
        return $this->render('default/front/about.html.twig');
    }
    
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contact = new \App\Entity\ContactUs(); // Note the backslash at the beginning
        
        $form = $this->createForm(\App\Form\ContactUsType::class, $contact);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all();

            $entityManager->persist($contact);
            $entityManager->flush();

            return $this->redirectToRoute('app_contact', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('default/front/contact.html.twig', [
          'form' => $form->createView(),
        ]);  
    }
    
}
