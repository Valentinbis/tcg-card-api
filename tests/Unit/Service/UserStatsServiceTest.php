<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Service\UserStatsService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class UserStatsServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private UserStatsService $service;
    private User $user;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->service = new UserStatsService($this->entityManager);
        
        $this->user = $this->createMock(User::class);
        $this->user->method('getId')->willReturn(1);
        $this->user->method('getCreatedAt')->willReturn(new \DateTime('2024-01-01'));
    }

    public function testGetUserStatsReturnsCorrectStructure(): void
    {
        // Mock pour le total des cartes
        $totalCardsQuery = $this->createMock(AbstractQuery::class);
        $totalCardsQuery->method('getSingleScalarResult')->willReturn(1000);

        // Mock pour les cartes possédées
        $ownedCardsQuery = $this->createMock(AbstractQuery::class);
        $ownedCardsQuery->method('getSingleScalarResult')->willReturn(250);

        $this->entityManager
            ->method('createQuery')
            ->willReturnOnConsecutiveCalls($totalCardsQuery, $ownedCardsQuery);

        // Mock pour le type favori
        $statement = $this->createMock(Statement::class);
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn([
            ['types' => '["Fire", "Water"]'],
            ['types' => '["Fire", "Electric"]'],
            ['types' => '["Fire"]'],
        ]);
        $statement->method('executeQuery')->willReturn($result);

        $connection = $this->createMock(Connection::class);
        $connection->method('prepare')->willReturn($statement);

        $this->entityManager->method('getConnection')->willReturn($connection);

        $stats = $this->service->getUserStats($this->user);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('totalCards', $stats);
        $this->assertArrayHasKey('totalOwnedCards', $stats);
        $this->assertArrayHasKey('completionPercentage', $stats);
        $this->assertArrayHasKey('totalValue', $stats);
        $this->assertArrayHasKey('favoriteType', $stats);
        $this->assertArrayHasKey('joinDate', $stats);
    }

    public function testGetUserStatsCalculatesCorrectPercentage(): void
    {
        $totalCardsQuery = $this->createMock(AbstractQuery::class);
        $totalCardsQuery->method('getSingleScalarResult')->willReturn(1000);

        $ownedCardsQuery = $this->createMock(AbstractQuery::class);
        $ownedCardsQuery->method('getSingleScalarResult')->willReturn(250);

        $this->entityManager
            ->method('createQuery')
            ->willReturnOnConsecutiveCalls($totalCardsQuery, $ownedCardsQuery);

        $statement = $this->createMock(Statement::class);
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn([]);
        $statement->method('executeQuery')->willReturn($result);

        $connection = $this->createMock(Connection::class);
        $connection->method('prepare')->willReturn($statement);
        $this->entityManager->method('getConnection')->willReturn($connection);

        $stats = $this->service->getUserStats($this->user);

        $this->assertEquals(1000, $stats['totalCards']);
        $this->assertEquals(250, $stats['totalOwnedCards']);
        $this->assertEquals(25, $stats['completionPercentage']);
    }

    public function testGetUserStatsWithNoCardsReturnsZeroPercentage(): void
    {
        $totalCardsQuery = $this->createMock(AbstractQuery::class);
        $totalCardsQuery->method('getSingleScalarResult')->willReturn(0);

        $ownedCardsQuery = $this->createMock(AbstractQuery::class);
        $ownedCardsQuery->method('getSingleScalarResult')->willReturn(0);

        $this->entityManager
            ->method('createQuery')
            ->willReturnOnConsecutiveCalls($totalCardsQuery, $ownedCardsQuery);

        $statement = $this->createMock(Statement::class);
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn([]);
        $statement->method('executeQuery')->willReturn($result);

        $connection = $this->createMock(Connection::class);
        $connection->method('prepare')->willReturn($statement);
        $this->entityManager->method('getConnection')->willReturn($connection);

        $stats = $this->service->getUserStats($this->user);

        $this->assertEquals(0, $stats['completionPercentage']);
    }

    public function testGetUserStatsReturnsMostFrequentType(): void
    {
        $totalCardsQuery = $this->createMock(AbstractQuery::class);
        $totalCardsQuery->method('getSingleScalarResult')->willReturn(100);

        $ownedCardsQuery = $this->createMock(AbstractQuery::class);
        $ownedCardsQuery->method('getSingleScalarResult')->willReturn(50);

        $this->entityManager
            ->method('createQuery')
            ->willReturnOnConsecutiveCalls($totalCardsQuery, $ownedCardsQuery);

        $statement = $this->createMock(Statement::class);
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn([
            ['types' => '["Fire", "Water"]'],
            ['types' => '["Fire", "Electric"]'],
            ['types' => '["Fire"]'],
            ['types' => '["Water"]'],
        ]);
        $statement->method('executeQuery')->willReturn($result);

        $connection = $this->createMock(Connection::class);
        $connection->method('prepare')->willReturn($statement);
        $this->entityManager->method('getConnection')->willReturn($connection);

        $stats = $this->service->getUserStats($this->user);

        $this->assertEquals('Fire', $stats['favoriteType']);
    }
}
