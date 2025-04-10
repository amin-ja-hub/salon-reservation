<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\Service;

#[Route('/')]
class UserController extends AbstractController
{
    #[Route('/admin/user', name: 'app_user_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $searchTerm = $request->query->get('search');

        $query = $entityManager->createQuery(
            'SELECT u FROM App\Entity\User u 
             WHERE u.fullName LIKE :searchTerm 
             OR u.email LIKE :searchTerm
             OR u.mobile LIKE :searchTerm
             OR u.username LIKE :searchTerm'
        )->setParameter('searchTerm', '%' . $searchTerm . '%');

        $users = $query->getResult();

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'searchTerm' => $searchTerm
        ]);
    }


    #[Route('admin/user/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('admin/user/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('admin/user/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager,Service $service): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all();
            $plainPassword = $formData['pass'];

            // Handle required fields like title, publish, metadesc, text, etc.
            $user->setFullName($formData['name']);
            $user->setUdate(new \DateTime());
            $user->setEmail($formData['email']);
            $user->setMobile($formData['mobile']);
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
            $user->setPassword($hashedPassword);
            $user->setPlainPassword($plainPassword);
            // Handle file upload if a new file is provided
            if ($request->files->get('file') != null) {
                $file = $request->files->get('file');
                $fileId = $service->uploadFile(4, $file, $user->getId(), 'mainpic');
                $fileEntity = $entityManager->getRepository('App\Entity\File')->find($fileId);
                $user->setImage($fileEntity);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('admin/user/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/user/edit', name: 'app_user_edit_self', methods: ['GET', 'POST'])]
    public function editSelf(Request $request, EntityManagerInterface $entityManager, Service $service): Response
    {
        $user = $this->getUser(); // Get the logged-in user

        // Create and handle the form for the logged-in user
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all();
            $plainPassword = $formData['pass'];

            // Handle required fields like title, publish, metadesc, text, etc.
            $user->setFullName($formData['name']);
            $user->setEmail($formData['email']);
            $user->setMobile($formData['mobile']);
            $user->setText($formData['text']);
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
            $user->setPassword($hashedPassword);
            $user->setPlainPassword($plainPassword);
            // Handle file upload if a new file is provided
            if ($request->files->get('file') != null) {
                $file = $request->files->get('file');
                $fileId = $service->uploadFile(4, $file, $user->getId(), 'mainpic');
                $fileEntity = $entityManager->getRepository('App\Entity\File')->find($fileId);
                $user->setImage($fileEntity);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_panel', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    
}
