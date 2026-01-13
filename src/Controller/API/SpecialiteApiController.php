<?php

namespace App\Controller\API;

use App\Entity\Specialite;
use App\Repository\SpecialiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * SpecialiteApiController - CRUD API for specialities
 * Handles GET, POST, DELETE operations for specialites
 */
class SpecialiteApiController extends AbstractController
{
    #[Route('/api/specialites', methods: ['GET'])]
    public function index(SpecialiteRepository $repo): JsonResponse
    {
        $specialites = $repo->findAll();
        $data = array_map(function($specialite) {
            return [
                'id' => $specialite->getId(),
                'nom' => $specialite->getNom(),
            ];
        }, $specialites);
        
        return $this->json($data);
    }

    #[Route('/api/specialites', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nom'])) {
            return $this->json(['error' => 'Missing required field: nom'], 400);
        }

        $specialite = new Specialite();
        $specialite->setNom($data['nom']);

        $em->persist($specialite);
        $em->flush();

        return $this->json([
            'id' => $specialite->getId(),
            'nom' => $specialite->getNom(),
        ], 201);
    }

    #[Route('/api/specialites/{id}', methods: ['DELETE'])]
    public function delete(Specialite $specialite, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($specialite);
        $em->flush();

        return $this->json(['message' => 'Specialite deleted']);
    }
}

