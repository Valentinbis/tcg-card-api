<?php

namespace App\Repository;

use App\DTO\MovementFilterDTO;
use App\DTO\PaginationDTO;
use App\Entity\Movement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

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
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Movement::class);
    }

    public function paginateMovement(int $page, int $limit): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('m'),
            $page,
            $limit
        );
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

    public function findGroupByCategories(int $userId, string $type): array
    {
        return $this->createQueryBuilder('m')
            ->select('c.name as category, SUM(m.amount) as total')
            ->join('m.category', 'c')
            ->where('m.user = :userId')
            ->andWhere('m.type = :type')
            // ->andWhere('m.date >= :start AND m.date <= :end')
            ->groupBy('category')
            ->setParameter('userId', $userId)
            ->setParameter('type', $type)
            // ->setParameter('start', date('Y-m-01'))
            // ->setParameter('end', date('Y-m-t'))
            ->getQuery()
            ->getResult();
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

    public function calculateTotalYearlyByMonth(int $userId, string $year): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total', 'total');
        $rsm->addScalarResult('month', 'month');

        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT SUM(m.amount) AS total, EXTRACT(MONTH FROM m.date) AS month
             FROM movement m
             WHERE m.user_id = :userId
             AND EXTRACT(YEAR FROM m.date) = :year
             GROUP BY month',
            $rsm
        );

        $query->setParameter('userId', $userId);
        $query->setParameter('year', $year);
        return $query->getResult();
    }

    public function calculateTotal(int $userId): float | null
    {
        $query = $this->createQueryBuilder('m')
            ->select('SUM(m.amount) as total')
            ->where('m.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery();

        $result = $query->getSingleScalarResult();

        return $result;
    }

    public function findByCriteria(MovementFilterDTO $filter, ?PaginationDTO $pagination)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m')
            ->where('m.user = :user')
            ->setParameter('user', $filter->user);

        if ($filter->type) {
            $qb->andWhere('m.type = :type')
                ->setParameter('type', $filter->type);
        }

        if ($filter->categoryId) {
            $qb->andWhere('m.category = :categoryId')
                ->setParameter('categoryId', $filter->categoryId);
        }

        if ($filter->startDate && $filter->endDate) {
            $qb->andWhere('m.date BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $filter->startDate)
                ->setParameter('endDate', $filter->endDate);
        }


        $paginationResult = $this->paginator->paginate(
            $qb,
            $pagination->page,
            $pagination->limit,
            [
                // Obliger de mettre m.date dans la requête
                'pageParameterName' => 'sort', 'sortDirectionParameterName' => 'order',
                'sortFieldAllowList' => ['m.date'],
            ],
        );

        return $paginationResult;
    }
}
