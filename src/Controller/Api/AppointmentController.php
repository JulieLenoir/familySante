<?php


namespace App\Controller\Api;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/appointments', name: 'api_appointments_')]
class AppointmentController extends AbstractController
{
    // GET /api/appointments → liste tous les rendez-vous
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(AppointmentRepository $appointmentRepository): JsonResponse
    {
        $appointments = $appointmentRepository->findAll();


        $data = array_map(function (Appointment $appointment) {
            return [
                'id' => $appointment->getId(),
                'title' => $appointment->getTitle(),
                'date' => $appointment->getDate()->format('Y-m-d H:i:s'),
                'status' => $appointment->getStatus(),
                'type' => $appointment->getAppointmentType(),
                'professional' => $appointment->getProfessional()->getName(),
                'family' => $appointment->getFamilyMember()->getFirstName(),
                'notes' => $appointment->getNote(),
            ];
        }, $appointments);

        return $this->json($data);
    }
    // GET /api/appointments/{id} → récupère un rendez-vous par ID
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Appointment $appointment): JsonResponse
    {
        if (!$appointment) {
            return $this->json(['error' => 'Appointment not found'], 404);
        }

        return $this->json([
            'id' => $appointment->getId(),
            'title' => $appointment->getTitle(),
            'date' => $appointment->getDate()->format('Y-m-d H:i:s'),
            'status' => $appointment->getStatus(),
            'type' => $appointment->getAppointmentType(),
            'professional' => $appointment->getProfessional()->getName(),
            'family' => $appointment->getFamilyMember()->getFirstName(),
            'notes' => $appointment->getNote(),
        ]);
    }

    // POST /api/appointments → crée un nouveau rendez-vous
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['title']) || empty($data['date']) || empty($data['status']) || empty($data['type']) || empty($data['professional_id']) || empty($data['family_id'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $appointment = new Appointment();
        $appointment->setTitle($data['title']);
        $appointment->setDate(new \DateTime($data['date']));
        $appointment->setStatus($data['status']);
        $appointment->setAppointmentType($data['type']);

        $appointment->setNote($data['notes'] ?? null);

        $em->persist($appointment);
        $em->flush();

        return $this->json(['message' => 'Appointment created', 'id' => $appointment->getId()], 201);
    }

    // DELETE /api/appointments/{id} → supprime un rendez-vous
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Appointment $appointment, EntityManagerInterface $em): JsonResponse
    {
        if (!$appointment) {
            return $this->json(['error' => 'Appointment not found'], 404);
        }

        $em->remove($appointment);
        $em->flush();

        return $this->json(['message' => 'Appointment deleted'], 204);
    }
}
