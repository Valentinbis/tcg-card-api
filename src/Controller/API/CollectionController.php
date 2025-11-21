<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Attribute\LogAction;
use App\Attribute\LogPerformance;
use App\DTO\AddToCollectionDTO;
use App\DTO\UpdateCollectionDTO;
use App\Entity\User;
use App\Service\CollectionService;
use App\Service\CollectionStatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CollectionController extends AbstractController
{
    public function __construct(
        private readonly CollectionService $collectionService,
        private readonly CollectionStatsService $collectionStatsService,
    ) {
    }

    /**
     * Liste la collection de l'utilisateur avec filtres optionnels.
     */
    #[Route('/api/collection', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('view_collection', 'User collection accessed')]
    #[LogPerformance(threshold: 0.5)]
    public function list(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $filters = [];
        if ($request->query->has('condition')) {
            $filters['condition'] = $request->query->get('condition');
        }
        if ($request->query->has('variant')) {
            $filters['variant'] = $request->query->get('variant');
        }
        if ($request->query->has('minQuantity')) {
            $filters['minQuantity'] = (int) $request->query->get('minQuantity');
        }
        if ($request->query->has('minPrice')) {
            $filters['minPrice'] = (float) $request->query->get('minPrice');
        }
        if ($request->query->has('maxPrice')) {
            $filters['maxPrice'] = (float) $request->query->get('maxPrice');
        }

        $collection = $this->collectionService->getUserCollection($user, $filters);

        return $this->json($collection, Response::HTTP_OK, [], ['groups' => 'collection:read']);
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

    /**
     * Ajoute une carte à la collection.
     */
    #[Route('/api/collection', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('add_to_collection', 'Card added to collection')]
    #[LogPerformance(threshold: 0.3)]
    public function add(
        #[MapRequestPayload]
        AddToCollectionDTO $dto
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $collection = $this->collectionService->addToCollection(
            user: $user,
            cardId: $dto->cardId,
            quantity: $dto->quantity ?? 1,
            condition: $dto->condition,
            purchasePrice: $dto->purchasePrice,
            purchaseDate: $dto->purchaseDate,
            notes: $dto->notes,
            variant: $dto->variant,
        );

        return $this->json($collection, Response::HTTP_CREATED, [], ['groups' => 'collection:read']);
    }

    /**
     * Met à jour une carte de la collection.
     */
    #[Route('/api/collection/{cardId}', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('update_collection', 'Collection item updated')]
    #[LogPerformance(threshold: 0.3)]
    public function update(
        string $cardId,
        #[MapRequestPayload]
        UpdateCollectionDTO $dto
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $collection = $this->collectionService->updateCollectionItem(
            user: $user,
            cardId: $cardId,
            quantity: $dto->quantity,
            condition: $dto->condition,
            purchasePrice: $dto->purchasePrice,
            purchaseDate: $dto->purchaseDate,
            notes: $dto->notes,
            variant: $dto->variant,
        );

        if (!$collection) {
            return $this->json(['error' => 'Collection item not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($collection, Response::HTTP_OK, [], ['groups' => 'collection:read']);
    }

    /**
     * Supprime une carte de la collection.
     */
    #[Route('/api/collection/{cardId}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('remove_from_collection', 'Card removed from collection')]
    #[LogPerformance(threshold: 0.3)]
    public function remove(string $cardId): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $removed = $this->collectionService->removeFromCollection($user, $cardId);

        if (!$removed) {
            return $this->json(['error' => 'Collection item not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
