<?php

namespace App\Controller\Api;

use App\Entity\User;

use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Contrôleur REST basique pour l'entité User.
 * - Routes en attributs (prefix: /api/users)
 * - Réponses JSON via $this->json()
 */


#[Route('/api/v1/users', name: 'api_users_')]
class UserController extends AbstractController
{
    /**
     * GET /api/users
     */

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(UserRepository $repo): JsonResponse
    {
        $users = $repo->findAll();

        // Projection manuelle : on renvoie seulement les champs pertinents
        $data = array_map(function (User $u) {
            return [
                'id' => $u->getId(),
                'email' => $u->getEmail(),
                'roles' => $u->getRoles(),
            ];
        }, $users);

        return $this->json($data);
    }

    /**
     * GET /api/users/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }

    /**
     * DELETE /api/users/{id}
     */

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(User $user, UserRepository $repo): JsonResponse
    {
        $repo->remove($user, true);

        return $this->json(null, 204);
    }
}
