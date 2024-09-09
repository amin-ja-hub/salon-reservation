<?php

namespace App\Controller;

use App\Entity\DefaultWorkSchedule;
use App\Form\DefaultWorkScheduleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('admin/schedule/')]
final class DefaultWorkScheduleController extends AbstractController
{
    #[Route(name: 'app_default_schedule_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $defaultWorkSchedules = $entityManager
            ->getRepository(DefaultWorkSchedule::class)
            ->findAll();

        return $this->render('default_work_schedule/index.html.twig', [
            'items'=> $defaultWorkSchedules,
        ]);
    }

    #[Route('/new', name: 'app_default_schedule_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $defaultWorkSchedule = new DefaultWorkSchedule();
        $form = $this->createForm(DefaultWorkScheduleType::class, $defaultWorkSchedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($defaultWorkSchedule);
            $entityManager->flush();

            return $this->redirectToRoute('app_default_work_schedule_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('default_work_schedule/new.html.twig', [
            'default_work_schedule' => $defaultWorkSchedule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_default_schedule_show', methods: ['GET'])]
    public function show(DefaultWorkSchedule $defaultWorkSchedule): Response
    {
        return $this->render('default_work_schedule/show.html.twig', [
            'default_work_schedule' => $defaultWorkSchedule,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_default_schedule_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DefaultWorkSchedule $defaultWorkSchedule, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DefaultWorkScheduleType::class, $defaultWorkSchedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_default_work_schedule_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('default_work_schedule/edit.html.twig', [
            'default_work_schedule' => $defaultWorkSchedule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_default_work_schedule_delete', methods: ['POST'])]
    public function delete(Request $request, DefaultWorkSchedule $defaultWorkSchedule, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$defaultWorkSchedule->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($defaultWorkSchedule);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_default_schedule_index', [], Response::HTTP_SEE_OTHER);
    }
}
