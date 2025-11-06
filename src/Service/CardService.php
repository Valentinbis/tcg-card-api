<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CardViewDTO;
use App\Entity\Card;
use App\Entity\User;
use App\Entity\UserCard;
use App\Enum\LanguageEnum;
use Doctrine\ORM\EntityManagerInterface;

class CardService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getUserCardsWithFilters(
        User $user,
        ?string $type,
        ?string $number,
        ?string $owned,
        ?string $lang,
        int $offset,
        int $limit,
        string $sort,
        string $order
    ): array {
        // 1. Récupère toutes les cartes triées (sans pagination)
        $qb = $this->em->getRepository(Card::class)->createQueryBuilder('c')
            ->orderBy('c.'.$sort, $order);

        $allCards = $qb->getQuery()->getResult();

        // 2. Tous les UserCard pour cet utilisateur
        $userCards = $this->em->getRepository(UserCard::class)->findBy([
            'user_id' => $user->getId(),
        ]);

        // 3. Indexer les UserCard par card_id pour accès rapide
        $ownedLanguagesByCardId = [];
        foreach ($userCards as $uc) {
            $ownedLanguagesByCardId[$uc->getCardId()] = array_map(
                fn (LanguageEnum $langEnum) => $langEnum->value,
                $uc->getLanguages()
            );
        }

        // 4. Appliquer les filtres côté PHP

        // Filtre par type
        if ($type) {
            $allCards = array_filter($allCards, function ($card) use ($type) {
                $types = $card->getTypes() ?? [];

                return in_array($type, $types, true);
            });
            $allCards = array_values($allCards);
        }

        // Filtre par numéro
        if (null !== $number && '' !== $number) {
            $allCards = array_filter($allCards, function ($card) use ($number) {
                return (string) $card->getNumber() === (string) $number;
            });
            $allCards = array_values($allCards);
        }

        // Filtre owned/lang
        if (null !== $owned) {
            $allCards = array_filter($allCards, function ($card) use ($ownedLanguagesByCardId, $lang, $owned) {
                $ownedLangs = $ownedLanguagesByCardId[$card->getId()] ?? [];
                if ($lang) {
                    $hasLang = in_array($lang, $ownedLangs, true);

                    return 'true' === $owned ? $hasLang : !$hasLang;
                } else {
                    $hasAny = !empty($ownedLangs);

                    return 'true' === $owned ? $hasAny : !$hasAny;
                }
            });
            $allCards = array_values($allCards);
        }

        // 5. Calcule le total AVANT pagination
        $total = count($allCards);

        // 6. Découpe pour la page courante
        $cards = array_slice($allCards, $offset, $limit);

        // 7. Construire la réponse paginée
        $cardViews = array_map(
            fn (Card $card) => new CardViewDTO(
                $card->getId(),
                $card->getName() ?? '',
                $card->getNameFr() ?? '',
                (int) ($card->getNumber() ?? 0),
                $card->getRarity() ?? '',
                $card->getNationalPokedexNumbers() ?? [],
                $card->getImages() ?? [],
                $ownedLanguagesByCardId[$card->getId()] ?? []
            ),
            $cards
        );

        return [
            'data' => $cardViews,
            'total' => $total,
        ];
    }

    public function updateUserCardLanguages(User $user, int $cardId, array $languages): void
    {
        $enumLanguages = array_map(
            fn (string $lang) => LanguageEnum::from($lang),
            $languages
        );

        $userCard = $this->em->getRepository(UserCard::class)->findOneBy([
            'user_id' => $user->getId(),
            'card_id' => $cardId,
        ]);

        if (!$userCard) {
            $userCard = new UserCard();
            $userCard->setUserId($user->getId());
            $userCard->setCardId($cardId);
        }

        $userCard->setLanguages($enumLanguages);
        $this->em->persist($userCard);
        $this->em->flush();
    }
}
