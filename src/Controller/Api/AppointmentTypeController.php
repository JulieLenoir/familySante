<?php

namespace App\Controller\Api;

use App\Entity\AppointmentType;
use App\Repository\AppointmentTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/appointment-types', name: 'api_appointment_types_')]
class AppointmentTypeController extends AbstractController
{
    // GET /api/appointment-types → liste tous les types de rendez-vous
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(AppointmentTypeRepository $appointmentTypeRepository): JsonResponse
    {
        $appointmentTypes = $appointmentTypeRepository->findAll();

        // On convertit les entités en tableau JSON simple
        $data = array_map(function (AppointmentType $appointmentType) {
            return [
                'id' => $appointmentType->getId(),
                'name' => $appointmentType->getName(),
                'description' => $appointmentType->getDescription(),
            ];
        }, $appointmentTypes);

        return $this->json($data);
    }

    // GET /api/appointment-types/{id} → récupère un type de rendez-vous par ID
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(AppointmentType $appointmentType): JsonResponse
    {
        if (!$appointmentType) {
            return $this->json(['error' => 'Appointment Type not found'], 404);
        }

        return $this->json([
            'id' => $appointmentType->getId(),
            'name' => $appointmentType->getName(),
            'description' => $appointmentType->getDescription(),
        ]);
    }
    // POST /api/appointment-types → crée un nouveau type de rendez-vous
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['name'])) {
            return $this->json(['error' => 'Name is required'], 400);
        }

        $appointmentType = new AppointmentType();
        $appointmentType->setName($data['name']);
        $appointmentType->setDescription($data['description'] ?? null);

        $em->persist($appointmentType);
        $em->flush();

        return $this->json([
            'id' => $appointmentType->getId(),
            'name' => $appointmentType->getName(),
            'description' => $appointmentType->getDescription(),
        ], 201);
    }
    // PUT /api/appointment-types/{id} → met à jour un type de rendez-vous
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, AppointmentType $appointmentType, EntityManagerInterface $em): JsonResponse
    {
        if (!$appointmentType) {
            return $this->json(['error' => 'Appointment Type not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $appointmentType->setName($data['name']);
        }
        if (array_key_exists('description', $data)) {
            $appointmentType->setDescription($data['description']);
        }

        $em->flush();

        return $this->json([
            'id' => $appointmentType->getId(),
            'name' => $appointmentType->getName(),
            'description' => $appointmentType->getDescription(),
        ]);
    }
    // DELETE /api/appointment-types/{id} → supprime un type de rendez-vous
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(AppointmentType $appointmentType, EntityManagerInterface $em): JsonResponse
    {
        if (!$appointmentType) {
            return $this->json(['error' => 'Appointment Type not found'], 404);
        }

        $em->remove($appointmentType);
        $em->flush();

        return $this->json(['message' => 'Appointment Type deleted successfully']);
    }
}
