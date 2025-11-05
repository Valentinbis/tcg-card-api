<?php

namespace App\Controller\API;

use App\Attribute\LogAction;
use App\Attribute\LogPerformance;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InfosController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
    ) {}

    /**
     * Informations système et statut de l'API
     */
    #[Route('/api', name: 'app_infos', methods: ['GET'])]
    #[LogAction('health_check', 'API health check performed')]
    #[LogPerformance(threshold: 0.2)]
    public function index(): Response
    {
        try {
            $this->connection->executeQuery('SELECT 1');
            $isConnected = true;
        } catch (\Exception $e) {
            $isConnected = false;
        }

        return new Response(json_encode([
            'name' => $_ENV['APP_NAME'],
            'env' => $_ENV['APP_ENV'],
            'database' => [
                'connected' => $isConnected
            ]
        ]), 200, ['Content-Type' => 'application/json']);
    }
}
