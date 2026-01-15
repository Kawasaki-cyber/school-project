<?php

namespace App\Controller;

use App\Repository\MedecinRepository;
use App\Repository\PatientRepository;
use App\Repository\UserRepository;
use App\Repository\RendezVousRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin')]
    public function dashboard(
        UserRepository $userRepository,
        MedecinRepository $medecinRepository,
        PatientRepository $patientRepository,
        RendezVousRepository $rendezVousRepository
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $latestUsers = $userRepository->findBy([], ['id' => 'DESC'], 5);
        $latestMedecins = $medecinRepository->findBy([], ['id' => 'DESC'], 5);
        $latestPatients = $patientRepository->findBy([], ['registration_date' => 'DESC'], 5);
        $latestRendezvous = $rendezVousRepository->findBy([], ['date_modification' => 'DESC', 'date_creation' => 'DESC'], 10);

        return $this->render('admin/dashboard.html.twig', [
            'users_count' => $userRepository->count([]),
            'medecins_count' => $medecinRepository->count([]),
            'patients_count' => $patientRepository->count([]),
            'rendezvous_count' => $rendezVousRepository->count([]),
            'latest_users' => $latestUsers,
            'latest_medecins' => $latestMedecins,
            'latest_patients' => $latestPatients,
            'latest_rendezvous' => $latestRendezvous,
        ]);
    }

}
