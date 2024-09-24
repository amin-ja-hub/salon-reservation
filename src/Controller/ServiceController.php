<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Barchasb;
use Symfony\Component\HttpFoundation\File\UploadedFile; // Add this at the top of your controller if not already present
use App\Service\Service as FileUploadService; // Ensure this is imported

#[Route('/admin/service')]
final class ServiceController extends AbstractController
{
    #[Route(name: 'app_service_index', methods: ['GET'])]
    public function index(ServiceRepository $serviceRepository): Response
    {
        return $this->render('service/index.html.twig', [
            'services' => $serviceRepository->findBy(['parent' => null]),
        ]);
    }

    #[Route('/new', name: 'app_service_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, FileUploadService $fileUploadService): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all();
            $this->setServiceFields($service, $formData);
            $this->handleTags($service, $formData['keywords'] ?? [], $entityManager, 2);

            $entityManager->persist($service);
            $entityManager->flush();

            // Injecting the file upload service here instead of the entity
            $this->handleFileUpload($service, $request->files->get('file'), $fileUploadService, $entityManager);

            return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('service/new.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_service_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {            
            
            $formData = $request->request->all();
            $this->setServiceFields($service, $formData);
            $this->handleTags($service, $formData['keywords'] ?? [], $entityManager,2);
            
            
            $entityManager->flush();
            
            $this->handleFileUpload($service, $request->files->get('file'), $fileUploadService, $entityManager);

            return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('service/edit.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    // Private methods

    private function setServiceFields(Service $service, array $formData, string $type = '1'): void
    {
        $service->setTitle($formData['title']);
        $service->setUser($this->getUser());
        $service->setMetadesc($formData['metadesc']);
        $service->setText($formData['text']);
        $service->setUrl($formData['url']);
        $service->setCdate(new \DateTime());
        $service->setType($type);
    }

    private function handleTags(Service $service, array $tags, EntityManagerInterface $entityManager,$type): void
    {
        // Clear existing tags
        foreach ($service->getBarchasbs() as $barchasb) {
            $service->removeBarchasb($barchasb);
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

            $service->addBarchasb($barchasb);
        }
    }

    private function handleFileUpload(Service $serviceEntity, ?UploadedFile $file, FileUploadService $service, EntityManagerInterface $entityManager): void
    {
        if ($file !== null) {
            $fileId = $service->uploadFile(3, $file, $serviceEntity->getId(), 'mainpic');
            $fileEntity = $entityManager->getRepository('App\Entity\File')->find($fileId);
            var_dump($fileId);
            $serviceEntity->setImage($fileEntity);            
            $entityManager->persist($serviceEntity);
            $entityManager->flush();
        }
    }
    
    #[Route('/{id}', name: 'app_service_show', methods: ['GET'])]
    public function show(Service $service): Response
    {
        return $this->render('service/show.html.twig', [
            'service' => $service,
        ]);
    }    
    
    #[Route('/{id}', name: 'app_service_delete', methods: ['POST'])]
    public function delete(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($service);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
    }
}
