<?php

namespace App\Tests;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseWebTestCase extends WebTestCase
{
    protected $client;
    protected $entityManager;
    
    protected function setUp(): void
    {
        parent::setUp();

        // Crée un nouveau client pour chaque test, assurant que le kernel est bien démarré
        $this->client = static::createClient();

        // Configuration du mock de l'EntityManager
        $this->entityManager = $this->createMock(EntityManager::class);
        self::getContainer()->set('doctrine.orm.entity_manager', $this->entityManager);
    }

    protected function getHttpClient()
    {
        return $this->client;
    }
}
