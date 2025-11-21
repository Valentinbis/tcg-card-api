<?php

namespace App\Repository;

use App\Entity\Collection;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Collection>
 */
class CollectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collection::class);
    }

    /**
     * Trouve tous les éléments de la collection d'un utilisateur
     *
     * @return Collection[]
     */
    public function findByUser(User $user, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->setParameter('user', $user);

        if (isset($filters['condition'])) {
            $qb->andWhere('c.condition = :condition')
               ->setParameter('condition', $filters['condition']);
        }

        if (isset($filters['minQuantity'])) {
            $qb->andWhere('c.quantity >= :minQuantity')
               ->setParameter('minQuantity', $filters['minQuantity']);
        }

        if (isset($filters['minPrice'])) {
            $qb->andWhere('c.purchasePrice >= :minPrice')
               ->setParameter('minPrice', $filters['minPrice']);
        }

        if (isset($filters['maxPrice'])) {
            $qb->andWhere('c.purchasePrice <= :maxPrice')
               ->setParameter('maxPrice', $filters['maxPrice']);
        }

        if (isset($filters['variant'])) {
            $qb->andWhere('c.variant = :variant')
               ->setParameter('variant', $filters['variant']);
        }

        $orderBy = $filters['orderBy'] ?? 'createdAt';
        $direction = $filters['direction'] ?? 'DESC';
        $qb->orderBy('c.' . $orderBy, $direction);

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve un élément spécifique de la collection
     */
    public function findByUserAndCard(User $user, string $cardId): ?Collection
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.cardId = :cardId')
            ->setParameter('user', $user)
            ->setParameter('cardId', $cardId)
            ->andWhere('c.variant IS NOT NULL')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compte le nombre total de cartes dans la collection
     */
    public function countByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('SUM(c.quantity)')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre de cartes uniques dans la collection
     */
    public function countUniqueCardsByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Calcule la valeur totale de la collection
     */
    public function getTotalValue(User $user): float
    {
        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.purchasePrice * c.quantity)')
            ->where('c.user = :user')
            ->andWhere('c.purchasePrice IS NOT NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : 0.0;
    }

    /**
     * Récupère le nombre de cartes par condition
     *
     * @return array<string, int>
     */
    public function getCountByCondition(User $user): array
    {
        $results = $this->createQueryBuilder('c')
            ->select('c.condition', 'COUNT(c.id) as count')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->groupBy('c.condition')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $result) {
            $condition = $result['condition'] ?? 'Unknown';
            $counts[$condition] = (int) $result['count'];
        }

        return $counts;
    }

    /**
     * Récupère la valeur par condition
     *
     * @return array<string, float>
     */
    public function getValueByCondition(User $user): array
    {
        $results = $this->createQueryBuilder('c')
            ->select('c.condition', 'SUM(c.purchasePrice * c.quantity) as value')
            ->where('c.user = :user')
            ->andWhere('c.purchasePrice IS NOT NULL')
            ->setParameter('user', $user)
            ->groupBy('c.condition')
            ->getQuery()
            ->getResult();

        $values = [];
        foreach ($results as $result) {
            $condition = $result['condition'] ?? 'Unknown';
            $values[$condition] = $result['value'] !== null ? (float) $result['value'] : 0.0;
        }

        return $values;
    }
}