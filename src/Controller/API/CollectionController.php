<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Attribute\LogAction;
use App\Attribute\LogPerformance;
use App\Entity\User;
use App\Service\CollectionStatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CollectionController extends AbstractController
{
    public function __construct(
        private readonly CollectionStatsService $collectionStatsService,
    ) {
    }

    /**
     * Récupère les statistiques de collection par set.
     */
    #[Route('/api/collection/stats', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('view_collection_stats', 'Collection statistics accessed')]
    #[LogPerformance(threshold: 0.5)]
    public function stats(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json(
            ['sets' => $this->collectionStatsService->getCollectionStats($user)], 
            Response::HTTP_OK
        );
    }
}
