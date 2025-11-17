<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CardViewDTO;
use App\Entity\Card;
use App\Entity\Collection;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CardService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return array{data: array<CardViewDTO>, total: int}
     */
    public function getUserCardsWithFilters(
        User $user,
        ?string $type,
        ?string $number,
        ?string $owned,
        int $offset,
        int $limit,
        string $sort,
        string $order
    ): array {
        // 1. Récupère toutes les cartes triées (sans pagination)
        $qb = $this->em->getRepository(Card::class)->createQueryBuilder('c')
            ->orderBy('c.'.$sort, $order);

        /** @var array<Card> $allCards */
        $allCards = $qb->getQuery()->getResult();

        // 2. Toutes les Collection pour cet utilisateur
        $collections = $this->em->getRepository(Collection::class)->findBy([
            'user' => $user,
        ]);

        // 3. Indexer les Collection par card_id pour accès rapide
        /** @var array<string, bool> $ownedCards */
        $ownedCards = [];
        foreach ($collections as $collection) {
            $ownedCards[$collection->getCardId()] = true;
        }

        // 4. Appliquer les filtres côté PHP

        // Filtre par type
        if ($type) {
            $allCards = array_filter($allCards, function (Card $card) use ($type): bool {
                $types = $card->getTypes() ?? [];

                return in_array($type, $types, true);
            });
            $allCards = array_values($allCards);
        }

        // Filtre par numéro
        if (null !== $number && '' !== $number) {
            $allCards = array_filter($allCards, function (Card $card) use ($number): bool {
                return (string) $card->getNumber() === (string) $number;
            });
            $allCards = array_values($allCards);
        }

        // Filtre owned
        if (null !== $owned) {
            $allCards = array_filter($allCards, function (Card $card) use ($ownedCards, $owned): bool {
                $isOwned = isset($ownedCards[$card->getId()]);

                return 'true' === $owned ? $isOwned : !$isOwned;
            });
            $allCards = array_values($allCards);
        }

        // 5. Calcule le total AVANT pagination
        $total = count($allCards);

        // 6. Découpe pour la page courante
        $cards = array_slice($allCards, $offset, $limit);

        // 7. Construire la réponse paginée
        /** @var array<CardViewDTO> $cardViews */
        $cardViews = array_map(
            fn (Card $card): CardViewDTO => (function () use ($card, $ownedCards) {
                $dto = new CardViewDTO(
                    $card->getId(),
                    $card->getName() ?? '',
                    $card->getNameFr() ?? '',
                    $card->getNumber() ?? '',
                    $card->getRarity() ?? '',
                    $card->getNationalPokedexNumbers() ?? [],
                    $card->getImages() ?? [],
                    $ownedCards[$card->getId()] ?? false
                );
                // Ajout des variantes et prix
                $dto->variants = [];
                foreach ($card->getVariants() as $variant) {
                    $dto->variants[$variant->getType()->value] = [
                        'price' => $variant->getPrice(),
                        'cardmarket_average' => $variant->getCardmarketAverage(),
                        'cardmarket_trend' => $variant->getCardmarketTrend(),
                        'cardmarket_min' => $variant->getCardmarketMin(),
                        'cardmarket_max' => $variant->getCardmarketMax(),
                        'cardmarket_suggested' => $variant->getCardmarketSuggested(),
                        'cardmarket_germanProLow' => $variant->getCardmarketGermanProLow(),
                        'cardmarket_low_ex_plus' => $variant->getCardmarketLowExPlus(),
                        'cardmarket_avg1' => $variant->getCardmarketAvg1(),
                        'cardmarket_avg7' => $variant->getCardmarketAvg7(),
                        'cardmarket_avg30' => $variant->getCardmarketAvg30(),
                        'cardmarket_reverse' => $variant->getCardmarketReverse(),
                        'cardmarket_reverse_low' => $variant->getCardmarketReverseLow(),
                        'cardmarket_reverse_trend' => $variant->getCardmarketReverseTrend(),
                        'cardmarket_reverse_avg1' => $variant->getCardmarketReverseAvg1(),
                        'cardmarket_reverse_avg7' => $variant->getCardmarketReverseAvg7(),
                        'cardmarket_reverse_avg30' => $variant->getCardmarketReverseAvg30(),
                        'cardmarket_holo' => $variant->getCardmarketHolo(),
                        'tcgplayer_normal_low' => $variant->getTcgplayerNormalLow(),
                        'tcgplayer_normal_mid' => $variant->getTcgplayerNormalMid(),
                        'tcgplayer_normal_high' => $variant->getTcgplayerNormalHigh(),
                        'tcgplayer_normal_market' => $variant->getTcgplayerNormalMarket(),
                        'tcgplayer_normal_direct' => $variant->getTcgplayerNormalDirect(),
                        'tcgplayer_reverse_low' => $variant->getTcgplayerReverseLow(),
                        'tcgplayer_reverse_mid' => $variant->getTcgplayerReverseMid(),
                        'tcgplayer_reverse_high' => $variant->getTcgplayerReverseHigh(),
                        'tcgplayer_reverse_market' => $variant->getTcgplayerReverseMarket(),
                        'tcgplayer_reverse_direct' => $variant->getTcgplayerReverseDirect(),
                        'tcgplayer_holo_low' => $variant->getTcgplayerHoloLow(),
                        'tcgplayer_holo_mid' => $variant->getTcgplayerHoloMid(),
                        'tcgplayer_holo_high' => $variant->getTcgplayerHoloHigh(),
                        'tcgplayer_holo_market' => $variant->getTcgplayerHoloMarket(),
                        'tcgplayer_holo_direct' => $variant->getTcgplayerHoloDirect(),
                    ];
                }
                return $dto;
            })(),
            $cards
        );

        return [
            'data' => $cardViews,
            'total' => $total,
        ];
    }

    /**
     * @param array<string> $languages
     */
    public function updateUserCollectionLanguages(User $user, string $cardId, array $languages): void
    {
        $collection = $this->em->getRepository(Collection::class)->findOneBy([
            'user' => $user,
            'cardId' => $cardId,
        ]);

        if (!$collection) {
            $collection = new Collection();
            $collection->setUser($user);
            $collection->setCardId($cardId);
            $collection->setQuantity(1);
        }

        $this->em->persist($collection);
        $this->em->flush();
    }
}
