<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\Wishlist;
use App\Enum\CardVariantEnum;
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
     * Ajoute une carte à la wishlist.
     */
    public function addToWishlist(
        User $user,
        string $cardId,
        int $priority = 0,
        ?string $notes = null,
        ?float $maxPrice = null,
        ?CardVariantEnum $variant = CardVariantEnum::NORMAL
    ): Wishlist {
        // Vérifie si la carte existe déjà dans la wishlist
        $existingItem = $this->wishlistRepository->findByUserAndCard($user, $cardId);

        if (null !== $existingItem) {
            throw new \InvalidArgumentException('This card is already in your wishlist');
        }

        $wishlistItem = new Wishlist();
        $wishlistItem->setUser($user)
            ->setCardId($cardId)
            ->setPriority($priority)
            ->setNotes($notes)
            ->setMaxPrice(null !== $maxPrice ? (string) $maxPrice : null)
            ->setVariant($variant);

        $this->entityManager->persist($wishlistItem);
        $this->entityManager->flush();

        return $wishlistItem;
    }

    /**
     * Retire une carte de la wishlist.
     */
    public function removeFromWishlist(User $user, string $cardId): void
    {
        $wishlistItem = $this->wishlistRepository->findByUserAndCard($user, $cardId);

        if (null === $wishlistItem) {
            throw new \InvalidArgumentException('This card is not in your wishlist');
        }

        $this->entityManager->remove($wishlistItem);
        $this->entityManager->flush();
    }

    /**
     * Met à jour un élément de la wishlist.
     */
    public function updateWishlistItem(
        User $user,
        string $cardId,
        ?int $priority = null,
        ?string $notes = null,
        ?float $maxPrice = null,
        ?CardVariantEnum $variant = null
    ): Wishlist {
        $wishlistItem = $this->wishlistRepository->findByUserAndCard($user, $cardId);

        if (null === $wishlistItem) {
            throw new \InvalidArgumentException('This card is not in your wishlist');
        }

        if (null !== $priority) {
            $wishlistItem->setPriority($priority);
        }
        if (null !== $notes) {
            $wishlistItem->setNotes($notes);
        }
        if (null !== $maxPrice) {
            $wishlistItem->setMaxPrice((string) $maxPrice);
        }
        if (null !== $variant) {
            $wishlistItem->setVariant($variant);
        }

        $this->entityManager->flush();

        return $wishlistItem;
    }

    /**
     * Récupère la wishlist complète d'un utilisateur.
     *
     * @param array<string, mixed> $filters
     *
     * @return Wishlist[]
     */
    public function getUserWishlist(User $user, array $filters = []): array
    {
        $wishlists = $this->wishlistRepository->findByUser($user, $filters);
        $result = [];
        foreach ($wishlists as $wishlist) {
            $card = $wishlist->getCardId();
            $variant = $wishlist->getVariant();
            $cardEntity = $this->entityManager->getRepository('App\\Entity\\Card')->find($card);
            $variantEntity = null;
            if ($cardEntity && method_exists($cardEntity, 'getVariants') && $variant) {
                foreach ($cardEntity->getVariants() as $v) {
                    if ($v->getType() === $variant) {
                        $variantEntity = $v;

                        break;
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
            $cardName = $cardEntity ? $cardEntity->getName() : null;
            $images = $cardEntity ? $cardEntity->getImages() : null;
            $cardImage = $images && isset($images['small']) ? $images['small'] : null;
            $result[] = [
                'id' => $wishlist->getId(),
                'cardId' => $wishlist->getCardId(),
                'cardName' => $cardName,
                'cardImage' => $cardImage,
                'variant' => $wishlist->getVariant(),
                'priority' => $wishlist->getPriority(),
                'notes' => $wishlist->getNotes(),
                'maxPrice' => $wishlist->getMaxPrice(),
                'createdAt' => $wishlist->getCreatedAt(),
                'updatedAt' => $wishlist->getUpdatedAt(),
                'prices' => $prices,
            ];
        }

        return $result;
    }

    /**
     * Compte le nombre d'éléments dans la wishlist.
     */
    public function getWishlistCount(User $user): int
    {
        return $this->wishlistRepository->countByUser($user);
    }

    /**
     * Obtient les statistiques de la wishlist par priorité.
     *
     * @return array<int, int>
     */
    public function getWishlistStatsByPriority(User $user): array
    {
        return $this->wishlistRepository->getCountByPriority($user);
    }

    /**
     * Vérifie si une carte est dans la wishlist.
     */
    public function isInWishlist(User $user, string $cardId): bool
    {
        return null !== $this->wishlistRepository->findByUserAndCard($user, $cardId);
    }
}
