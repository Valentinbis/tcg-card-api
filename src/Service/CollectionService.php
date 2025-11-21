<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Collection;
use App\Entity\User;
use App\Enum\CardConditionEnum;
use App\Enum\CardVariantEnum;
use App\Repository\CollectionRepository;
use Doctrine\ORM\EntityManagerInterface;

class CollectionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CollectionRepository $collectionRepository,
    ) {
    }

    /**
     * Add a card to user's collection or update quantity if exists.
     */
    public function addToCollection(
        User $user,
        string $cardId,
        int $quantity = 1,
        ?CardConditionEnum $condition = null,
        ?float $purchasePrice = null,
        ?\DateTimeImmutable $purchaseDate = null,
        ?string $notes = null,
        ?CardVariantEnum $variant = null
    ): Collection {
        $variant = $variant ?? CardVariantEnum::NORMAL;
        $existingCollection = $this->collectionRepository->findByUserAndCard($user, $cardId, $variant);

        if ($existingCollection) {
            $existingCollection->setQuantity($existingCollection->getQuantity() + $quantity);
            if (null !== $purchasePrice) {
                $existingCollection->setPurchasePrice($purchasePrice);
            }
            if (null !== $purchaseDate) {
                $existingCollection->setPurchaseDate($purchaseDate);
            }
            if (null !== $notes) {
                $existingCollection->setNotes($notes);
            }
            $this->entityManager->flush();

            return $existingCollection;
        }

        $collection = new Collection();
        $collection->setUser($user)
            ->setCardId($cardId)
            ->setQuantity($quantity)
            ->setCondition($condition)
            ->setPurchasePrice($purchasePrice)
            ->setPurchaseDate($purchaseDate)
            ->setNotes($notes)
            ->setVariant($variant);

        $this->entityManager->persist($collection);
        $this->entityManager->flush();

        return $collection;
    }

    /**
     * Remove a card from user's collection completely.
     */
    public function removeFromCollection(User $user, string $cardId): bool
    {
        $collection = $this->collectionRepository->findByUserAndCard($user, $cardId);

        if (!$collection) {
            return false;
        }

        $this->entityManager->remove($collection);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Update collection item properties (partial update).
     */
    public function updateCollectionItem(
        User $user,
        string $cardId,
        ?int $quantity = null,
        ?CardConditionEnum $condition = null,
        ?float $purchasePrice = null,
        ?\DateTimeImmutable $purchaseDate = null,
        ?string $notes = null,
        ?CardVariantEnum $variant = null
    ): ?Collection {
        $collection = $this->collectionRepository->findByUserAndCard($user, $cardId, $variant);

        if (!$collection) {
            return null;
        }

        if (null !== $quantity) {
            $collection->setQuantity($quantity);
        }
        if (null !== $condition) {
            $collection->setCondition($condition);
        }
        if (null !== $purchasePrice) {
            $collection->setPurchasePrice($purchasePrice);
        }
        if (null !== $purchaseDate) {
            $collection->setPurchaseDate($purchaseDate);
        }
        if (null !== $notes) {
            $collection->setNotes($notes);
        }
        if (null !== $variant) {
            $collection->setVariant($variant);
        }

        $this->entityManager->flush();

        return $collection;
    }

    /**
     * Get user's complete collection with optional filters.
     *
     * @param array<string, mixed> $filters
     */
    public function getUserCollection(User $user, array $filters = []): array
    {
        $collections = $this->collectionRepository->findByUser($user, $filters);
        $result = [];
        foreach ($collections as $collection) {
            $card = $collection->getCardId();
            $variant = $collection->getVariant();
            // Récupérer l'entité Card et la variante
            $cardEntity = null;
            $variantEntity = null;
            if (method_exists($collection, 'getUser')) {
                // On suppose que Card est accessible via CardRepository
                $cardEntity = $this->entityManager->getRepository('App\\Entity\\Card')->find($card);
                if ($cardEntity && method_exists($cardEntity, 'getVariants')) {
                    foreach ($cardEntity->getVariants() as $v) {
                        if ($v->getType() === $variant) {
                            $variantEntity = $v;

                            break;
                        }
                    }
                }
            }
            $prices = [];
            if ($variantEntity) {
                $prices = [
                    'price' => $variantEntity->getPrice(),
                    'cardmarket_average' => $variantEntity->getCardmarketAverage(),
                    'cardmarket_trend' => $variantEntity->getCardmarketTrend(),
                    'cardmarket_min' => $variantEntity->getCardmarketMin(),
                    'cardmarket_max' => $variantEntity->getCardmarketMax(),
                    'cardmarket_suggested' => $variantEntity->getCardmarketSuggested(),
                    'cardmarket_germanProLow' => $variantEntity->getCardmarketGermanProLow(),
                    'cardmarket_low_ex_plus' => $variantEntity->getCardmarketLowExPlus(),
                    'cardmarket_avg1' => $variantEntity->getCardmarketAvg1(),
                    'cardmarket_avg7' => $variantEntity->getCardmarketAvg7(),
                    'cardmarket_avg30' => $variantEntity->getCardmarketAvg30(),
                    'cardmarket_reverse' => $variantEntity->getCardmarketReverse(),
                    'cardmarket_reverse_low' => $variantEntity->getCardmarketReverseLow(),
                    'cardmarket_reverse_trend' => $variantEntity->getCardmarketReverseTrend(),
                    'cardmarket_reverse_avg1' => $variantEntity->getCardmarketReverseAvg1(),
                    'cardmarket_reverse_avg7' => $variantEntity->getCardmarketReverseAvg7(),
                    'cardmarket_reverse_avg30' => $variantEntity->getCardmarketReverseAvg30(),
                    'cardmarket_holo' => $variantEntity->getCardmarketHolo(),
                    'tcgplayer_normal_low' => $variantEntity->getTcgplayerNormalLow(),
                    'tcgplayer_normal_mid' => $variantEntity->getTcgplayerNormalMid(),
                    'tcgplayer_normal_high' => $variantEntity->getTcgplayerNormalHigh(),
                    'tcgplayer_normal_market' => $variantEntity->getTcgplayerNormalMarket(),
                    'tcgplayer_normal_direct' => $variantEntity->getTcgplayerNormalDirect(),
                    'tcgplayer_reverse_low' => $variantEntity->getTcgplayerReverseLow(),
                    'tcgplayer_reverse_mid' => $variantEntity->getTcgplayerReverseMid(),
                    'tcgplayer_reverse_high' => $variantEntity->getTcgplayerReverseHigh(),
                    'tcgplayer_reverse_market' => $variantEntity->getTcgplayerReverseMarket(),
                    'tcgplayer_reverse_direct' => $variantEntity->getTcgplayerReverseDirect(),
                    'tcgplayer_holo_low' => $variantEntity->getTcgplayerHoloLow(),
                    'tcgplayer_holo_mid' => $variantEntity->getTcgplayerHoloMid(),
                    'tcgplayer_holo_high' => $variantEntity->getTcgplayerHoloHigh(),
                    'tcgplayer_holo_market' => $variantEntity->getTcgplayerHoloMarket(),
                    'tcgplayer_holo_direct' => $variantEntity->getTcgplayerHoloDirect(),
                ];
            }
            $result[] = [
                'id' => $collection->getId(),
                'cardId' => $collection->getCardId(),
                'quantity' => $collection->getQuantity(),
                'condition' => $collection->getCondition(),
                'purchasePrice' => $collection->getPurchasePrice(),
                'purchaseDate' => $collection->getPurchaseDate(),
                'notes' => $collection->getNotes(),
                'variant' => $collection->getVariant(),
                'createdAt' => $collection->getCreatedAt(),
                'updatedAt' => $collection->getUpdatedAt(),
                'prices' => $prices,
            ];
        }

        return $result;
    }

    /**
     * Get total number of cards in collection (sum of quantities).
     */
    public function getCollectionCount(User $user): int
    {
        return $this->collectionRepository->countByUser($user);
    }

    /**
     * Get number of unique cards in collection.
     */
    public function getUniqueCardCount(User $user): int
    {
        return $this->collectionRepository->countUniqueCardsByUser($user);
    }

    /**
     * Get total monetary value of collection.
     */
    public function getTotalValue(User $user): float
    {
        return $this->collectionRepository->getTotalValue($user);
    }

    /**
     * Get comprehensive collection statistics.
     */
    public function getCollectionStats(User $user): array
    {
        return [
            'totalCards' => $this->getCollectionCount($user),
            'uniqueCards' => $this->getUniqueCardCount($user),
            'totalValue' => $this->getTotalValue($user),
            'countByCondition' => $this->collectionRepository->getCountByCondition($user),
            'valueByCondition' => $this->collectionRepository->getValueByCondition($user),
        ];
    }

    /**
     * Check if card is in user's collection.
     */
    public function isInCollection(User $user, string $cardId): bool
    {
        return null !== $this->collectionRepository->findByUserAndCard($user, $cardId);
    }
}
