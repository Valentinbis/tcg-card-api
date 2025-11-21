<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Attribute\LogAction;
use App\Attribute\LogPerformance;
use App\DTO\AddToWishlistDTO;
use App\DTO\UpdateWishlistDTO;
use App\Entity\User;
use App\Enum\CardVariantEnum;
use App\Service\WishlistService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class WishlistController extends AbstractController
{
    public function __construct(
        private readonly WishlistService $wishlistService,
    ) {
    }

    /**
     * Liste la wishlist de l'utilisateur avec filtres optionnels.
     */
    #[Route('/api/wishlist', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('view_wishlist', 'User wishlist accessed')]
    #[LogPerformance(threshold: 0.5)]
    public function list(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $filters = [];
        if ($request->query->has('minPriority')) {
            $filters['minPriority'] = (int) $request->query->get('minPriority');
        }
        if ($request->query->has('minPrice')) {
            $filters['minPrice'] = (float) $request->query->get('minPrice');
        }
        if ($request->query->has('maxPrice')) {
            $filters['maxPrice'] = (float) $request->query->get('maxPrice');
        }
        if ($request->query->has('orderBy')) {
            $filters['orderBy'] = $request->query->get('orderBy');
        }
        if ($request->query->has('direction')) {
            $filters['direction'] = $request->query->get('direction');
        }

        $wishlistItems = $this->wishlistService->getUserWishlist($user, $filters);

        return $this->json($wishlistItems, Response::HTTP_OK, [], ['groups' => 'wishlist:read']);
    }

    /**
     * Récupère les statistiques de wishlist.
     */
    #[Route('/api/wishlist/stats', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('view_wishlist_stats', 'Wishlist statistics accessed')]
    #[LogPerformance(threshold: 0.5)]
    public function stats(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'total' => $this->wishlistService->getWishlistCount($user),
            'byPriority' => $this->wishlistService->getWishlistStatsByPriority($user),
        ], Response::HTTP_OK);
    }

    /**
     * Ajoute une carte à la wishlist.
     */
    #[Route('/api/wishlist', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('add_to_wishlist', 'Card added to wishlist')]
    #[LogPerformance(threshold: 0.3)]
    public function add(
        #[MapRequestPayload] AddToWishlistDTO $dto
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $wishlistItem = $this->wishlistService->addToWishlist(
                user: $user,
                cardId: $dto->cardId,
                priority: $dto->priority ?? 0,
                notes: $dto->notes,
                maxPrice: $dto->maxPrice,
                variant: $dto->variant ?? CardVariantEnum::NORMAL
            );

            return $this->json($wishlistItem, Response::HTTP_CREATED, [], ['groups' => 'wishlist:read']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Met à jour une carte de la wishlist.
     */
    #[Route('/api/wishlist/{cardId}', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('update_wishlist', 'Wishlist item updated')]
    #[LogPerformance(threshold: 0.3)]
    public function update(
        string $cardId,
        #[MapRequestPayload] UpdateWishlistDTO $dto
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $wishlistItem = $this->wishlistService->updateWishlistItem(
                user: $user,
                cardId: $cardId,
                priority: $dto->priority,
                notes: $dto->notes,
                maxPrice: $dto->maxPrice,
                variant: $dto->variant
            );

            return $this->json($wishlistItem, Response::HTTP_OK, [], ['groups' => 'wishlist:read']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Supprime une carte de la wishlist.
     */
    #[Route('/api/wishlist/{cardId}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    #[LogAction('remove_from_wishlist', 'Card removed from wishlist')]
    #[LogPerformance(threshold: 0.3)]
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
}
