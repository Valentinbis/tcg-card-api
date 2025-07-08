<?php

namespace App\Controller;

use App\DTO\CardViewDTO;
use App\DTO\PaginationDTO;
use App\Entity\Card;
use App\Entity\UserCard;
use App\Enum\LanguageEnum;
use App\Service\CardService;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
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
    ) {}

    #[Route('/api/cards', name: 'api_cards', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $pagination = new PaginationDTO(
            page: (int)($request->query->get('page', 1)),
            limit: (int)($request->query->get('limit', 20)),
            sort: $request->query->get('sort', 'number'),
            order: $request->query->get('order', 'ASC')
        );
        $owned = $request->query->get('owned');
        $lang = $request->query->get('lang');
        $type = $request->query->get('type');
        $number = $request->query->get('number');
        $offset = ($pagination->page - 1) * $pagination->limit;

        $result = $this->cardService->getUserCardsWithFilters(
            $user,
            $type,
            $number,
            $owned,
            $lang,
            $offset,
            $pagination->limit,
            $pagination->sort,
            $pagination->order
        );

        $paginationData = $this->paginationService->paginate(
            $result['total'],
            $pagination->limit,
            $pagination->page
        );

        return $this->json([
            'data' => $result['data'],
            'pagination' => $paginationData,
        ], Response::HTTP_OK, [], ['groups' => ['card:read']]);
    }

    #[Route('/api/cards/{id}/languages', name: 'api_card_languages', methods: ['POST'])]
    public function updateLanguages(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $languages = $data['languages'] ?? [];

        $this->cardService->updateUserCardLanguages($user, $id, $languages);

        return $this->json(['success' => true]);
    }
}
