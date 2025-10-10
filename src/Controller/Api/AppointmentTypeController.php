<?php

namespace App\Controller\Api;

use App\Entity\AppointmentType;
use App\Repository\AppointmentTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/appointment-types', name: 'api_appointments_types_')]
class AppointmentTypeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AppointmentTypeRepository $appointmentTypeRepository,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {}


    // =========================================================
    // GET /api/v1/appointments-types
    // =========================================================
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $appointmentsTypes = $this->appointmentTypeRepository->findAll();

        // On sérialise directement selon les groupes configurés
        $json = $this->serializer->serialize(
            $appointmentsTypes,
            'json',
            ['groups' => ['appointmentType:read']]
        );

        return new JsonResponse($json, JsonResponse::HTTP_OK, [], true);
    }


    // =========================================================
    // GET /api/v1/appointments-types/{id}
    // =========================================================
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(AppointmentType $appointmentType): JsonResponse
    {
        if (!$appointmentType) {
            return $this->json(['error' => 'Appointment not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $json = $this->serializer->serialize(
            $appointmentType,
            'json',
            ['groups' => ['appointmentType:read']]
        );

        return new JsonResponse($json, JsonResponse::HTTP_OK, [], true);
    }



    // =========================================================
    // POST /api/v1/appointment-types
    // =========================================================

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $appointmentType = $this->serializer->deserialize(
            $request->getContent(),
            AppointmentType::class,
            'json',
            ['groups' => ['appointmentType:write']]
        );

        // Validation
        $errors = $this->validator->validate($appointmentType);
        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        // Gestion des relations (récupérées par ID)
        $data = json_decode($request->getContent(), true);

        $appointmentType = new AppointmentType();
        $appointmentType->setName($data['name']);
        $appointmentType->setDescription($data['description'] ?? null);


        // Sauvegarde
        $this->em->persist($appointmentType);
        $this->em->flush();

        $json = $this->serializer->serialize(
            $appointmentType,
            'json',
            ['groups' => ['appointmentType:read']]
        );

        return new JsonResponse($json, JsonResponse::HTTP_CREATED, [], true);
    }


    // ATTENTION : les méthodes update et delete sont commentées car non utilisées pour l'instant ET non serialisees
    // // PUT /api/appointment-types/{id} → met à jour un type de rendez-vous
    // #[Route('/{id}', name: 'update', methods: ['PUT'])]
    // public function update(Request $request, AppointmentType $appointmentType, EntityManagerInterface $em): JsonResponse
    // {
    //     if (!$appointmentType) {
    //         return $this->json(['error' => 'Appointment Type not found'], 404);
    //     }

    //     $data = json_decode($request->getContent(), true);

    //     if (isset($data['name'])) {
    //         $appointmentType->setName($data['name']);
    //     }
    //     if (array_key_exists('description', $data)) {
    //         $appointmentType->setDescription($data['description']);
    //     }

    //     $em->flush();

    //     return $this->json([
    //         'id' => $appointmentType->getId(),
    //         'name' => $appointmentType->getName(),
    //         'description' => $appointmentType->getDescription(),
    //     ]);
    // }
    // // DELETE /api/appointment-types/{id} → supprime un type de rendez-vous
    // #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    // public function delete(AppointmentType $appointmentType, EntityManagerInterface $em): JsonResponse
    // {
    //     if (!$appointmentType) {
    //         return $this->json(['error' => 'Appointment Type not found'], 404);
    //     }

    //     $em->remove($appointmentType);
    //     $em->flush();

    //     return $this->json(['message' => 'Appointment Type deleted successfully']);
    // }
}
