<?php

namespace App\Controller\API;

use App\Repository\RendezVousRepository;
use App\Repository\MedecinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class StatsController extends AbstractController
{
    #[Route('/stats', name: 'api_stats')]
    public function getStats(
        RendezVousRepository $rendezVousRepo,
        MedecinRepository $medecinRepo,
        EntityManagerInterface $em
    ): JsonResponse
    {
        // Rendez-vous by Doctor
        $rdvByDoctor = $em->createQuery(
            'SELECT CONCAT(m.first_name, \' \', m.last_name) as medecin_nom, COUNT(r.id) as total
             FROM App\Entity\RendezVous r
             JOIN r.medecin m
             GROUP BY m.id
             ORDER BY total DESC'
        )->getResult();

        // Rendez-vous by Status
        $rdvByStatus = $em->createQuery(
            'SELECT r.statut, COUNT(r.id) as total
             FROM App\Entity\RendezVous r
             GROUP BY r.statut'
        )->getResult();

        // Rendez-vous by Date (last 30 days)
        $rdvByDate = $em->createQuery(
            'SELECT DATE(r.date_heure) as date, COUNT(r.id) as total
             FROM App\Entity\RendezVous r
             WHERE r.date_heure >= :date
             GROUP BY date
             ORDER BY date ASC'
        )->setParameter('date', new \DateTime('-30 days'))
        ->getResult();

        // Patients by Specialite
        $patientsBySpecialite = $em->createQuery(
            'SELECT s.nom as specialite_nom, COUNT(DISTINCT r.patient) as total
             FROM App\Entity\RendezVous r
             JOIN r.medecin m
             JOIN m.specialite s
             GROUP BY s.id
             ORDER BY total DESC'
        )->getResult();

        // Rendez-vous per Week (last 12 weeks)
        $rdvPerWeek = $em->createQuery(
            'SELECT YEARWEEK(r.date_heure) as week, COUNT(r.id) as total
             FROM App\Entity\RendezVous r
             WHERE r.date_heure >= :date
             GROUP BY week
             ORDER BY week ASC'
        )->setParameter('date', new \DateTime('-12 weeks'))
        ->getResult();

        return $this->json([
            'rdvByDoctor' => $rdvByDoctor,
            'rdvByStatus' => $rdvByStatus,
            'rdvByDate' => $rdvByDate,
            'patientsBySpecialite' => $patientsBySpecialite,
            'rdvPerWeek' => $rdvPerWeek,
        ]);
    }
}
