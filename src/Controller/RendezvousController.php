<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RendezvousController extends AbstractController
{
    #[Route('/rendezvous', name: 'app_rendezvous')]
    public function index(RendezVousRepository $rendezVousRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $rendezvous = $rendezVousRepository->findBy([], ['date_heure' => 'DESC']);

        return $this->render('rendezvous/index.html.twig', [
            'rendezvous' => $rendezvous,
        ]);
    }

        // Rendezvous Management
    #[Route('/admin/rendezvous', name: 'app_admin_rendezvous')]
    public function listRendezvous(RendezVousRepository $rendezVousRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_PATIENT')) {
            throw $this->createAccessDeniedException();
        }

        $rendezvous = $rendezVousRepository->findBy([], ['date_creation' => 'DESC']);
        return $this->render('admin/rendezvous/index.html.twig', [
            'rendezvous' => $rendezvous,
        ]);
    }

    #[Route('/admin/rendezvous/{id}', name: 'app_admin_rendezvous_show')]
    public function showRendezvous(RendezVous $rendezvous): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_PATIENT')) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('admin/rendezvous/show.html.twig', [
            'rendezvous' => $rendezvous,
        ]);
    }

    #[Route('/admin/rendezvous/{id}/edit', name: 'app_admin_rendezvous_edit', methods: ['GET', 'POST'])]
    public function editRendezvous(Request $request, RendezVous $rendezvous, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(RendezVousType::class, $rendezvous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Rendez-vous updated successfully.');
            return $this->redirectToRoute('app_admin_rendezvous_show', ['id' => $rendezvous->getId()]);
        }

        return $this->render('admin/rendezvous/edit.html.twig', [
            'rendezvous' => $rendezvous,
            'form' => $form,
        ]);
    }

    #[Route('/admin/rendezvous/{id}/delete', name: 'app_admin_rendezvous_delete', methods: ['POST'])]
    public function deleteRendezvous(Request $request, RendezVous $rendezvous, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_rendezvous_' . $rendezvous->getId(), $token)) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_admin_rendezvous_show', ['id' => $rendezvous->getId()]);
        }

        $entityManager->remove($rendezvous);
        $entityManager->flush();

        $this->addFlash('success', 'Rendez-vous deleted successfully.');
        return $this->redirectToRoute('app_admin_rendezvous');
    }
}