<?php

namespace App\Controller\Api;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/notifications')]
class NotificationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private NotificationRepository $notificationRepository,
        private AppointmentRepository $appointmentRepository
    ) {}

    /**
     * GET /api/notifications
     * Liste toutes les notifications
     */
    #[Route('', name: 'get_notifications', methods: ['GET'])]
    public function getNotifications(): JsonResponse
    {
        $notifications = $this->notificationRepository->findAll();

        $data = array_map(fn(Notification $n) => [
            'id' => $n->getId(),
            'remindAt' => $n->getRemindAt()?->format('Y-m-d H:i:s'),
            'channel' => $n->getChannel(),
            'status' => $n->getStatus(),
            'appointment' => $n->getAppointment()?->getId(),
        ], $notifications);

        return $this->json($data);
    }

    /**
     * POST /api/notifications
     * Crée une nouvelle notification
     */
    #[Route('', name: 'create_notification', methods: ['POST'])]
    public function createNotification(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation de base
        if (
            empty($data['remindAt']) ||
            empty($data['channel']) ||
            empty($data['status']) ||
            empty($data['appointmentId'])
        ) {
            return $this->json(['error' => 'Les champs remindAt, channel, status et appointmentId sont requis'], 400);
        }

        // Vérifie que le rendez-vous existe
        $appointment = $this->appointmentRepository->find($data['appointmentId']);
        if (!$appointment) {
            return $this->json(['error' => 'Appointment non trouvé'], 404);
        }

        // Création de l'entité
        $notification = new Notification();
        $notification->setRemindAt(new \DateTime($data['remindAt']));
        $notification->setChannel($data['channel']);
        $notification->setStatus($data['status']);
        $notification->setAppointment($appointment);

        $this->em->persist($notification);
        $this->em->flush();

        // Retour JSON
        return $this->json([
            'id' => $notification->getId(),
            'remindAt' => $notification->getRemindAt()->format('Y-m-d H:i:s'),
            'channel' => $notification->getChannel(),
            'status' => $notification->getStatus(),
            'appointment' => $notification->getAppointment()->getId(),
        ], 201);
    }
}
