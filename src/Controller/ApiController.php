<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }

    #[Route('/api/test', name: 'api_test')]
    public function test(): Response
    {
        return $this->json([
            'status' => 'ok',
            'message' => 'Bienvenue sur ton API Symfony sous WAMP !'
        ]);
    }
}
