<?php

namespace App\Repository;

use App\DTO\MovementFilterDTO;
use App\DTO\PaginationDTO;
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

    public function calculateTotalBetweenDates(int $userId, string $start, string $end): float | null
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

    public function findByCriteria(MovementFilterDTO $criteria, ?PaginationDTO $pagination)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m')
            ->where('m.user = :user')
            ->setParameter('user', $criteria->user);

        if (null !== $criteria->type) {
            $qb->andWhere('m.type = :type')
               ->setParameter('type', $criteria->type);
        }
    
        if (null !== $criteria->categoryId) {
            $qb->andWhere('m.category = :categoryId')
               ->setParameter('categoryId', $criteria->categoryId);
        }

        if (null !== $criteria->startDate && null !== $criteria->endDate) {
            $qb->andWhere('m.date BETWEEN :startDate AND :endDate')
               ->setParameter('startDate', $criteria->startDate)
               ->setParameter('endDate', $criteria->endDate);
        }

        // Tri
        if (null !== $pagination->sort) {
            // foreach ($pagination->sort as $key => $value) {
                $qb->addOrderBy('m.' . $pagination->sort, $pagination->order);
            // }
        }
    
        // Pagination
        if (null !== $pagination->page && null !== $pagination->limit) {
            $qb->setFirstResult(($pagination->page - 1) * $pagination->limit)
               ->setMaxResults($pagination->limit);
        }
    
        return $qb->getQuery()->getResult();
    }
}
