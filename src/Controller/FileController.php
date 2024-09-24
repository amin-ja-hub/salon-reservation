<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\FileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/')]
class FileController extends AbstractController
{
    
    #[Route('/admin/file', name: 'app_file_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Get the current page from the query parameter, default is 1
        $page = $request->query->getInt('page', 1);
        $limit = 5; // Limit to 50 items per page

        // Get the total number of files
        $fileRepository = $entityManager->getRepository(File::class);
        $totalFiles = $fileRepository->count([]);

        // Calculate the total number of pages
        $totalPages = ceil($totalFiles / $limit);

        // Calculate the offset for the current page
        $offset = ($page - 1) * $limit;

        // Fetch the files for the current page
        $files = $fileRepository->findBy([], null, $limit, $offset);

        return $this->render('file/index.html.twig', [
            'files' => $files,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/admin/file/upload', name: 'app_file_upload', methods: ['POST'])]
    public function uploadFiles(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        $uploadedFiles = $request->files->get('files');

        // Define the upload directory directly in the controller
        $uploadDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/files';

        foreach ($uploadedFiles as $uploadedFile) {
            if ($uploadedFile instanceof UploadedFile) {
                // Get the file size before moving the file
                $fileSize = $uploadedFile->getSize();

                // Generate a unique name for the file
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                // Move the file to the upload directory
                try {
                    $uploadedFile->move($uploadDirectory, $newFilename);

                    // Create a new File entity and set its properties
                    $fileEntity = new File();
                    $fileEntity->setName($newFilename);
                    $fileEntity->setPath('/uploads/files');
                    $fileEntity->setCdate(new \DateTime());
                    $fileEntity->setSize($fileSize); // Now using the size fetched before moving the file
                    $fileEntity->setFormat($uploadedFile->getClientMimeType());
                    // Optionally set the user
                    $fileEntity->setUser($this->getUser());

                    // Persist and flush the file entity
                    $entityManager->persist($fileEntity);
                    $entityManager->flush();
                } catch (FileException $e) {
                    // Handle the error
                }
            }
        }

        return $this->redirectToRoute('app_file_index');
    }

    #[Route('file/new', name: 'app_file_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $file = new File();
        $form = $this->createForm(FileType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($file);
            $entityManager->flush();

            return $this->redirectToRoute('app_file_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('file/new.html.twig', [
            'file' => $file,
            'form' => $form,
        ]);
    }

    #[Route('file/{id}', name: 'app_file_show', methods: ['GET'])]
    public function show(File $file): Response
    {
        return $this->render('file/show.html.twig', [
            'file' => $file,
        ]);
    }

    #[Route('file/{id}/edit', name: 'app_file_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, File $file, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FileType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_file_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('file/edit.html.twig', [
            'file' => $file,
            'form' => $form,
        ]);
    }

    #[Route('admin/file/{id}', name: 'app_file_delete', methods: ['POST'])]
    public function delete(Request $request, File $file, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$file->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($file);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_file_index', [], Response::HTTP_SEE_OTHER);
    }
}
