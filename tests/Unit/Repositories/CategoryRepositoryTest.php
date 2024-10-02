<?php

namespace App\Tests\Unit\Repositories;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class CategoryRepositoryTest extends TestCase
{
    private $entityManager;
    private $categoryRepository;

    protected function setUp(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $registry->method('getManagerForClass')->willReturn($this->entityManager);

        $this->categoryRepository = new CategoryRepository($registry);
    }

    // public function testFindCategoriesWithoutParent()
    // {
    //     $category1 = new Category();
    //     $category1->setParent(null);

    //     $category2 = new Category();
    //     $category2->setParent(null);

    //     $this->entityManager->expects($this->once())
    //         ->method('createQueryBuilder')
    //         ->willReturn($this->getQueryBuilderMock([$category1, $category2]));

    //     $result = $this->categoryRepository->findCategoriesWithoutParent();

    //     $this->assertCount(2, $result);
    //     $this->assertSame($category1, $result[0]);
    //     $this->assertSame($category2, $result[1]);
    // }

    private function getQueryBuilderMock($result)
    {
        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn($result);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('c.parent IS NOT NULL')
            ->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        return $queryBuilder;
    }
}
