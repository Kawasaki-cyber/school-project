<?php

namespace App\Controller\API;

use App\Entity\RendezVous;
use App\Repository\RendezVousRepository;
use App\Repository\MedecinRepository;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * RendezVousApiController - CRUD API for appointments
 * Handles GET, POST, DELETE operations for rendez-vous
 * IMPORTANT: Enforces business rule - doctor cannot have two appointments at same date/time
 */
class RendezVousApiController extends AbstractController
{
    #[Route('/api/rdv', methods: ['GET'])]
    public function index(RendezVousRepository $repo): JsonResponse
    {
        $rdvs = $repo->findAll();
        $data = array_map(function($rdv) {
            return [
                'id' => $rdv->getId(),
                'medecin_id' => $rdv->getMedecin() ? $rdv->getMedecin()->getId() : null,
                'medecin_nom' => $rdv->getMedecin() ? $rdv->getMedecin()->getFullName() : null,
                'patient_id' => $rdv->getPatient() ? $rdv->getPatient()->getId() : null,
                'patient_nom' => $rdv->getPatient() ? $rdv->getPatient()->getFullName() : null,
                'date' => $rdv->getDateHeure() ? $rdv->getDateHeure()->format('Y-m-d') : null,
                'heure' => $rdv->getDateHeure() ? $rdv->getDateHeure()->format('H:i') : null,
                'date_heure' => $rdv->getDateHeure() ? $rdv->getDateHeure()->format('Y-m-d H:i:s') : null,
                'statut' => $rdv->getStatut(),
            ];
        }, $rdvs);
        
        return $this->json($data);
    }

    #[Route('/api/rdv', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        MedecinRepository $medRepo,
        PatientRepository $patRepo,
        RendezVousRepository $rdvRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['medecin_id']) || !isset($data['patient_id']) || !isset($data['date']) || !isset($data['heure'])) {
            return $this->json(['error' => 'Missing required fields: medecin_id, patient_id, date, heure'], 400);
        }

        $medecin = $medRepo->find($data['medecin_id']);
        if (!$medecin) {
            return $this->json(['error' => 'Medecin not found'], 404);
        }

        $patient = $patRepo->find($data['patient_id']);
        if (!$patient) {
            return $this->json(['error' => 'Patient not found'], 404);
        }

        // Combine date and heure into DateTime
        try {
            $dateHeure = new \DateTime($data['date'] . ' ' . $data['heure']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date or time format'], 400);
        }

        // ❗ BUSINESS RULE: A doctor cannot have two appointments at the same date and time
        // This rule is enforced ONLY in Symfony backend (not JavaScript)
        $existing = $rdvRepo->createQueryBuilder('r')
            ->where('r.medecin = :medecin')
            ->andWhere('r.date_heure = :dateHeure')
            ->andWhere('r.statut != :annule')
            ->setParameter('medecin', $medecin)
            ->setParameter('dateHeure', $dateHeure)
            ->setParameter('annule', 'annule')
            ->getQuery()
            ->getOneOrNullResult();

        if ($existing) {
            return $this->json(
                ['error' => 'This doctor already has an appointment at this date and time'],
                400
            );
        }

        $rdv = new RendezVous();
        $rdv->setMedecin($medecin);
        $rdv->setPatient($patient);
        $rdv->setDateHeure($dateHeure);
        $rdv->setStatut($data['statut'] ?? 'programme');
        $rdv->setReference('RDV' . uniqid());
        $rdv->setType($data['type'] ?? 'consultation');

        $em->persist($rdv);
        $em->flush();

        return $this->json([
            'id' => $rdv->getId(),
            'medecin_id' => $rdv->getMedecin()->getId(),
            'patient_id' => $rdv->getPatient()->getId(),
            'date' => $rdv->getDateHeure()->format('Y-m-d'),
            'heure' => $rdv->getDateHeure()->format('H:i'),
            'statut' => $rdv->getStatut(),
        ], 201);
    }

    #[Route('/api/rdv/{id}', methods: ['DELETE'])]
    public function delete(RendezVous $rdv, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($rdv);
        $em->flush();

        return $this->json(['message' => 'Appointment deleted']);
    }
}
