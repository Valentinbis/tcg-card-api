<?php

namespace App\Tests\Controller\API;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Tests\BaseWebTestCase;
use Liip\TestFixturesBundle\Test\FixturesTrait;

class UserControllerTest extends BaseWebTestCase
{
    // use FixturesTrait;

    // private $userRepository;

    // protected function setUp(): void
    // {
    //     parent::setUp(); // Initialise le client HTTP via BaseWebTestCase
    //     $this->loadFixtures([UserFixtures::class]);
    // }

    // public function testMe(): void
    // {
    //     // Simuler un utilisateur connecté
    //     $user = new User();
    //     $user->setEmail('test@example.com');

    //     // Simuler l'authentification de l'utilisateur
    //     $this->getHttpClient()->loginUser($user, 'main');

    //     // Effectuer une requête GET sur /api/me
    //     $this->getHttpClient()->request('GET', '/api/me');

    //     // Vérifier que la réponse est 200 OK
    //     $this->assertResponseIsSuccessful();

    //     // Vérifier que la réponse contient bien l'utilisateur connecté
    //     $responseContent = $this->getHttpClient()->getResponse()->getContent();
    //     $responseData = json_decode($responseContent, true);

    //     $this->assertEquals('test@example.com', $responseData['email']);
    // }

    // public function testUsers()
    // {
    //     // Mock de plusieurs utilisateurs
    //     $users = [
    //         (new User())->setEmail('user1@example.com'),
    //         (new User())->setEmail('user2@example.com')
    //     ];

    //     // Configurer le mock du repository pour retourner les utilisateurs
    //     $this->userRepository
    //         ->method('findAll')
    //         ->willReturn($users);

    //     // Simuler l'authentification de l'utilisateur
    //     $authenticatedUser = new User();
    //     $authenticatedUser->setEmail('test@example.com');
    //     $this->client->loginUser($authenticatedUser, 'main');

    //     // Effectuer une requête GET sur /api/users
    //     $this->client->request('GET', '/api/users');

    //     // Vérifier que la réponse est 200 OK
    //     $this->assertResponseIsSuccessful();

    //     // Vérifier le contenu JSON
    //     $responseContent = $this->client->getResponse()->getContent();
    //     $responseData = json_decode($responseContent, true);

    //     // Vérifier que deux utilisateurs sont retournés
    //     $this->assertCount(2, $responseData);

    //     // Vérifier que les emails des utilisateurs sont corrects
    //     $this->assertEquals('user1@example.com', $responseData[0]['email']);
    //     $this->assertEquals('user2@example.com', $responseData[1]['email']);
    // }

    // public function testUser()
    // {
    //     // Mock de plusieurs utilisateurs avec des IDs différents
    //     $users = [
    //         1 => (new User())->setEmail('user1@example.com'),
    //         2 => (new User())->setEmail('user2@example.com')
    //     ];

    //     // Configurer le mock du repository pour retourner l'utilisateur en fonction de l'ID
    //     $this->userRepository
    //         ->method('find')
    //         ->will($this->returnCallback(function ($id) use ($users) {
    //             return $users[$id] ?? null;
    //         }));
        
    //     // Simuler l'authentification de l'utilisateur
    //     $authenticatedUser = new User();
    //     $authenticatedUser->setEmail('test@example.com');
    //     $authenticatedUser->setRoles(['ROLE_USER']); // Ajoute le rôle nécessaire pour cette route
    //     $this->client->loginUser($authenticatedUser, 'main');

    //     // Effectuer une requête GET sur /api/user/1
    //     $this->client->request('GET', '/api/user/1');

    //     // Vérifier que la réponse est 200 OK
    //     $this->assertResponseIsSuccessful();

    //     // Vérifier le contenu JSON
    //     $responseContent = $this->client->getResponse()->getContent();
    //     $responseData = json_decode($responseContent, true);

    //     // Vérifier que l'email de l'utilisateur est correct
    //     $this->assertEquals('user1@example.com', $responseData['email']);

    //     // Effectuer une requête GET sur /api/user/2
    //     $this->client->request('GET', '/api/user/2');

    //     // Vérifier que la réponse est 200 OK
    //     $this->assertResponseIsSuccessful();

    //     // Vérifier le contenu JSON
    //     $responseContent = $this->client->getResponse()->getContent();
    //     $responseData = json_decode($responseContent, true);

    //     // Vérifier que l'email de l'utilisateur est correct
    //     $this->assertEquals('user2@example.com', $responseData['email']);
    // }
}
