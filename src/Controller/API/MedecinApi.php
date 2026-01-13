<?php

namespace App\Controller\API;

use App\Entity\Medecin;
use App\Entity\Specialite;
use App\Repository\MedecinRepository;
use App\Repository\SpecialiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * MedecinApiController - CRUD API for doctors
 * Handles GET, POST, DELETE operations for medecins
 */
class MedecinApiController extends AbstractController
{
    #[Route('/api/medecins', methods: ['GET'])]
    public function index(MedecinRepository $repo): JsonResponse
    {
        $medecins = $repo->findAll();
        $data = array_map(function($medecin) {
            return [
                'id' => $medecin->getId(),
                'nom' => $medecin->getFullName(),
                'specialite' => $medecin->getSpecialite() ? $medecin->getSpecialite()->getNom() : null,
                'specialite_id' => $medecin->getSpecialite() ? $medecin->getSpecialite()->getId() : null,
            ];
        }, $medecins);
        
        return $this->json($data);
    }

    #[Route('/api/medecins', methods: ['POST'])]
    public function create(
        Request $request, 
        EntityManagerInterface $em,
        SpecialiteRepository $specialiteRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nom']) || !isset($data['specialite_id'])) {
            return $this->json(['error' => 'Missing required fields: nom, specialite_id'], 400);
        }

        $specialite = $specialiteRepo->find($data['specialite_id']);
        if (!$specialite) {
            return $this->json(['error' => 'Specialite not found'], 404);
        }

        // Parse nom into first_name and last_name
        $nameParts = explode(' ', $data['nom'], 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        $medecin = new Medecin();
        $medecin->setFirstName($firstName);
        $medecin->setLastName($lastName);
        $medecin->setSpecialite($specialite);
        $medecin->setSpecialization($specialite->getNom());
        
        // Set required fields with defaults
        $medecin->setLicenseNumber('LIC' . uniqid());
        $medecin->setLicenseIssueDate(new \DateTime());
        $medecin->setAddress('');
        $medecin->setCity('');
        $medecin->setPostalCode('');
        $medecin->setCountry('');

        $em->persist($medecin);
        $em->flush();

        return $this->json([
            'id' => $medecin->getId(),
            'nom' => $medecin->getFullName(),
            'specialite' => $medecin->getSpecialite()->getNom(),
        ], 201);
    }

    #[Route('/api/medecins/{id}', methods: ['DELETE'])]
    public function delete(Medecin $medecin, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($medecin);
        $em->flush();

        return $this->json(['message' => 'Doctor deleted']);
    }
}
