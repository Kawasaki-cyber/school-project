<?php

namespace App\Controller\API;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * StatsApiController - Statistics API for dashboard
 * Returns 5 statistics as required:
 * 1. Appointments by doctor
 * 2. Appointments by status
 * 3. Appointments by date
 * 4. Patients by speciality
 * 5. Appointments per week
 */
class StatsApiController extends AbstractController
{
    #[Route('/api/stats', methods: ['GET'])]
    public function stats(EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();

        // 1. Appointments by doctor
        $rdvByDoctor = $conn->fetchAllAssociative("
            SELECT 
                m.id as medecin_id,
                CONCAT(m.first_name, ' ', m.last_name) as medecin_nom,
                COUNT(r.id) as total
            FROM medecin m
            LEFT JOIN rendez_vous r ON r.medecin_id = m.id
            GROUP BY m.id, m.first_name, m.last_name
            ORDER BY total DESC
        ");

        // 2. Appointments by status
        $rdvByStatus = $conn->fetchAllAssociative("
            SELECT statut, COUNT(*) as total
            FROM rendez_vous
            GROUP BY statut
            ORDER BY total DESC
        ");

        // 3. Appointments by date (last 30 days)
        $rdvByDate = $conn->fetchAllAssociative("
            SELECT 
                DATE(date_heure) as date,
                COUNT(*) as total
            FROM rendez_vous
            WHERE date_heure >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(date_heure)
            ORDER BY date DESC
        ");

        // 4. Patients by speciality
        $patientsBySpecialite = $conn->fetchAllAssociative("
            SELECT 
                s.id as specialite_id,
                s.nom as specialite_nom,
                COUNT(DISTINCT p.id) as total
            FROM specialite s
            LEFT JOIN medecin m ON m.specialite_id = s.id
            LEFT JOIN rendez_vous r ON r.medecin_id = m.id
            LEFT JOIN patient p ON r.patient_id = p.id
            GROUP BY s.id, s.nom
            HAVING total > 0
            ORDER BY total DESC
        ");

        // 5. Appointments per week (last 12 weeks)
        $rdvPerWeek = $conn->fetchAllAssociative("
            SELECT 
                YEARWEEK(date_heure) as week,
                COUNT(*) as total
            FROM rendez_vous
            WHERE date_heure >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
            GROUP BY YEARWEEK(date_heure)
            ORDER BY week DESC
        ");

        return $this->json([
            'rdvByDoctor' => $rdvByDoctor,
            'rdvByStatus' => $rdvByStatus,
            'rdvByDate' => $rdvByDate,
            'patientsBySpecialite' => $patientsBySpecialite,
            'rdvPerWeek' => $rdvPerWeek,
        ]);
    }
}
