<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CollectionStatsService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Récupère les statistiques de collection par set pour un utilisateur.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getCollectionStats(User $user): array
    {
        $query = $this->entityManager->getConnection()->prepare(
            'SELECT 
                s.id,
                s.name,
                s.series,
                s.total,
                s.printed_total as printedTotal,
                s.release_date as releaseDate,
                COUNT(DISTINCT col.card_id) as owned
             FROM sets s
             LEFT JOIN cards c ON c.set_id = s.id
             LEFT JOIN collection col ON col.card_id = c.id AND col.user_id = :userId
             GROUP BY s.id, s.name, s.series, s.total, s.printed_total, s.release_date
             ORDER BY s.release_date DESC'
        );

        $query->bindValue('userId', $user->getId());
        $result = $query->executeQuery();
        $setsData = $result->fetchAllAssociative();

        $sets = [];
        foreach ($setsData as $setData) {
            if (!is_array($setData)) {
                continue;
            }

            $total = is_numeric($setData['total'] ?? null) ? (int) $setData['total'] :
                    (is_numeric($setData['printedTotal'] ?? null) ? (int) $setData['printedTotal'] : 0);
            $owned = is_numeric($setData['owned'] ?? null) ? (int) $setData['owned'] : 0;
            $percentage = $total > 0 ? round(($owned / $total) * 100) : 0;

            $sets[] = [
                'id' => is_string($setData['id'] ?? null) ? $setData['id'] : '',
                'name' => is_string($setData['name'] ?? null) ? $setData['name'] : '',
                'series' => is_string($setData['series'] ?? null) ? $setData['series'] : '',
                'total' => $total,
                'owned' => $owned,
                'percentage' => (int) $percentage,
                'printedTotal' => is_numeric($setData['printedTotal'] ?? null) ? (int) $setData['printedTotal'] : 0,
                'releaseDate' => is_string($setData['releaseDate'] ?? null) ? $setData['releaseDate'] : null,
            ];
        }

        return ['sets' => $sets];
    }
}
