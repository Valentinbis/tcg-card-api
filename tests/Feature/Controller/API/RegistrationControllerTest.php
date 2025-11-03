<?php

namespace App\Tests\Feature\Controller\API;

use App\Entity\User;
use App\Tests\BaseWebTestCase;

final class RegistrationControllerTest extends BaseWebTestCase
{
    public function testRegisterCreatesNewUser(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'newuser@test.com',
            'password' => 'Password123!',
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]));

        self::assertResponseIsSuccessful();
        $data = $this->assertJsonResponse($client);
        
        self::assertArrayHasKey('email', $data);
        self::assertSame('newuser@test.com', $data['email']);
        
        // Vérifier que l'utilisateur est bien créé en base
        $user = $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['email' => 'newuser@test.com']);
        
        self::assertNotNull($user);
        self::assertSame('John', $user->getFirstName());
        self::assertSame('Doe', $user->getLastName());
    }

    public function testRegisterWithEmptyBodyReturnsBadRequest(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '');

        self::assertResponseStatusCodeSame(400);
    }

    public function testRegisterWithInvalidDataReturnsBadRequest(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'invalid-email', // Email invalide
            'password' => 'short', // Mot de passe trop court
        ]));

        self::assertResponseStatusCodeSame(400);
    }

    public function testLoginReturnsTokenForValidCredentials(): void
    {
        $client = static::createClient();
        
        // Créer d'abord un utilisateur
        self::bootKernel();
        self::$kernelBooted = true;
        $this->createTestUser(['ROLE_USER'], 'login@test.com');
        
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'login@test.com',
            'password' => 'password123', // Mot de passe défini dans createTestUser
        ]));

        self::assertResponseIsSuccessful();
        $data = $this->assertJsonResponse($client);
        
        self::assertArrayHasKey('email', $data);
        self::assertArrayHasKey('apiToken', $data);
        self::assertNotEmpty($data['apiToken']);
    }

    public function testLoginWithInvalidCredentialsReturnsUnauthorized(): void
    {
        $client = static::createClient();
        
        // Créer un utilisateur
        self::bootKernel();
        self::$kernelBooted = true;
        $this->createTestUser(['ROLE_USER'], 'login@test.com');
        
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'login@test.com',
            'password' => 'wrongpassword',
        ]));

        self::assertResponseStatusCodeSame(401);
        $data = $this->assertJsonResponse($client, 401);
        self::assertArrayHasKey('error', $data);
    }

    public function testLoginWithMissingEmailReturnsBadRequest(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'password' => 'password123',
        ]));

        self::assertResponseStatusCodeSame(400);
    }

    public function testLoginWithMissingPasswordReturnsBadRequest(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@test.com',
        ]));

        self::assertResponseStatusCodeSame(400);
    }

    public function testLogoutInvalidatesToken(): void
    {
        $client = static::createClient();
        
        // Créer un utilisateur
        self::bootKernel();
        self::$kernelBooted = true;
        $user = $this->createTestUser(['ROLE_USER'], 'logout@test.com');
        $originalToken = $user->getApiToken();
        
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $originalToken);
        $client->request('GET', '/api/logout');

        self::assertResponseIsSuccessful();
        $data = $this->assertJsonResponse($client);
        self::assertArrayHasKey('message', $data);
        
        // Vérifier que le token a été invalidé
        $this->getEntityManager()->refresh($user);
        self::assertSame('', $user->getApiToken());
    }

    public function testLogoutWithInvalidTokenReturnsUnauthorized(): void
    {
        $client = static::createClient();
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer invalid-token');
        $client->request('GET', '/api/logout');

        self::assertResponseStatusCodeSame(401);
    }
}
