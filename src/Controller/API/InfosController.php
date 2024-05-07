<?php

namespace App\Controller\API;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InfosController extends AbstractController
{
    #[Route('/api', name: 'app_infos')]
    public function index(Connection $connection): Response
    {
        try {
            $connection->executeQuery('SELECT 1');
            $isConnected = true;
        } catch (\Exception $e) {
            $isConnected = false;
        }

        return $this->json([
            'name' => $_ENV['APP_NAME'],
            'env' => $_ENV['APP_ENV'],
            'database' => [
                'connected' => $isConnected
            ]
        ]);
    }
}
