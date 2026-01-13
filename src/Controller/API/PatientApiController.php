<?php

namespace App\Controller\API;

use App\Entity\Patient;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * PatientApiController - CRUD API for patients
 * Handles GET, POST, DELETE operations for patients
 */
class PatientApiController extends AbstractController
{
    #[Route('/api/patients', methods: ['GET'])]
    public function index(PatientRepository $repo): JsonResponse
    {
        $patients = $repo->findAll();
        $data = array_map(function($patient) {
            return [
                'id' => $patient->getId(),
                'nom' => $patient->getFullName(),
                'telephone' => $patient->getPhone(),
            ];
        }, $patients);
        
        return $this->json($data);
    }

    #[Route('/api/patients', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nom']) || !isset($data['telephone'])) {
            return $this->json(['error' => 'Missing required fields: nom, telephone'], 400);
        }

        // Parse nom into first_name and last_name
        $nameParts = explode(' ', $data['nom'], 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        $patient = new Patient();
        $patient->setFirstName($firstName);
        $patient->setLastName($lastName);
        $patient->setPhone($data['telephone']);
        $patient->setPatientNumber('PAT' . uniqid());
        
        // Set required fields with defaults
        $patient->setDateOfBirth(new \DateTime('1990-01-01'));
        $patient->setAddress('');
        $patient->setCity('');
        $patient->setPostalCode('');

        $em->persist($patient);
        $em->flush();

        return $this->json([
            'id' => $patient->getId(),
            'nom' => $patient->getFullName(),
            'telephone' => $patient->getPhone(),
        ], 201);
    }

    #[Route('/api/patients/{id}', methods: ['DELETE'])]
    public function delete(Patient $patient, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($patient);
        $em->flush();

        return $this->json(['message' => 'Patient deleted']);
    }
}

