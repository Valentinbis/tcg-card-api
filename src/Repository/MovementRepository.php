<?php

namespace App\Repository;

use App\Entity\Movement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movement>
 *
 * @method Movement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movement[]    findAll()
 * @method Movement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movement::class);
    }

    public function findLatestMovement(): ?Movement
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.date', 'DESC')
            ->addOrderBy('m.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function calculateTotalBetweenDates(int $userId, string $start, string $end): float
    {
    
        $query = $this->createQueryBuilder('m')
            ->select('SUM(m.amount) as total')
            ->where('m.date >= :start AND m.date <= :end')
            ->andWhere('m.user = :userId')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('userId', $userId)
            ->getQuery();
    
        $result = $query->getSingleScalarResult();
    
        return $result;
    }
}
