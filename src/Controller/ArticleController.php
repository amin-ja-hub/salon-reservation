<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Barchasb;
use App\Service\Service;
use Symfony\Component\HttpFoundation\File\UploadedFile; // Add this at the top of your controller if not already present

#[Route('/admin')]
class ArticleController extends AbstractController
{
    #[Route('/article/', name: 'app_article_index', methods: ['GET'])]
    public function Articleindex(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Fetch the search query from the request
        $searchQuery = $request->query->get('search', '');

        // Build the query to search for articles
        $queryBuilder = $entityManager->getRepository(Article::class)->createQueryBuilder('a')
            ->where('a.type = :type')
            ->andWhere('(a.remove IS NULL OR a.remove = 0)') // Apply the remove condition
            ->setParameter('type', 1);

        // If a search query exists, filter by the article title or content
        if ($searchQuery) {
            $queryBuilder->andWhere('a.title LIKE :search ')
                ->setParameter('search', '%' . $searchQuery . '%');
        }

        // Execute the query
        $articles = $queryBuilder->getQuery()->getResult();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'searchQuery' => $searchQuery // Pass the search query back to the template
        ]);
    }

    #[Route('/article/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Service $service): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all();
            $this->setArticleFields($article, $formData);
            $this->handleCategory($article, $formData['category'] ?? null, $entityManager);
            $this->handleTags($article, $formData['keywords'] ?? [], $entityManager,1);

            $entityManager->persist($article);
            $entityManager->flush();

            $this->handleFileUpload($article, $request->files->get('file'), $service, $entityManager);

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
            'barchasbs' => $this->getBarchasbs($entityManager, 1),
        ]);
    }

    #[Route('/article/edit/{id}', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Article $article, Request $request, EntityManagerInterface $entityManager, Service $service): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all();
            $this->setArticleFields($article, $formData);
            $this->handleCategory($article, $formData['category'] ?? null, $entityManager);
            $this->handleTags($article, $formData['keywords'] ?? [], $entityManager,1);

            $this->handleFileUpload($article, $request->files->get('file'), $service, $entityManager);

            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
            'barchasbs' => $this->getBarchasbs($entityManager, 1),
        ]);
    }

    // Private methods

    private function setArticleFields(Article $article, array $formData, string $type = '1'): void
    {
        $article->setTitle($formData['title']);
        $article->setUser($this->getUser());
        $article->setMetadesc($formData['metadesc']);
        $article->setText($formData['text']);
        $article->setUrl($formData['url']);
        $article->setCdate(new \DateTime());
        $article->setType($type);
    }

    private function handleCategory(Article $article, ?string $categoryId, EntityManagerInterface $entityManager): void
    {
        if ($categoryId) {
            $category = $entityManager->getRepository('App\Entity\Category')->find($categoryId);
            $article->setCategory($category);
        } else {
            $article->setCategory(null);
        }
    }

    private function handleTags(Article $article, array $tags, EntityManagerInterface $entityManager,$type): void
    {
        // Clear existing tags
        foreach ($article->getBarchasbs() as $barchasb) {
            $article->removeBarchasb($barchasb);
        }

        foreach ($tags as $tagName) {
            $barchasb = $entityManager->getRepository(Barchasb::class)->findOneBy(['title' => $tagName]);

            if (!$barchasb) {
                $barchasb = new Barchasb();
                $barchasb->setTitle($tagName);
                $barchasb->setCdate(new \DateTime());
                $barchasb->setPublished(1);
                $barchasb->setType($type);
                $entityManager->persist($barchasb);
            }

            $article->addBarchasb($barchasb);
        }
    }

    private function handleFileUpload(Article $article, ?UploadedFile $file, Service $service, EntityManagerInterface $entityManager): void
    {
        if ($file !== null) {
            $fileId = $service->uploadFile(1, $file, $article->getId(), 'mainpic');
            $fileEntity = $entityManager->getRepository('App\Entity\File')->find($fileId);
            var_dump($fileId);
            $article->setImage($fileEntity);            
            $entityManager->persist($article);
            $entityManager->flush();
            
        }
    }

    private function getBarchasbs(EntityManagerInterface $entityManager, int $type): array
    {
        return $entityManager->getRepository(Barchasb::class)->findBy(['type' => $type]);
    }     
    
    #[Route('/article/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/article/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
