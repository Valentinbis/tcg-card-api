<?php

namespace App\Controller;

use App\DTO\CardViewDTO;
use App\Entity\Card;
use App\Entity\UserCard;
use App\Enum\LanguageEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardController extends AbstractController
{
    #[Route('/api/cards', name: 'api_cards', methods: ['GET'])]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        // 1. Toutes les cartes
        $cards = $em->getRepository(Card::class)->findBy([], ['number' => 'ASC']);

        // 2. Tous les UserCard pour cet utilisateur
        /** @var User $user */
        $userCards = $em->getRepository(UserCard::class)->findBy([
            'user_id' => $user->getId(),
        ]);

        // 3. Indexer les UserCard par card_id pour accès rapide
        $ownedLanguagesByCardId = [];
        foreach ($userCards as $uc) {
            $ownedLanguagesByCardId[$uc->getCardId()] = array_map(
                fn(LanguageEnum $lang) => $lang->value,
                $uc->getLanguages()
            );
        }

        // 4. Construire la réponse
        $cardViews = array_map(
            fn(Card $card) => new CardViewDTO(
                $card->getId(),
                $card->getName() ?? '',
                $card->getNameFr() ?? '',
                (int)($card->getNumber() ?? 0),
                $card->getRarity() ?? '',
                $card->getNationalPokedexNumbers() ?? [],
                $card->getImages() ?? [],
                $ownedLanguagesByCardId[$card->getId()] ?? []
            ),
            $cards
        );

        return $this->json($cardViews, Response::HTTP_OK, [], ['groups' => ['card:read']]);
    }
}
