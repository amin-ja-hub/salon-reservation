<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Barchasb;
use App\Service\Service;

#[Route('/admin/category')]
class CategoryController extends AbstractController
{
    
    #[Route('/', name: 'app_category_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Fetch the search query from the request
        $searchQuery = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1); // Current page from the query string
        $limit = 15; // Limit of categories per page

        // Build the query to search for categories
        $queryBuilder = $entityManager->getRepository(Category::class)->createQueryBuilder('a')
            ->where('(a.remove IS NULL OR a.remove = 0)');

        // If a search query exists, filter by the category title
        if ($searchQuery) {
            $queryBuilder->andWhere('a.title LIKE :search')
                ->setParameter('search', '%' . $searchQuery . '%');
        }

        // Get the total number of categories matching the query
        $totalCategories = count($queryBuilder->getQuery()->getResult());
        $totalPages = ceil($totalCategories / $limit);
        $offset = ($page - 1) * $limit;

        // Fetch the categories for the current page, ordered in reverse (newest first)
        $categories = $queryBuilder
            ->orderBy('a.id', 'DESC') // Reverse order by category ID
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
            'searchQuery' => $searchQuery, // Pass the search query back to the template
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,Service $service): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        // Initialize $barchasbs variable
        $barchasbs = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all();

            // Handle required fields like title, publish, metadesc, text, etc.
            $category->setTitle($formData['title']);
            $category->setMetadesc($formData['metadesc']);
            $category->setCdate(new \DateTime());
            $category->setType($formData['categorytype']);
            $category->setUrl($formData['url']);


            // Handle optional fields like category
            if (isset($formData['category1'])) {
                $categoryid = $entityManager->getRepository('App\Entity\Category')->find($formData['category1']);
                $category->setParent($categoryid);
            }


            // Check if 'keywords' exists in $formData
            if (isset($formData['keywords'])) {
                $tags = $formData['keywords'];

                // Handle tags (keywords)
                foreach ($tags as $tagName) {
                    $barchasbRepository = $entityManager->getRepository(Barchasb::class);
                    $barchasb = $barchasbRepository->findOneBy(['title' => $tagName]);

                    if (!$barchasb) {
                        $barchasb = new Barchasb();
                        $barchasb->setTitle($tagName);
                        $barchasb->setCdate(new \DateTime());
                        $barchasb->setPublished(1);
                        $barchasb->setType(3);
                        $entityManager->persist($barchasb);
                    }

                    $category->addBarchasb($barchasb);
                }
            }

            // Persist the category entity after handling keywords and file upload
            $entityManager->persist($category);
            $entityManager->flush();
            $entityId = $category->getId();
            if ($request->files->get('file') != null) {
                $file = $request->files->get('file');
                $fileId = $service->uploadFile(2, $file, $entityId, 'mainpic');
                $file = $entityManager->getRepository('App\Entity\File')->find($fileId);
                $category->setImage($file);
                $entityManager->persist($category);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        // Fetch Barchasb entities where type = 1
        $barchasbs = $entityManager->getRepository(Barchasb::class)->findBy(['type' => 3]);

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
            'barchasbs' => $barchasbs,

        ]);
    }

    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, Service $service, int $id): Response
    {
        // Fetch the existing category by ID
        $category = $entityManager->getRepository(Category::class)->find($id);
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        // Initialize $barchasbs variable
        $barchasbs = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all();

            // Handle required fields like title, publish, metadesc, text, etc.
            $category->setTitle($formData['title']);
            $category->setMetadesc($formData['metadesc']);
            $category->setCdate(new \DateTime());
            $category->setType($formData['categorytype']);
            $category->setUrl($formData['url']);

            // Handle optional fields like category
            if (isset($formData['category1'])) {
                $categoryid = $entityManager->getRepository(Category::class)->find($formData['category1']);
                $category->setParent($categoryid);
            }

            // Check if 'keywords' exists in $formData
            if (isset($formData['keywords'])) {
                $tags = $formData['keywords'];
                    foreach ($category->getBarchasbs() as $barchasb) {
                        $category->removeBarchasb($barchasb);
                    }
                // Handle tags (keywords)
                foreach ($tags as $tagName) {
                    $barchasbRepository = $entityManager->getRepository(Barchasb::class);
                    $barchasb = $barchasbRepository->findOneBy(['title' => $tagName]);

                    if (!$barchasb) {
                        $barchasb = new Barchasb();
                        $barchasb->setTitle($tagName);
                        $barchasb->setCdate(new \DateTime());
                        $barchasb->setPublished(1);
                        $barchasb->setType(3);
                        $entityManager->persist($barchasb);
                    }

                    $category->addBarchasb($barchasb);
                }
            }

            // Persist the category entity after handling keywords and file upload
            $entityManager->persist($category);
            $entityManager->flush();
            $entityId = $category->getId();
            if ($request->files->get('file') != null) {
                $file = $request->files->get('file');
                $fileId = $service->uploadFile(2, $file, $entityId, 'mainpic');
                $file = $entityManager->getRepository('App\Entity\File')->find($fileId);
                $category->setImage($file);
                $entityManager->persist($category);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        // Fetch Barchasb entities where type = 1
        $barchasbs = $entityManager->getRepository(Barchasb::class)->findBy(['type' => 3]);

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
            'barchasbs' => $barchasbs,
        ]);
    }

    #[Route('/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
