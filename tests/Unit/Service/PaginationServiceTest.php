<?php

namespace App\Tests\Unit\Service;

use App\Service\PaginationService;
use PHPUnit\Framework\TestCase;

final class PaginationServiceTest extends TestCase
{
    private PaginationService $paginationService;

    protected function setUp(): void
    {
        $this->paginationService = new PaginationService();
    }

    public function testPaginateReturnsCorrectStructure(): void
    {
        $result = $this->paginationService->paginate(100, 10, 1);

        self::assertIsArray($result);
        self::assertArrayHasKey('current_page', $result);
        self::assertArrayHasKey('per_page', $result);
        self::assertArrayHasKey('total_items', $result);
        self::assertArrayHasKey('total_pages', $result);
    }

    public function testPaginateCalculatesCorrectTotalPages(): void
    {
        $result = $this->paginationService->paginate(100, 10, 1);

        self::assertEquals(10, $result['total_pages']);
        self::assertSame(1, $result['current_page']);
        self::assertSame(10, $result['per_page']);
        self::assertSame(100, $result['total_items']);
    }

    public function testPaginateWithPartialPage(): void
    {
        $result = $this->paginationService->paginate(95, 10, 1);

        self::assertEquals(10, $result['total_pages']); // 95 / 10 = 9.5 => ceil = 10
    }

    public function testPaginateWithExactDivision(): void
    {
        $result = $this->paginationService->paginate(50, 10, 1);

        self::assertEquals(5, $result['total_pages']);
    }

    public function testPaginateWithDifferentPageSizes(): void
    {
        $result = $this->paginationService->paginate(100, 20, 3);

        self::assertEquals(5, $result['total_pages']); // 100 / 20 = 5
        self::assertSame(3, $result['current_page']);
        self::assertSame(20, $result['per_page']);
    }

    public function testPaginateWithSmallDataset(): void
    {
        $result = $this->paginationService->paginate(5, 10, 1);

        self::assertEquals(1, $result['total_pages']);
        self::assertSame(5, $result['total_items']);
    }

    public function testPaginateWithZeroItems(): void
    {
        $result = $this->paginationService->paginate(0, 10, 1);

        self::assertEquals(0, $result['total_pages']);
        self::assertSame(0, $result['total_items']);
    }
}
