<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Service\CollectionStatsService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CollectionStatsServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private CollectionStatsService $service;
    private User $user;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->service = new CollectionStatsService($this->entityManager);
        
        $this->user = $this->createMock(User::class);
        $this->user->method('getId')->willReturn(1);
    }

    public function testGetCollectionStatsReturnsArray(): void
    {
        $statement = $this->createMock(Statement::class);
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn([
            [
                'id' => 'base1',
                'name' => 'Base Set',
                'series' => 'Base',
                'total' => 102,
                'printedTotal' => 102,
                'releaseDate' => '1999-01-09',
                'owned' => 50,
            ],
            [
                'id' => 'jungle',
                'name' => 'Jungle',
                'series' => 'Base',
                'total' => 64,
                'printedTotal' => 64,
                'releaseDate' => '1999-06-16',
                'owned' => 32,
            ],
        ]);
        
        $statement->method('executeQuery')->willReturn($result);

        $connection = $this->createMock(Connection::class);
        $connection->method('prepare')->willReturn($statement);

        $this->entityManager->method('getConnection')->willReturn($connection);

        $stats = $this->service->getCollectionStats($this->user);

        $this->assertIsArray($stats);
        $this->assertCount(2, $stats);
        $this->assertEquals('base1', $stats[0]['id']);
        $this->assertEquals('Jungle', $stats[1]['name']);
    }

    public function testGetCollectionStatsCalculatesPercentage(): void
    {
        $statement = $this->createMock(Statement::class);
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn([
            [
                'id' => 'base1',
                'name' => 'Base Set',
                'series' => 'Base',
                'total' => 100,
                'printedTotal' => 100,
                'releaseDate' => '1999-01-09',
                'owned' => 50,
            ],
        ]);
        
        $statement->method('executeQuery')->willReturn($result);

        $connection = $this->createMock(Connection::class);
        $connection->method('prepare')->willReturn($statement);

        $this->entityManager->method('getConnection')->willReturn($connection);

        $stats = $this->service->getCollectionStats($this->user);

        $this->assertEquals(50, $stats[0]['percentage']);
        $this->assertEquals(100, $stats[0]['total']);
        $this->assertEquals(50, $stats[0]['owned']);
    }

    public function testGetCollectionStatsWithNoOwnedCards(): void
    {
        $statement = $this->createMock(Statement::class);
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn([
            [
                'id' => 'base1',
                'name' => 'Base Set',
                'series' => 'Base',
                'total' => 100,
                'printedTotal' => 100,
                'releaseDate' => '1999-01-09',
                'owned' => 0,
            ],
        ]);
        
        $statement->method('executeQuery')->willReturn($result);

        $connection = $this->createMock(Connection::class);
        $connection->method('prepare')->willReturn($statement);

        $this->entityManager->method('getConnection')->willReturn($connection);

        $stats = $this->service->getCollectionStats($this->user);

        $this->assertEquals(0, $stats[0]['percentage']);
        $this->assertEquals(0, $stats[0]['owned']);
    }

    public function testGetCollectionStatsReturnsEmptyArrayWhenNoSets(): void
    {
        $statement = $this->createMock(Statement::class);
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn([]);
        
        $statement->method('executeQuery')->willReturn($result);

        $connection = $this->createMock(Connection::class);
        $connection->method('prepare')->willReturn($statement);

        $this->entityManager->method('getConnection')->willReturn($connection);

        $stats = $this->service->getCollectionStats($this->user);

        $this->assertIsArray($stats);
        $this->assertCount(0, $stats);
    }
}
