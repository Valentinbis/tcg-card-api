<?php

namespace App\Tests\Feature\Controller\API;

use App\Entity\User;
use App\Tests\BaseWebTestCase;

final class UserControllerTest extends BaseWebTestCase
{
    public function testMeReturnsCurrentUserProfile(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/me');

        self::assertResponseIsSuccessful();
        $data = $this->assertJsonResponse($client);
        
        self::assertArrayHasKey('email', $data);
        self::assertArrayHasKey('firstName', $data);
        self::assertArrayHasKey('lastName', $data);
    }

    public function testMeRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/me');

        self::assertResponseStatusCodeSame(401);
    }

    public function testGetCurrentUserReturnsUserDetails(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/user/me');

        self::assertResponseIsSuccessful();
        $data = $this->assertJsonResponse($client);
        
        self::assertArrayHasKey('id', $data);
        self::assertArrayHasKey('email', $data);
        self::assertArrayHasKey('firstName', $data);
        self::assertArrayHasKey('lastName', $data);
        self::assertArrayHasKey('apiToken', $data);
        self::assertArrayHasKey('roles', $data);
        self::assertContains('ROLE_USER', $data['roles']);
    }

    public function testGetUsersReturnsUsersList(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/users');

        self::assertResponseIsSuccessful();
        $data = $this->assertJsonResponse($client);
        
        self::assertIsArray($data);
        self::assertGreaterThanOrEqual(1, count($data)); // Au moins l'utilisateur authentifié
    }

    public function testGetUserByIdReturnsUserDetails(): void
    {
        $client = $this->createAuthenticatedClient();
        
        // Récupérer l'ID de l'utilisateur authentifié
        $client->request('GET', '/api/me');
        $meData = json_decode($client->getResponse()->getContent(), true);
        $userId = $meData['id'];
        
        $client->request('GET', '/api/user/' . $userId);

        self::assertResponseIsSuccessful();
        $data = $this->assertJsonResponse($client);
        
        self::assertArrayHasKey('email', $data);
    }

    public function testGetUserByIdRequiresAuthentication(): void
    {
        // Créer d'abord un utilisateur pour avoir un ID valide
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/me');
        $meData = json_decode($client->getResponse()->getContent(), true);
        $userId = $meData['id'];
        
        // Essayer d'accéder sans authentification
        $unauthClient = static::createClient();
        $unauthClient->request('GET', '/api/user/' . $userId);

        self::assertResponseStatusCodeSame(401);
    }

    public function testDeleteUserRequiresAdminRole(): void
    {
        // Créer un utilisateur normal pour récupérer un ID
        $client = $this->createAuthenticatedClient(['ROLE_USER']);
        $client->request('GET', '/api/me');
        $meData = json_decode($client->getResponse()->getContent(), true);
        $userId = $meData['id'];
        
        // Utilisateur normal essaie de supprimer
        $client->request('DELETE', '/api/user/' . $userId);

        self::assertResponseStatusCodeSame(403); // Forbidden
    }

    public function testDeleteUserSucceedsWithAdminRole(): void
    {
        // Créer deux utilisateurs : un admin et un à supprimer
        $adminClient = $this->createAuthenticatedClient(['ROLE_ADMIN']);
        
        // Créer un second client pour avoir un autre utilisateur
        $normalUserClient = static::createClient();
        self::$kernelBooted = false;
        $userToDelete = $this->createTestUser(['ROLE_USER'], 'todelete@test.com');
        $userIdToDelete = $userToDelete->getId();
        
        // Admin supprime l'utilisateur
        $adminClient->request('DELETE', '/api/user/' . $userIdToDelete);

        self::assertResponseStatusCodeSame(204); // No Content
        
        // Vérifier que l'utilisateur a bien été supprimé
        $this->getEntityManager()->clear();
        $deletedUser = $this->getEntityManager()
            ->getRepository(User::class)
            ->find($userIdToDelete);
        
        self::assertNull($deletedUser);
    }

    public function testUpdateUserSucceeds(): void
    {
        $client = $this->createAuthenticatedClient(['ROLE_USER']);
        
        // Récupérer l'ID de l'utilisateur
        $client->request('GET', '/api/me');
        $meData = json_decode($client->getResponse()->getContent(), true);
        $userId = $meData['id'];
        
        $client->request('PUT', '/api/user/' . $userId, [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'firstName' => 'Updated',
            'lastName' => 'Name',
        ]));

        self::assertResponseIsSuccessful();
    }

    public function testUpdateUserRequiresAuthentication(): void
    {
        // Créer un utilisateur pour avoir un ID
        $authClient = $this->createAuthenticatedClient(['ROLE_USER']);
        $authClient->request('GET', '/api/me');
        $meData = json_decode($authClient->getResponse()->getContent(), true);
        $userId = $meData['id'];
        
        $client = static::createClient();
        $client->request('PUT', '/api/user/' . $userId, [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'firstName' => 'Updated',
        ]));

        self::assertResponseStatusCodeSame(401);
    }
}
