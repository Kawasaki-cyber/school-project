<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\RendezVous;
use App\Repository\MedecinRepository;
use App\Repository\PatientRepository;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PatientController extends AbstractController
{
    #[Route('/patient', name: 'app_patient', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        RendezVousRepository $rendezVousRepository,
        PatientRepository $patientRepository,
        MedecinRepository $medecinRepository
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_PATIENT')) {
            throw $this->createAccessDeniedException();
        }

        $currentUser = $this->getUser();
        $currentPatient = null;
        if (!$this->isGranted('ROLE_ADMIN') && $currentUser instanceof \App\Entity\User) {
            $userEmail = $currentUser->getEmail();
            $userNom = $currentUser->getNom();

            if ($userEmail) {
                $currentPatient = $patientRepository->findOneBy(['email' => $userEmail]);
            }
            if (!$currentPatient && $userNom) {
                $currentPatient = $patientRepository->findOneByFullName($userNom);
            }
        }

        if ($request->isMethod('POST')) {
            $csrfToken = (string) $request->request->get('_csrf_token');
            if (!$this->isCsrfTokenValid('create_rendezvous', $csrfToken)) {
                $this->addFlash('error', 'Invalid CSRF token. Please try again.');
                return $this->redirectToRoute('app_patient');
            }

            $patientName = trim((string) $request->request->get('patient_name'));
            $medecinId = (int) $request->request->get('medecin_id');
            $dateTimeRaw = (string) $request->request->get('date_heure');
            $type = trim((string) $request->request->get('type'));
            $motif = trim((string) $request->request->get('motif'));

            if ($patientName === '' || !$medecinId || !$dateTimeRaw || $type === '') {
                $this->addFlash('error', 'Please fill in your name, doctor, date/time and type.');
                return $this->redirectToRoute('app_patient');
            }

            try {
                $dateHeure = new \DateTime($dateTimeRaw);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Invalid date/time.');
                return $this->redirectToRoute('app_patient');
            }

            $patient = $patientRepository->findOneByFullName($patientName);
            $medecin = $medecinRepository->find($medecinId);

            if (!$medecin) {
                $this->addFlash('error', 'Invalid doctor selection.');
                return $this->redirectToRoute('app_patient');
            }

            // If the patient doesn't exist yet, create a minimal record so we can attach the rendez-vous.
            // (This is a school/demo-friendly approach; you can later replace it by linking User <-> Patient.)
            if (!$patient) {
                $normalized = trim(preg_replace('/\s+/', ' ', $patientName) ?? '');
                $parts = $normalized !== '' ? explode(' ', $normalized) : [];
                $firstName = $parts[0] ?? 'Unknown';
                $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'Unknown';

                $patient = new Patient();
                $patient->setPatientNumber('PAT' . date('YmdHis') . random_int(100, 999));
                $patient->setFirstName($firstName);
                $patient->setLastName($lastName);
                $patient->setDateOfBirth(new \DateTime('2000-01-01'));
                $patient->setAddress('N/A');
                $patient->setCity('N/A');
                $patient->setPostalCode('00000');

                if ($currentUser instanceof \App\Entity\User && $currentUser->getEmail()) {
                    $patient->setEmail($currentUser->getEmail());
                }

                $entityManager->persist($patient);
            }

            if (!$patient) {
                $this->addFlash('error', 'Unable to resolve patient.');
                return $this->redirectToRoute('app_patient');
            }

            $rendezVous = new RendezVous();
            $rendezVous->setReference('RDV' . date('YmdHis') . random_int(100, 999));
            $rendezVous->setPatient($patient);
            $rendezVous->setMedecin($medecin);
            $rendezVous->setDateHeure($dateHeure);
            $rendezVous->setType($type);
            $rendezVous->setMotif($motif !== '' ? $motif : null);
            $rendezVous->setStatut('programme');

            $entityManager->persist($rendezVous);
            $entityManager->flush();

            $this->addFlash('success', 'Rendez-vous pris avec succès.');
            return $this->redirectToRoute('app_patient');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $rendezvous = $rendezVousRepository->findBy([], ['date_heure' => 'DESC']);
        } elseif ($currentPatient) {
            $rendezvous = $rendezVousRepository->findBy(['patient' => $currentPatient], ['date_heure' => 'DESC']);
        } else {
            $rendezvous = [];
        }

        return $this->render('patient/index.html.twig', [
            'medecins' => $medecinRepository->findAll(),
            'rendezvous' => $rendezvous,
        ]);
    }

    #[Route('/patients', name: 'app_admin_patients')]
    public function listPatients(PatientRepository $patientRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $search = $request->query->get('search', '');
        
        if ($search) {
            $patients = $patientRepository->search($search);
        } else {
            $patients = $patientRepository->findAll();
        }

        return $this->render('patient/list.html.twig', [
            'patients' => $patients,
            'search' => $search,
        ]);
    }

    #[Route('/patients/{id}', name: 'app_patient_show', requirements: ['id' => '\d+'])]
    public function show(Patient $patient): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('patient/show.html.twig', [
            'patient' => $patient,
        ]);
    }

    #[Route('/patients/{id}/delete', name: 'app_patient_delete', methods: ['POST'])]
    public function delete(Patient $patient, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em->remove($patient);
        $em->flush();

        $this->addFlash('success', 'Patient deleted successfully!');
        return $this->redirectToRoute('app_admin_patients');
    }
}
