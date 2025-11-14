<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\WishlistService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/user/wishlist', name: 'wishlist_')]
#[OA\Tag(name: 'Wishlist')]
class WishlistController extends AbstractController
{
    public function __construct(
        private readonly WishlistService $wishlistService
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/user/wishlist',
        summary: 'Get user wishlist',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'minPriority',
                in: 'query',
                description: 'Minimum priority filter',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'maxPrice',
                in: 'query',
                description: 'Maximum price filter',
                required: false,
                schema: new OA\Schema(type: 'number', format: 'float')
            ),
            new OA\Parameter(
                name: 'orderBy',
                in: 'query',
                description: 'Order by field (priority, createdAt, maxPrice)',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'priority')
            ),
            new OA\Parameter(
                name: 'direction',
                in: 'query',
                description: 'Sort direction (ASC, DESC)',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'DESC')
            )
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Wishlist items retrieved successfully',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'cardId', type: 'string'),
                    new OA\Property(property: 'priority', type: 'integer'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                    new OA\Property(property: 'maxPrice', type: 'number', format: 'float', nullable: true),
                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
                ]
            )
        )
    )]
    public function list(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $filters = [
            'minPriority' => $request->query->get('minPriority'),
            'maxPrice' => $request->query->get('maxPrice'),
            'orderBy' => $request->query->get('orderBy', 'priority'),
            'direction' => $request->query->get('direction', 'DESC'),
        ];

        $wishlistItems = $this->wishlistService->getUserWishlist($user, $filters);

        $data = array_map(fn($item) => [
            'id' => $item->getId(),
            'cardId' => $item->getCardId(),
            'priority' => $item->getPriority(),
            'notes' => $item->getNotes(),
            'maxPrice' => $item->getMaxPrice(),
            'createdAt' => $item->getCreatedAt()->format('c'),
            'updatedAt' => $item->getUpdatedAt()->format('c'),
        ], $wishlistItems);

        return $this->json($data);
    }

    #[Route('', name: 'add', methods: ['POST'])]
    #[OA\Post(
        path: '/api/user/wishlist',
        summary: 'Add card to wishlist',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['cardId'],
                properties: [
                    new OA\Property(property: 'cardId', type: 'string', example: 'base1-1'),
                    new OA\Property(property: 'priority', type: 'integer', example: 3, default: 0),
                    new OA\Property(property: 'notes', type: 'string', example: 'Need for collection', nullable: true),
                    new OA\Property(property: 'maxPrice', type: 'number', format: 'float', example: 50.00, nullable: true)
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Card added to wishlist successfully'
    )]
    #[OA\Response(
        response: 400,
        description: 'Card already in wishlist or invalid data'
    )]
    public function add(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (!isset($data['cardId'])) {
            return $this->json(['error' => 'cardId is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $wishlistItem = $this->wishlistService->addToWishlist(
                $user,
                $data['cardId'],
                $data['priority'] ?? 0,
                $data['notes'] ?? null,
                $data['maxPrice'] ?? null
            );

            return $this->json([
                'id' => $wishlistItem->getId(),
                'cardId' => $wishlistItem->getCardId(),
                'priority' => $wishlistItem->getPriority(),
                'notes' => $wishlistItem->getNotes(),
                'maxPrice' => $wishlistItem->getMaxPrice(),
                'createdAt' => $wishlistItem->getCreatedAt()->format('c'),
                'updatedAt' => $wishlistItem->getUpdatedAt()->format('c'),
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{cardId}', name: 'update', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/user/wishlist/{cardId}',
        summary: 'Update wishlist item',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'cardId',
                in: 'path',
                description: 'Card ID',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'priority', type: 'integer', example: 5),
                    new OA\Property(property: 'notes', type: 'string', example: 'Updated notes'),
                    new OA\Property(property: 'maxPrice', type: 'number', format: 'float', example: 75.00)
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Wishlist item updated successfully'
    )]
    #[OA\Response(
        response: 404,
        description: 'Card not in wishlist'
    )]
    public function update(string $cardId, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        try {
            $wishlistItem = $this->wishlistService->updateWishlistItem(
                $user,
                $cardId,
                $data['priority'] ?? null,
                $data['notes'] ?? null,
                $data['maxPrice'] ?? null
            );

            return $this->json([
                'id' => $wishlistItem->getId(),
                'cardId' => $wishlistItem->getCardId(),
                'priority' => $wishlistItem->getPriority(),
                'notes' => $wishlistItem->getNotes(),
                'maxPrice' => $wishlistItem->getMaxPrice(),
                'createdAt' => $wishlistItem->getCreatedAt()->format('c'),
                'updatedAt' => $wishlistItem->getUpdatedAt()->format('c'),
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/{cardId}', name: 'remove', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/user/wishlist/{cardId}',
        summary: 'Remove card from wishlist',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'cardId',
                in: 'path',
                description: 'Card ID',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ]
    )]
    #[OA\Response(
        response: 204,
        description: 'Card removed from wishlist successfully'
    )]
    #[OA\Response(
        response: 404,
        description: 'Card not in wishlist'
    )]
    public function remove(string $cardId): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $this->wishlistService->removeFromWishlist($user, $cardId);
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/stats/summary', name: 'stats', methods: ['GET'])]
    #[OA\Get(
        path: '/api/user/wishlist/stats/summary',
        summary: 'Get wishlist statistics',
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: 'Wishlist statistics',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'total', type: 'integer', example: 42),
                new OA\Property(
                    property: 'byPriority',
                    type: 'object',
                    example: ['5' => 10, '3' => 15, '0' => 17]
                )
            ]
        )
    )]
    public function stats(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'total' => $this->wishlistService->getWishlistCount($user),
            'byPriority' => $this->wishlistService->getWishlistStatsByPriority($user),
        ]);
    }
}
