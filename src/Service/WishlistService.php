<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Wishlist;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;

class WishlistService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WishlistRepository $wishlistRepository
    ) {
    }

    /**
     * Ajoute une carte à la wishlist
     */
    public function addToWishlist(
        User $user,
        string $cardId,
        int $priority = 0,
        ?string $notes = null,
        ?float $maxPrice = null
    ): Wishlist {
        // Vérifie si la carte existe déjà dans la wishlist
        $existingItem = $this->wishlistRepository->findByUserAndCard($user, $cardId);
        
        if ($existingItem !== null) {
            throw new \InvalidArgumentException('This card is already in your wishlist');
        }

        $wishlistItem = new Wishlist();
        $wishlistItem->setUser($user)
            ->setCardId($cardId)
            ->setPriority($priority)
            ->setNotes($notes)
            ->setMaxPrice($maxPrice !== null ? (string) $maxPrice : null);

        $this->entityManager->persist($wishlistItem);
        $this->entityManager->flush();

        return $wishlistItem;
    }

    /**
     * Retire une carte de la wishlist
     */
    public function removeFromWishlist(User $user, string $cardId): void
    {
        $wishlistItem = $this->wishlistRepository->findByUserAndCard($user, $cardId);
        
        if ($wishlistItem === null) {
            throw new \InvalidArgumentException('This card is not in your wishlist');
        }

        $this->entityManager->remove($wishlistItem);
        $this->entityManager->flush();
    }

    /**
     * Met à jour un élément de la wishlist
     */
    public function updateWishlistItem(
        User $user,
        string $cardId,
        ?int $priority = null,
        ?string $notes = null,
        ?float $maxPrice = null
    ): Wishlist {
        $wishlistItem = $this->wishlistRepository->findByUserAndCard($user, $cardId);
        
        if ($wishlistItem === null) {
            throw new \InvalidArgumentException('This card is not in your wishlist');
        }

        if ($priority !== null) {
            $wishlistItem->setPriority($priority);
        }
        
        if ($notes !== null) {
            $wishlistItem->setNotes($notes);
        }
        
        if ($maxPrice !== null) {
            $wishlistItem->setMaxPrice((string) $maxPrice);
        }

        $this->entityManager->flush();

        return $wishlistItem;
    }

    /**
     * Récupère la wishlist complète d'un utilisateur
     *
     * @return Wishlist[]
     */
    public function getUserWishlist(User $user, array $filters = []): array
    {
        return $this->wishlistRepository->findByUser($user, $filters);
    }

    /**
     * Compte le nombre d'éléments dans la wishlist
     */
    public function getWishlistCount(User $user): int
    {
        return $this->wishlistRepository->countByUser($user);
    }

    /**
     * Obtient les statistiques de la wishlist par priorité
     *
     * @return array<int, int>
     */
    public function getWishlistStatsByPriority(User $user): array
    {
        return $this->wishlistRepository->getCountByPriority($user);
    }

    /**
     * Vérifie si une carte est dans la wishlist
     */
    public function isInWishlist(User $user, string $cardId): bool
    {
        return $this->wishlistRepository->findByUserAndCard($user, $cardId) !== null;
    }
}
