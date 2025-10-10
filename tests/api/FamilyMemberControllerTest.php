<?php


namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class FamilyMemberControllerTest extends WebTestCase
{

    public function testListFamilieMembers(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/family-members');

        $this->assertResponseIsSuccessful(); // 2xx
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateFamilyMember(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/family-members',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'birthDate' => '1980-01-01',
                'relation' => 'Père',
                'family_id' => 1
            ])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('Family member created successfully', $data['message']);
    }

    public function testShowFamilyMember(): void
    {
        $client = static::createClient();

        // Étape 1 : créer un membre de la famille
        $client->request(
            'POST',
            '/api/family-members',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'Marie',
                'lastName' => 'Dupont',
                'birthDate' => '2010-05-15',
                'relation' => 'Fille',
                'family_id' => 1
            ])
        );

        $familyMemberId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Étape 2 : récupérer le membre de la famille
        $client->request('GET', "/api/family-members/$familyMemberId");

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Marie', $data['firstName']);
        $this->assertSame('Dupont', $data['lastName']);
        $this->assertSame('2010-05-15', $data['birthDate']);
        $this->assertSame('Fille', $data['relation']);
    }

    public function testUpdateFamilyMember(): void
    {
        $client = static::createClient();

        // Créer un membre de la famille
        $client->request(
            'POST',
            '/api/family-members',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'Paul',
                'lastName' => 'Martin',
                'birthDate' => '1975-03-20',
                'relation' => 'Oncle',
                'family_id' => 1
            ])
        );

        $familyMemberId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Mettre à jour le membre de la famille
        $client->request(
            'PUT',
            "/api/family-members/$familyMemberId",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'Pauline',
                'relation' => 'Tante'
            ])
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Family member updated successfully', $data['message']);

        // Vérifier la mise à jour
        $client->request('GET', "/api/family-members/$familyMemberId");
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Pauline', $data['firstName']);
        $this->assertSame('Tante', $data['relation']);
    }

    public function testDeleteFamilyMember(): void
    {
        $client = static::createClient();

        // Créer un membre de la famille
        $client->request(
            'POST',
            '/api/family-members',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'Luc',
                'lastName' => 'Bernard',
                'birthDate' => '1965-07-30',
                'relation' => 'Grand-père',
                'family_id' => 1
            ])
        );

        $familyMemberId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Supprimer le membre de la famille
        $client->request('DELETE', "/api/family-members/$familyMemberId");

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Family member deleted successfully', $data['message']);

        // Vérifier la suppression
        $client->request('GET', "/api/family-members/$familyMemberId");
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
