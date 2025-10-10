<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class FamilyControllerTest extends WebTestCase
{
    public function testListFamilies(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/families');

        $this->assertResponseIsSuccessful(); // 2xx
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateFamily(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/families',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'Famille Test'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('Family created successfully', $data['message']);
    }

    public function testShowFamily(): void
    {
        $client = static::createClient();

        // Étape 1 : créer une famille
        $client->request(
            'POST',
            '/api/families',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'Famille Dupont'])
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $familyId = $data['id'];

        // Étape 2 : récupérer la famille
        $client->request('GET', "/api/families/$familyId");

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame('Famille Dupont', $data['name']);
    }

    public function testUpdateFamily(): void
    {
        $client = static::createClient();

        // Créer une famille
        $client->request(
            'POST',
            '/api/families',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'Famille Originale'])
        );
        $familyId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Mettre à jour cette famille
        $client->request(
            'PUT',
            "/api/families/$familyId",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'Famille Modifiée'])
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Family updated successfully', $data['message']);
    }

    public function testDeleteFamily(): void
    {
        $client = static::createClient();

        // Créer une famille
        $client->request(
            'POST',
            '/api/families',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'Famille à supprimer'])
        );
        $familyId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Supprimer la famille
        $client->request('DELETE', "/api/families/$familyId");

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Family deleted successfully', $data['message']);

        // Vérifie qu’elle n’existe plus
        $client->request('GET', "/api/families/$familyId");
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
