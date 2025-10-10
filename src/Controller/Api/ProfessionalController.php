<?php

namespace App\Controller\Api;

use App\Entity\Professional;
use App\Repository\ProfessionalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur REST basique pour l'entité Professional.
 * - Routes en attributs (prefix: /api/professionals)
 * - Réponses JSON via $this->json()
 */
#[Route('/api/professionals', name: 'api_professionals_')]
class ProfessionalController extends AbstractController
{
    /**
     * GET /api/professionals
     * Liste tous les professionnels
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(ProfessionalRepository $repo): JsonResponse
    {
        $professionals = $repo->findAll();

        // Projection manuelle : on renvoie seulement les champs pertinents
        $data = array_map(function (Professional $p) {
            return [
                'id' => $p->getId(),
                'name' => $p->getName(),
                'speciality' => $p->getSpeciality(),
                'city' => $p->getCity(),
                'bookingLink' => $p->getBookingLink(),
            ];
        }, $professionals);

        return $this->json($data);
    }

    /**
     * GET /api/professionals/{id}
     * Détail d'un pro (param conversion : Professional $professional).
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Professional $professional): JsonResponse
    {
        $data = [
            'id' => $professional->getId(),
            'name' => $professional->getName(),
            'speciality' => $professional->getSpeciality(),
            'city' => $professional->getCity(),
            'bookingLink' => $professional->getBookingLink(),
            'address' => $professional->getAddress(),
            'phone' => $professional->getPhone(),
            'additionalInformation' => $professional->getAdditionalInformation(),
        ];

        return $this->json($data);
    }

    /**
     * POST /api/professionals
     * Crée un professionnel.
     * - vérification minimale des champs obligatoires (name, speciality, city)
     * - déduplication : cherche un pro identique (name+speciality+city) avant création
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ProfessionalRepository $repository
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'JSON invalide'], Response::HTTP_BAD_REQUEST);
        }

        // Validation minimale
        $name = trim((string)($payload['name'] ?? ''));
        $speciality = trim((string)($payload['speciality'] ?? ''));
        $city = trim((string)($payload['city'] ?? ''));

        if ($name === '' || $speciality === '' || $city === '') {
            return $this->json(
                ['error' => 'Les champs name, speciality et city sont requis.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Déduplication
        $existing = $repository->findOneBy([
            'name' => $name,
            'speciality' => $speciality,
            'city' => $city,
        ]);

        if ($existing) {
            // On renvoie 409 Conflict + id existant pour que le front puisse réutiliser l'enregistrement
            return $this->json(
                ['message' => 'Ce professionnel existe déjà.', 'id' => $existing->getId()],
                Response::HTTP_CONFLICT
            );
        }

        // Création
        $professional = new Professional();
        $professional->setName($name);
        $professional->setSpeciality($speciality);
        $professional->setCity($city);
        $professional->setBookingLink($payload['bookingLink'] ?? null);
        $professional->setAddress($payload['address'] ?? null);
        $professional->setPhone($payload['phone'] ?? null);
        $professional->setAdditionalInformation($payload['additionalInformation'] ?? null);

        $em->persist($professional);
        $em->flush();

        return $this->json(['id' => $professional->getId()], Response::HTTP_CREATED);
    }

    /**
     * PUT/PATCH /api/professionals/{id}
     * Mise à jour partielle (PATCH) ou complète (PUT) d'un pro.
     */
    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(
        Request $request,
        Professional $professional,
        EntityManagerInterface $em
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'JSON invalide'], Response::HTTP_BAD_REQUEST);
        }

        // Mise à jour conditionnelle: on modifie uniquement les champs présents
        if (array_key_exists('name', $payload)) {
            $professional->setName((string)$payload['name']);
        }
        if (array_key_exists('speciality', $payload)) {
            $professional->setSpeciality((string)$payload['speciality']);
        }
        if (array_key_exists('city', $payload)) {
            $professional->setCity((string)$payload['city']);
        }
        if (array_key_exists('bookingLink', $payload)) {
            $professional->setBookingLink($payload['bookingLink']);
        }
        if (array_key_exists('address', $payload)) {
            $professional->setAddress($payload['address']);
        }
        if (array_key_exists('phone', $payload)) {
            $professional->setPhone($payload['phone']);
        }
        if (array_key_exists('additionalInformation', $payload)) {
            $professional->setAdditionalInformation($payload['additionalInformation']);
        }

        $em->flush();

        return $this->json(['message' => 'Mise à jour effectuée.']);
    }

    // /**
    //  * DELETE /api/professionals/{id}
    //  * Supprime un professionnel.
    //  */
    // #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    // public function delete(Professional $professional, EntityManagerInterface $em): JsonResponse
    // {
    //     $em->remove($professional);
    //     $em->flush();

    //     // 204 No Content est classique pour une suppression REST
    //     return $this->json(null, Response::HTTP_NO_CONTENT);
    // }
}
