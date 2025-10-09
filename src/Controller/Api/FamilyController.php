<?php

namespace App\Controller\Api;

use App\Entity\Family;
use App\Repository\FamilyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/families', name: 'api_families_')]
class FamilyController extends AbstractController
{
    // GET /api/families → liste toutes les familles
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(FamilyRepository $familyRepository): JsonResponse
    {
        $families = $familyRepository->findAll();

        // On convertit les entités en tableau JSON simple
        $data = array_map(function (Family $family) {
            return [
                'id' => $family->getId(),
                'name' => $family->getName(),
            ];
        }, $families);

        return $this->json($data);
    }

    // GET /api/families/{id} → récupère une famille par ID
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Family $family): JsonResponse
    {
        if (!$family) {
            return $this->json(['error' => 'Family not found'], 404);
        }

        return $this->json([
            'id' => $family->getId(),
            'name' => $family->getName(),
        ]);
    }

    // POST /api/families → crée une nouvelle famille
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['name'])) {
            return $this->json(['error' => 'Name is required'], 400);
        }

        $family = new Family();
        $family->setName($data['name']);

        $em->persist($family);
        $em->flush();

        return $this->json([
            'message' => 'Family created successfully',
            'id' => $family->getId(),
        ], 201);
    }

    // PUT /api/families/{id} → met à jour une famille
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, Family $family, EntityManagerInterface $em): JsonResponse
    {
        if (!$family) {
            return $this->json(['error' => 'Family not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $family->setName($data['name'] ?? $family->getName());

        $em->flush();

        return $this->json(['message' => 'Family updated successfully']);
    }

    // DELETE /api/families/{id} → supprime une famille
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Family $family, EntityManagerInterface $em): JsonResponse
    {
        if (!$family) {
            return $this->json(['error' => 'Family not found'], 404);
        }

        $em->remove($family);
        $em->flush();

        return $this->json(['message' => 'Family deleted successfully']);
    }
}
