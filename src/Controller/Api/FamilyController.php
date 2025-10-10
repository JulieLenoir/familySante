<?php

namespace App\Controller\Api;

use App\Entity\Family;
use App\Repository\UserRepository;
use App\Repository\FamilyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/v1/families', name: 'api_families_')]
class FamilyController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FamilyRepository $familyRepository,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {}


    // ==========================================================
    // GET /api/families → liste toutes les familles
    // ==========================================================
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $families = $this->familyRepository->findAll();


        // On sérialise directement selon les groupes configurés
        $json = $this->serializer->serialize(
            $families,
            'json',
            ['groups' => ['family:read']]
        );

        return new JsonResponse($json, JsonResponse::HTTP_OK, [], true);
    }

    // ==========================================================
    // GET /api/families/{id} → récupère une famille par ID
    // ==========================================================
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Family $family): JsonResponse
    {
        if (!$family) {
            return $this->json(['error' => 'Family not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        $json = $this->serializer->serialize(
            $family,
            'json',
            ['groups' => ['family:read']]
        );
        return new JsonResponse($json, JsonResponse::HTTP_OK, [], true);
    }


    // ==========================================================
    // POST /api/families → crée une nouvelle famille
    // ==========================================================
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserRepository $userRepo): JsonResponse
    {

        // Désérialisation directe du JSON reçu → objet Family
        $family = $this->serializer->deserialize(
            $request->getContent(),
            Family::class,
            'json',
            ['groups' => ['family:write']]
        );

        // Validation
        $errors = $this->validator->validate($family);
        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        // Gestion des relations (récupérées par ID)
        $data = json_decode($request->getContent(), true);

        if (!isset($data['user'])) {
            return $this->json(['error' => 'Missing required related entities'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $user = $userRepo->find($data['user']);
        if (!$user) {
            return $this->json(['error' => 'User not found'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $family->setUser($user);

        $this->em->persist($family);
        $this->em->flush();


        $json = $this->serializer->serialize(
            $family,
            'json',
            ['groups' => ['family:read']]
        );

        return new JsonResponse($json, JsonResponse::HTTP_CREATED, [], true);
    }

    // ==========================================================
    // DELETE /api/families/{id} → supprime une famille par ID
    // ==========================================================
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Family $family): JsonResponse
    {
        if (!$family) {
            return $this->json(['error' => 'Family not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->em->remove($family);
        $this->em->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
