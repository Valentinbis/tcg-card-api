<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Wishlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wishlist>
 */
class WishlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }

    /**
     * Trouve tous les éléments de la wishlist d'un utilisateur
     *
     * @return Wishlist[]
     */
    public function findByUser(User $user, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('w')
            ->where('w.user = :user')
            ->setParameter('user', $user);

        if (isset($filters['minPriority'])) {
            $qb->andWhere('w.priority >= :minPriority')
               ->setParameter('minPriority', $filters['minPriority']);
        }

        if (isset($filters['minPrice'])) {
            $qb->andWhere('w.maxPrice IS NOT NULL AND w.maxPrice >= :minPrice')
               ->setParameter('minPrice', $filters['minPrice']);
        }
        if (isset($filters['maxPrice'])) {
            $qb->andWhere('w.maxPrice IS NOT NULL AND w.maxPrice <= :maxPrice')
               ->setParameter('maxPrice', $filters['maxPrice']);
        }

        $orderBy = $filters['orderBy'] ?? 'priority';
        $direction = $filters['direction'] ?? 'DESC';
        $qb->orderBy('w.' . $orderBy, $direction);

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve un élément spécifique de la wishlist
     */
    public function findByUserAndCard(User $user, string $cardId): ?Wishlist
    {
        return $this->createQueryBuilder('w')
            ->where('w.user = :user')
            ->andWhere('w.cardId = :cardId')
            ->setParameter('user', $user)
            ->setParameter('cardId', $cardId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compte le nombre d'éléments dans la wishlist d'un utilisateur
     */
    public function countByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Obtient les cartes wishlist par priorité
     *
     * @return array<int, int>
     */
    public function getCountByPriority(User $user): array
    {
        $results = $this->createQueryBuilder('w')
            ->select('w.priority, COUNT(w.id) as count')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->groupBy('w.priority')
            ->orderBy('w.priority', 'DESC')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $result) {
            $counts[$result['priority']] = (int) $result['count'];
        }

        return $counts;
    }
}
