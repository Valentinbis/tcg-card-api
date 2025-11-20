<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\DTO\PaginationDTO;
use App\Service\CardService;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardController extends AbstractController
{
    public function __construct(
        private CardService $cardService,
        private PaginationService $paginationService
    ) {
    }

    /**
     * Liste des cartes avec filtres et pagination.
     */
    #[Route('/api/cards', name: 'api_cards', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $page = $request->query->get('page');
        $limit = $request->query->get('limit');
        $owned = $request->query->get('owned');
        $type = $request->query->get('type');
        $rarity = $request->query->get('rarity');
        $set = $request->query->get('set');
        $search = $request->query->get('search');
        $number = $request->query->get('number');
        
        $pageInt = is_numeric($page) ? (int) $page : 1;
        $limitInt = is_numeric($limit) ? (int) $limit : 20;
        $sort = (string) ($request->query->get('sort') ?? 'number');
        $order = (string) ($request->query->get('order') ?? 'ASC');
        
        $pagination = new PaginationDTO(
            page: $pageInt,
            limit: $limitInt,
            sort: $sort,
            order: $order
        );
        
        $offset = ($pagination->page - 1) * $pagination->limit;

        $result = $this->cardService->getUserCardsWithFilters(
            $user,
            is_string($type) ? $type : null,
            is_string($rarity) ? $rarity : null,
            is_string($set) ? $set : null,
            is_string($search) ? $search : null,
            is_string($number) ? $number : null,
            is_string($owned) ? $owned : null,
            $offset,
            $limitInt,
            $sort,
            $order
        );

        $paginationData = $this->paginationService->paginate(
            $result['total'],
            $limitInt,
            $pageInt
        );

        return $this->json([
            'data' => $result['data'],
            'pagination' => $paginationData,
        ], Response::HTTP_OK, [], ['groups' => ['card:read']]);
    }

    /**
     * Récupère les détails d'une carte par son ID.
     */
    #[Route('/api/cards/{cardId}', name: 'api_card_detail', methods: ['GET'])]
    public function show(string $cardId): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $card = $this->cardService->getCardById($cardId);

        if (!$card) {
            return $this->json(['error' => 'Card not found'], 404);
        }

        return $this->json($card, Response::HTTP_OK, [], ['groups' => ['card:read']]);
    }

    /**
     * Liste des sets disponibles.
     */
    #[Route('/api/sets', name: 'api_sets', methods: ['GET'])]
    public function sets(): JsonResponse
    {
        $sets = $this->cardService->getAllSets();

        return $this->json($sets, Response::HTTP_OK, [], ['groups' => ['set:read']]);
    }
}
