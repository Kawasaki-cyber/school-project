<?php

namespace App\Controller;

use App\Entity\Medecin;
use App\Repository\MedecinRepository;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MedecinController extends AbstractController
{
    #[Route('/medecin', name: 'app_medecin')]
    public function index(MedecinRepository $medecinRepository, RendezVousRepository $rendezVousRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_DOCTOR')) {
            throw $this->createAccessDeniedException();
        }

        $search = $request->query->get('search', '');
        $medecins = $search ? $medecinRepository->search($search) : $medecinRepository->findAll();
        // Sort by creation date so newly created rendez-vous appear first
        $rendezvous = $rendezVousRepository->findBy([], ['date_creation' => 'DESC']);

        return $this->render('medecin/index.html.twig', [
            'medecins' => $medecins,
            'search' => $search,
            'rendezvous' => $rendezvous,
        ]);
    }


        // Medecin CRUD
    #[Route('/medecins', name: 'app_admin_medecins')]
    public function listMedecins(MedecinRepository $medecinRepository, RendezVousRepository $rendezVousRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_DOCTOR')) {
            throw $this->createAccessDeniedException();
        }

        $search = $request->query->get('search', '');
        
        if ($search) {
            $medecins = $medecinRepository->search($search);
        } else {
            $medecins = $medecinRepository->findAll();
        }

        // Sort by creation date so newly created rendez-vous appear first
        $rendezvous = $rendezVousRepository->findBy([], ['date_creation' => 'DESC']);

        return $this->render('medecin/index.html.twig', [
            'medecins' => $medecins,
            'search' => $search,
            'rendezvous' => $rendezvous,
        ]);
    }

    #[Route('/medecins/new', name: 'app_admin_medecin_new')]
    public function newMedecin(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_DOCTOR')) {
            throw $this->createAccessDeniedException();
        }

        $medecin = new Medecin();
        $form = $this->createForm(\App\Form\MedecinType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($medecin);
            $entityManager->flush();

            $this->addFlash('success', 'Médecin créé avec succès!');
            return $this->redirectToRoute('app_admin_medecins');
        }

        return $this->render('medecin/form.html.twig', [
            'form' => $form,
            'title' => 'Create Medecin',
        ]);
    }

    #[Route('/medecins/{id}', name: 'app_admin_medecin_show')]
    public function showMedecin(Medecin $medecin): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_DOCTOR')) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('medecin/show.html.twig', [
            'medecin' => $medecin,
        ]);
    }

    #[Route('/medecins/{id}/edit', name: 'app_admin_medecin_edit')]
    public function editMedecin(Request $request, Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_DOCTOR')) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(\App\Form\MedecinType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Médecin modifié avec succès!');
            return $this->redirectToRoute('app_admin_medecins');
        }

        return $this->render('medecin/form.html.twig', [
            'form' => $form,
            'title' => 'Edit Medecin',
            'medecin' => $medecin,
        ]);
    }

    #[Route('/medecins/{id}/delete', name: 'app_admin_medecin_delete', methods: ['POST'])]
    public function deleteMedecin(Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_DOCTOR')) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($medecin);
        $entityManager->flush();

        $this->addFlash('success', 'Médecin supprimé avec succès!');
        return $this->redirectToRoute('app_admin_medecins');
    }

    #[Route('/medecins/export/csv', name: 'app_medecin_export_csv')]
    public function exportCsv(MedecinRepository $medecinRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_DOCTOR')) {
            throw $this->createAccessDeniedException();
        }

        $medecins = $medecinRepository->findAll();

        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="medecins_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://temp', 'r+');
        
        // CSV Header
        fputcsv($output, ['ID', 'License Number', 'First Name', 'Last Name', 'Specialization', 'Email', 'Phone', 'City', 'Active']);

        // CSV Data
        foreach ($medecins as $medecin) {
            fputcsv($output, [
                $medecin->getId(),
                $medecin->getLicenseNumber(),
                $medecin->getFirstName(),
                $medecin->getLastName(),
                $medecin->getSpecialization(),
                $medecin->getEmail(),
                $medecin->getPhone(),
                $medecin->getCity(),
                $medecin->isActive() ? 'Yes' : 'No',
            ]);
        }

        rewind($output);
        $response->setContent(stream_get_contents($output));
        fclose($output);

        return $response;
    }
}

