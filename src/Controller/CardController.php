<?php

declare(strict_types=1);

namespace App\Controller;

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
        $lang = $request->query->get('lang');
        $type = $request->query->get('type');
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
            is_string($number) ? $number : null,
            is_string($owned) ? $owned : null,
            is_string($lang) ? $lang : null,
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
     * Mise à jour des langues pour une carte utilisateur.
     */
    #[Route('/api/cards/{id}/languages', name: 'api_card_languages', methods: ['POST'])]
    public function updateLanguages(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid request data'], 400);
        }
        
        $languages = $data['languages'] ?? [];
        if (!is_array($languages)) {
            return $this->json(['error' => 'Invalid languages format'], 400);
        }
        
        /** @var array<string> $stringLanguages */
        $stringLanguages = array_filter($languages, 'is_string');

        $this->cardService->updateUserCardLanguages($user, $id, $stringLanguages);

        return $this->json(['success' => true]);
    }
}
