<?php

namespace App\Controller;

use App\Entity\WorkSchedule;
use App\Form\WorkScheduleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/work/schedule')]
final class WorkScheduleController extends AbstractController
{
    #[Route(name: 'app_work_schedule_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $workSchedules = $entityManager
            ->getRepository(WorkSchedule::class)
            ->findAll();

        return $this->render('work_schedule/index.html.twig', [
            'work_schedules' => $workSchedules,
        ]);
    }

    #[Route('/new', name: 'app_work_schedule_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $workSchedule = new WorkSchedule();
        $form = $this->createForm(WorkScheduleType::class, $workSchedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($workSchedule);
            $entityManager->flush();

            return $this->redirectToRoute('app_work_schedule_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('work_schedule/new.html.twig', [
            'work_schedule' => $workSchedule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_work_schedule_show', methods: ['GET'])]
    public function show(WorkSchedule $workSchedule): Response
    {
        return $this->render('work_schedule/show.html.twig', [
            'work_schedule' => $workSchedule,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_work_schedule_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, WorkSchedule $workSchedule, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WorkScheduleType::class, $workSchedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_work_schedule_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('work_schedule/edit.html.twig', [
            'work_schedule' => $workSchedule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_work_schedule_delete', methods: ['POST'])]
    public function delete(Request $request, WorkSchedule $workSchedule, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$workSchedule->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($workSchedule);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_work_schedule_index', [], Response::HTTP_SEE_OTHER);
    }
}
