<?php

namespace App\Controller\API;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InfosController
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    #[Route('/api', name: 'app_infos', methods: ['GET'])]
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
