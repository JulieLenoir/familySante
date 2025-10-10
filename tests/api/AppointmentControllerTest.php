<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AppointmentControllerTest extends WebTestCase
{
    public function testListAppointments(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/appointments');

        $this->assertResponseIsSuccessful(); // 2xx
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testCreateAppointment(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/appointments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'Rendez-vous Test',
                'date' => '2024-12-01T10:00:00+00:00',
                'location' => 'Cabinet Médical',
                'notes' => 'Apporter les résultats des analyses',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('Appointment created successfully', $data['message']);
    }

    public function testShowAppointment(): void
    {
        $client = static::createClient();

        // Étape 1 : créer un rendez-vous
        $client->request(
            'POST',
            '/api/appointments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'Rendez-vous Test',
                'date' => '2024-12-01T10:00:00+00:00',
                'location' => 'Cabinet Médical',
                'notes' => 'Apporter les résultats des analyses',
            ])
        );

        $appointmentId = json_decode($client->getResponse()->getContent(), true)['id'];


        // Étape 2 : récupérer le rendez-vous
        $client->request('GET', "/api/appointments/$appointmentId");

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $appointmentData = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Rendez-vous Test', $appointmentData['title']);
        $this->assertSame('2024-12-01T10:00:00+00:00', $appointmentData['date']);
        $this->assertSame('Cabinet Médical', $appointmentData['location']);
        $this->assertSame('Apporter les résultats des analyses', $appointmentData['notes']);
    }


    // public function testUpdateAppointment(): void
    // {
    //     $client = static::createClient();

    //     // Créer un rendez-vous
    //     $client->request(
    //         'POST',
    //         '/api/appointments',
    //         [],
    //         [],
    //         ['CONTENT_TYPE' => 'application/json'],
    //         json_encode([
    //             'title' => 'Rendez-vous Initial',
    //             'date' => '2024-12-01T10:00:00+00:00',
    //             'location' => 'Cabinet Médical',
    //             'notes' => 'Apporter les résultats des analyses',
    //         ])
    //     );
    //     $appointmentId = json_decode($client->getResponse()->getContent(), true)['id'];

    //     // Mettre à jour ce rendez-vous
    //     $client->request(
    //         'PUT',
    //         "/api/appointments/$appointmentId",
    //         [],
    //         [],
    //         ['CONTENT_TYPE' => 'application/json'],
    //         json_encode([
    //             'title' => 'Rendez-vous Mis à Jour',
    //             'date' => '2024-12-02T11:00:00+00:00',
    //             'location' => 'Hôpital',
    //             'notes' => 'Nouveau lieu et heure',
    //         ])
    //     );

    //     $this->assertResponseIsSuccessful();
    //     $this->assertResponseStatusCodeSame(Response::HTTP_OK);

    //     $data = json_decode($client->getResponse()->getContent(), true);
    //     $this->assertSame('Appointment updated successfully', $data['message']);

    //     // Vérifier que les modifications ont été prises en compte
    //     $client->request('GET', "/api/appointments/$appointmentId");
    //     $appointmentData = json_decode($client->getResponse()->getContent(), true);
    //     $this->assertSame('Rendez-vous Mis à Jour', $appointmentData['title']);
    //     $this->assertSame('2024-12-02T11:00:00+00:00', $appointmentData['date']);
    //     $this->assertSame('Hôpital', $appointmentData['location']);
    //     $this->assertSame('Nouveau lieu et heure', $appointmentData['notes']);
    // }
    // public function testDeleteAppointment(): void
    // {
    //     $client = static::createClient();

    //     // Créer un rendez-vous
    //     $client->request(
    //         'POST',
    //         '/api/appointments',
    //         [],
    //         [],
    //         ['CONTENT_TYPE' => 'application/json'],
    //         json_encode([
    //             'title' => 'Rendez-vous à Supprimer',
    //             'date' => '2024-12-01T10:00:00+00:00',
    //             'location' => 'Cabinet Médical',
    //             'notes' => 'Apporter les résultats des analyses',
    //         ])
    //     );
    //     $appointmentId = json_decode($client->getResponse()->getContent(), true)['id'];

    //     // Supprimer ce rendez-vous
    //     $client->request('DELETE', "/api/appointments/$appointmentId");

    //     $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

    //     // Vérifier que le rendez-vous a été supprimé
    //     $client->request('GET', "/api/appointments/$appointmentId");
    //     $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    // }
}
