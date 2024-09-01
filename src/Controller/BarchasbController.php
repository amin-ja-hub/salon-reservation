<?php

namespace App\Controller;

use App\Entity\Barchasb;
use App\Form\BarchasbType;
use App\Repository\BarchasbRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/barchasb')]
final class BarchasbController extends AbstractController
{
    #[Route(name: 'app_barchasb_index', methods: ['GET'])]
    public function index(BarchasbRepository $barchasbRepository): Response
    {
        return $this->render('barchasb/index.html.twig', [
            'barchasbs' => $barchasbRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_barchasb_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $barchasb = new Barchasb();
        $form = $this->createForm(BarchasbType::class, $barchasb);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($barchasb);
            $entityManager->flush();

            return $this->redirectToRoute('app_barchasb_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('barchasb/new.html.twig', [
            'barchasb' => $barchasb,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_barchasb_show', methods: ['GET'])]
    public function show(Barchasb $barchasb): Response
    {
        return $this->render('barchasb/show.html.twig', [
            'barchasb' => $barchasb,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_barchasb_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Barchasb $barchasb, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BarchasbType::class, $barchasb);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_barchasb_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('barchasb/edit.html.twig', [
            'barchasb' => $barchasb,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_barchasb_delete', methods: ['POST'])]
    public function delete(Request $request, Barchasb $barchasb, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$barchasb->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($barchasb);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_barchasb_index', [], Response::HTTP_SEE_OTHER);
    }
}
