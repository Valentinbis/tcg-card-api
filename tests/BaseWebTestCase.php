<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Classe de base pour les tests Feature/Integration
 * 
 * Utilise DAMA Doctrine Test Bundle pour auto-rollback des transactions
 * Pas besoin de nettoyer manuellement la DB dans tearDown()
 */
abstract class BaseWebTestCase extends WebTestCase
{
    protected ?EntityManagerInterface $entityManager = null;
    protected static bool $kernelBooted = false;
    
    protected function setUp(): void
    {
        parent::setUp();
        self::$kernelBooted = false;
    }

    /**
     * Récupère l'EntityManager (boot le kernel si nécessaire)
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        if (null === $this->entityManager) {
            if (!self::$kernelBooted) {
                self::bootKernel();
                self::$kernelBooted = true;
            }
            $this->entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        }
        
        return $this->entityManager;
    }

    /**
     * Crée un utilisateur de test en base
     * 
     * @param array $roles Rôles de l'utilisateur (default: ROLE_USER)
     * @param string|null $email Email personnalisé (default: généré automatiquement)
     * @return User L'utilisateur créé et persisté
     */
    protected function createTestUser(array $roles = ['ROLE_USER'], ?string $email = null): User
    {
        // Boot kernel si nécessaire
        if (!self::$kernelBooted) {
            self::bootKernel();
            self::$kernelBooted = true;
        }
        
        $user = new User();
        $user->setEmail($email ?? 'test-' . uniqid() . '@example.com');
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setRoles($roles);
        $user->setApiToken('test-api-token-' . uniqid());
        
        // Hash le mot de passe
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        
        // Persiste en base
        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();
        
        return $user;
    }

    /**
     * Crée un client HTTP authentifié
     * 
     * @param array $roles Rôles de l'utilisateur
     * @return KernelBrowser Client authentifié
     */
    protected function createAuthenticatedClient(array $roles = ['ROLE_USER']): KernelBrowser
    {
        // IMPORTANT : Créer le client AVANT de créer l'utilisateur
        // pour éviter l'erreur "Booting the kernel before calling createClient()"
        $client = static::createClient();
        self::$kernelBooted = true;
        
        // Maintenant on peut créer l'utilisateur
        $user = $this->createTestUser($roles);
        
        // Utilise loginUser() pour simuler une authentification (firewall main, pas api car stateless)
        $client->loginUser($user);
        
        return $client;
    }

    /**
     * Helper pour tester une réponse JSON
     */
    protected function assertJsonResponse(KernelBrowser $client, int $expectedStatusCode = 200): array
    {
        $response = $client->getResponse();
        
        self::assertResponseStatusCodeSame($expectedStatusCode);
        self::assertResponseHeaderSame('content-type', 'application/json');
        
        $content = $response->getContent();
        self::assertJson($content);
        
        return json_decode($content, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // DAMA Bundle gère le rollback automatiquement
        // Pas besoin de nettoyer manuellement
        $this->entityManager = null;
    }
}
