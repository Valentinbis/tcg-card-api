<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Collection;
use App\Entity\User;
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
     * Add a card to user's collection or update quantity if exists
     */
    public function addToCollection(
        User $user,
        string $cardId,
        int $quantity = 1,
        ?string $condition = null,
        ?float $purchasePrice = null,
        ?\DateTimeImmutable $purchaseDate = null,
        ?string $notes = null,
        ?array $languages = null
    ): Collection {
        $existingCollection = $this->collectionRepository->findByUserAndCard($user, $cardId);

        if ($existingCollection) {
            $existingCollection->setQuantity($existingCollection->getQuantity() + $quantity);
            
            if ($purchasePrice !== null) {
                $existingCollection->setPurchasePrice($purchasePrice);
            }
            if ($purchaseDate !== null) {
                $existingCollection->setPurchaseDate($purchaseDate);
            }
            if ($notes !== null) {
                $existingCollection->setNotes($notes);
            }
            if ($languages !== null) {
                $existingCollection->setLanguages($languages);
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
            ->setLanguages($languages);

        $this->entityManager->persist($collection);
        $this->entityManager->flush();

        return $collection;
    }

    /**
     * Remove a card from user's collection completely
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
     * Update collection item properties (partial update)
     */
    public function updateCollectionItem(
        User $user,
        string $cardId,
        ?int $quantity = null,
        ?string $condition = null,
        ?float $purchasePrice = null,
        ?\DateTimeImmutable $purchaseDate = null,
        ?string $notes = null,
        ?array $languages = null
    ): ?Collection {
        $collection = $this->collectionRepository->findByUserAndCard($user, $cardId);

        if (!$collection) {
            return null;
        }

        if ($quantity !== null) {
            $collection->setQuantity($quantity);
        }
        if ($condition !== null) {
            $collection->setCondition($condition);
        }
        if ($purchasePrice !== null) {
            $collection->setPurchasePrice($purchasePrice);
        }
        if ($purchaseDate !== null) {
            $collection->setPurchaseDate($purchaseDate);
        }
        if ($notes !== null) {
            $collection->setNotes($notes);
        }
        if ($languages !== null) {
            $collection->setLanguages($languages);
        }

        $this->entityManager->flush();

        return $collection;
    }

    /**
     * Get user's complete collection with optional filters
     */
    public function getUserCollection(User $user, array $filters = []): array
    {
        return $this->collectionRepository->findByUser($user, $filters);
    }

    /**
     * Get total number of cards in collection (sum of quantities)
     */
    public function getCollectionCount(User $user): int
    {
        return $this->collectionRepository->countByUser($user);
    }

    /**
     * Get number of unique cards in collection
     */
    public function getUniqueCardCount(User $user): int
    {
        return $this->collectionRepository->countUniqueCardsByUser($user);
    }

    /**
     * Get total monetary value of collection
     */
    public function getTotalValue(User $user): float
    {
        return $this->collectionRepository->getTotalValue($user);
    }

    /**
     * Get comprehensive collection statistics
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
     * Check if card is in user's collection
     */
    public function isInCollection(User $user, string $cardId): bool
    {
        return $this->collectionRepository->findByUserAndCard($user, $cardId) !== null;
    }
}
