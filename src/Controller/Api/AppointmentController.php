<?php

namespace App\Controller\Api;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Repository\AppointmentTypeRepository;
use App\Repository\ProfessionalRepository;
use App\Repository\FamilyMemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/appointments', name: 'api_appointments_')]
class AppointmentController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AppointmentRepository $appointmentRepository,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {}

    // =========================================================
    // GET /api/v1/appointments
    // =========================================================
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $appointments = $this->appointmentRepository->findAll();

        // On sérialise directement selon les groupes configurés
        $json = $this->serializer->serialize(
            $appointments,
            'json',
            ['groups' => ['appointment:read']]
        );

        return new JsonResponse($json, JsonResponse::HTTP_OK, [], true);
    }

    // =========================================================
    // GET /api/v1/appointments/{id}
    // =========================================================
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Appointment $appointment): JsonResponse
    {
        if (!$appointment) {
            return $this->json(['error' => 'Appointment not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $json = $this->serializer->serialize(
            $appointment,
            'json',
            ['groups' => ['appointment:read']]
        );

        return new JsonResponse($json, JsonResponse::HTTP_OK, [], true);
    }

    // =========================================================
    // POST /api/v1/appointments
    // =========================================================
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        AppointmentTypeRepository $typeRepo,
        ProfessionalRepository $proRepo,
        FamilyMemberRepository $familyRepo
    ): JsonResponse {
        // Désérialisation directe du JSON reçu → objet Appointment
        $appointment = $this->serializer->deserialize(
            $request->getContent(),
            Appointment::class,
            'json',
            ['groups' => ['appointment:write']]
        );

        // Validation
        $errors = $this->validator->validate($appointment);
        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        // Gestion des relations (récupérées par ID)
        $data = json_decode($request->getContent(), true);

        if (!isset($data['appointmentType'], $data['professional'], $data['familyMember'])) {
            return $this->json(['error' => 'Missing required related entities'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $appointmentType = $typeRepo->find($data['appointmentType']);
        $professional = $proRepo->find($data['professional']);
        $familyMember = $familyRepo->find($data['familyMember']);

        if (!$appointmentType || !$professional || !$familyMember) {
            return $this->json(['error' => 'Invalid related entity ID(s)'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $appointment->setAppointmentType($appointmentType);
        $appointment->setProfessional($professional);
        $appointment->setFamilyMember($familyMember);

        // Sauvegarde
        $this->em->persist($appointment);
        $this->em->flush();

        $json = $this->serializer->serialize(
            $appointment,
            'json',
            ['groups' => ['appointment:read']]
        );

        return new JsonResponse($json, JsonResponse::HTTP_CREATED, [], true);
    }

    // =========================================================
    // DELETE /api/v1/appointments/{id}
    // =========================================================
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Appointment $appointment): JsonResponse
    {
        if (!$appointment) {
            return $this->json(['error' => 'Appointment not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->em->remove($appointment);
        $this->em->flush();

        return $this->json(['message' => 'Appointment deleted'], JsonResponse::HTTP_NO_CONTENT);
    }
}
