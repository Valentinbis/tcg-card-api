<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserStatsService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Récupère les statistiques complètes d'un utilisateur.
     *
     * @return array<string, mixed>
     */
    public function getUserStats(User $user): array
    {
        return [
            'totalCards' => $this->getTotalCards(),
            'totalOwnedCards' => $this->getTotalOwnedCards($user),
            'completionPercentage' => $this->getCompletionPercentage($user),
            'totalValue' => $this->getTotalValue($user),
            'favoriteType' => $this->getFavoriteType($user),
            'joinDate' => $user->getCreatedAt()?->format('Y-m-d') ?? (new \DateTime())->format('Y-m-d'),
        ];
    }

    private function getTotalCards(): int
    {
        $query = $this->entityManager->createQuery(
            'SELECT COUNT(c.id) FROM App\Entity\Card c'
        );

        return (int) $query->getSingleScalarResult();
    }

    private function getTotalOwnedCards(User $user): int
    {
        $query = $this->entityManager->createQuery(
            'SELECT COUNT(DISTINCT c.cardId) FROM App\\Entity\\Collection c WHERE c.user = :user'
        )->setParameter('user', $user);

        return (int) $query->getSingleScalarResult();
    }

    private function getCompletionPercentage(User $user): int
    {
        $totalCards = $this->getTotalCards();
        $ownedCards = $this->getTotalOwnedCards($user);

        if ($totalCards === 0) {
            return 0;
        }

        return (int) round(($ownedCards / $totalCards) * 100);
    }

    private function getTotalValue(User $user): float
    {
        // TODO: Implémenter le calcul de la valeur totale quand les prix seront dans la DB
        return 0.0;
    }

    private function getFavoriteType(User $user): string
    {
        $query = $this->entityManager->getConnection()->prepare(
            'SELECT c.types 
             FROM cards c 
             INNER JOIN collection col ON c.id = col.card_id 
             WHERE col.user_id = :userId AND c.types IS NOT NULL'
        );
        
        $query->bindValue('userId', $user->getId());
        $result = $query->executeQuery();
        $types = $result->fetchAllAssociative();

        $typeCounts = [];
        foreach ($types as $row) {
            $cardTypes = json_decode($row['types'], true);
            if (is_array($cardTypes)) {
                foreach ($cardTypes as $type) {
                    $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
                }
            }
        }

        if (empty($typeCounts)) {
            return 'Aucun';
        }

        arsort($typeCounts);

        return (string) array_key_first($typeCounts);
    }
}
