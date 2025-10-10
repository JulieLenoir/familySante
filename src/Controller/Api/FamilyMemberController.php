<?php

namespace App\Controller\Api;

use App\Entity\FamilyMember;
use App\Entity\Family;
use App\Repository\FamilyMemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/family-members', name: 'api_family_members_')]
class FamilyMemberController extends AbstractController
{
    // GET /api/family-members → liste tous les membres de la famille
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(FamilyMemberRepository $familyMemberRepository): JsonResponse
    {
        $familyMembers = $familyMemberRepository->findAll();

        // On convertit les entités en tableau JSON simple
        $data = array_map(function (FamilyMember $familyMember) {
            return [
                'id' => $familyMember->getId(),
                'firstName' => $familyMember->getFirstName(),
                'lastName' => $familyMember->getLastName(),
                'birthDate' => $familyMember->getBirthDate() ? $familyMember->getBirthDate()->format('Y-m-d') : null,
                'family' => $familyMember->getFamily() ? $familyMember->getFamily()->getName() : null,
                'relation' => $familyMember->getRelation(),
            ];
        }, $familyMembers);
        return $this->json($data);
    }
    // GET /api/family-members/{id} → récupère un membre de la famille par ID
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(FamilyMember $familyMember): JsonResponse
    {
        if (!$familyMember) {
            return $this->json(['error' => 'Family member not found'], 404);
        }

        return $this->json([
            'id' => $familyMember->getId(),
            'firstName' => $familyMember->getFirstName(),
            'lastName' => $familyMember->getLastName(),
            'birthDate' => $familyMember->getBirthDate() ? $familyMember->getBirthDate()->format('Y-m-d') : null,
            'family' => $familyMember->getFamily() ? $familyMember->getFamily()->getName() : null,
            'relation' => $familyMember->getRelation(),
        ]);
    }
    // POST /api/family-members → crée un nouveau membre de la famille
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['firstName']) || empty($data['lastName'])) {
            return $this->json(['error' => 'First name and last name are required'], 400);
        }

        $familyMember = new FamilyMember();
        $familyMember->setFirstName($data['firstName']);
        $familyMember->setLastName($data['lastName']);
        if (!empty($data['birthDate'])) {
            $familyMember->setBirthDate(new \DateTime($data['birthDate']));
        }
        if (!empty($data['relation'])) {
            $familyMember->setRelation($data['relation']);
        }
        if (!empty($data['family_id'])) {
            $family = $em->getRepository(Family::class)->find($data['family_id']);
            if ($family) {
                $familyMember->setFamily($family);
            } else {
                return $this->json(['error' => 'Family not found'], 400);
            }
        }




        $em->persist($familyMember);
        $em->flush();

        return $this->json([
            'message' => 'Family member created successfully',
            'id' => $familyMember->getId(),
        ], 201);
    }
    // PUT /api/family-members/{id} → met à jour un membre de la famille
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, FamilyMember $familyMember, EntityManagerInterface $em): JsonResponse
    {
        if (!$familyMember) {
            return $this->json(['error' => 'Family member not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $familyMember->setFirstName($data['firstName'] ?? $familyMember->getFirstName());
        $familyMember->setLastName($data['lastName'] ?? $familyMember->getLastName());
        if (isset($data['birthDate'])) {
            $familyMember->setBirthDate($data['birthDate'] ? new \DateTime($data['birthDate']) : null);
        }
        $familyMember->setRelation($data['relation'] ?? $familyMember->getRelation());

        $em->flush();

        return $this->json(['message' => 'Family member updated successfully']);
    }
    // DELETE /api/family-members/{id} → supprime un membre de la famille
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(FamilyMember $familyMember, EntityManagerInterface $em): JsonResponse
    {
        if (!$familyMember) {
            return $this->json(['error' => 'Family member not found'], 404);
        }

        $em->remove($familyMember);
        $em->flush();

        return $this->json(['message' => 'Family member deleted successfully']);
    }
}
