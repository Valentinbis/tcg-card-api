<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Collection;
use App\Entity\User;
use App\Enum\CardVariantEnum;
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
     * Trouve tous les éléments de la collection d'un utilisateur.
     *
     * @param array<string, mixed> $filters
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

        $orderBy = isset($filters['orderBy']) && is_string($filters['orderBy']) ? $filters['orderBy'] : 'createdAt';
        $direction = isset($filters['direction']) && is_string($filters['direction']) ? $filters['direction'] : 'DESC';
        $qb->orderBy('c.'.$orderBy, $direction);

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve un élément spécifique de la collection.
     */
    public function findByUserAndCard(User $user, string $cardId, ?CardVariantEnum $variant = null): ?Collection
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.cardId = :cardId')
            ->setParameter('user', $user)
            ->setParameter('cardId', $cardId);

        if (null !== $variant) {
            $qb->andWhere('c.variant = :variant')
               ->setParameter('variant', $variant);
        } else {
            $qb->andWhere('c.variant IS NOT NULL');
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Compte le nombre total de cartes dans la collection.
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
     * Compte le nombre de cartes uniques dans la collection.
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
     * Calcule la valeur totale de la collection.
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

        return null !== $result ? (float) $result : 0.0;
    }

    /**
     * Récupère le nombre de cartes par condition.
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
        /** @var array<array{condition: string|null, count: mixed}> $results */
        foreach ($results as $result) {
            $condition = $result['condition'] ?? 'Unknown';
            $counts[$condition] = is_numeric($result['count']) ? (int) $result['count'] : 0;
        }

        return $counts;
    }

    /**
     * Récupère la valeur par condition.
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
        /** @var array<array{condition: string|null, value: mixed}> $results */
        foreach ($results as $result) {
            $condition = $result['condition'] ?? 'Unknown';
            $values[$condition] = null !== $result['value'] && is_numeric($result['value']) ? (float) $result['value'] : 0.0;
        }

        return $values;
    }
}
