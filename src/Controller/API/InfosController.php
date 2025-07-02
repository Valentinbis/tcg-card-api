<?php

namespace App\Controller\API;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InfosController extends AbstractController
{
    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger
    ) {}

    #[Route('/api', name: 'app_infos', methods: ['GET'])]
    public function index(): Response
    {
        try {
            $this->connection->executeQuery('SELECT 1');
            $isConnected = true;
            $this->logger->info('Database connection successful');
        } catch (\Exception $e) {
            $this->logger->error('Database connection failed', ['exception' => $e->getMessage()]);
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
