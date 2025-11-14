<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Entity\Wishlist;
use App\Repository\WishlistRepository;
use App\Service\WishlistService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class WishlistServiceTest extends TestCase
{
    private WishlistService $wishlistService;
    private EntityManagerInterface $entityManager;
    private WishlistRepository $wishlistRepository;
    private User $user;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->wishlistRepository = $this->createMock(WishlistRepository::class);
        
        $this->wishlistService = new WishlistService(
            $this->entityManager,
            $this->wishlistRepository
        );

        $this->user = new User();
        $this->user->setEmail('test@example.com');
    }

    public function testAddToWishlistSuccess(): void
    {
        $cardId = 'base1-4';
        $priority = 5;
        $notes = 'Must have card';
        $maxPrice = 99.99;

        $this->wishlistRepository
            ->expects($this->once())
            ->method('findByUserAndCard')
            ->with($this->user, $cardId)
            ->willReturn(null);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Wishlist::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->wishlistService->addToWishlist(
            $this->user,
            $cardId,
            $priority,
            $notes,
            $maxPrice
        );

        $this->assertInstanceOf(Wishlist::class, $result);
        $this->assertSame($this->user, $result->getUser());
        $this->assertSame($cardId, $result->getCardId());
        $this->assertSame($priority, $result->getPriority());
        $this->assertSame($notes, $result->getNotes());
        $this->assertSame('99.99', $result->getMaxPrice());
    }

    public function testAddToWishlistWithDefaultPriority(): void
    {
        $cardId = 'base1-4';

        $this->wishlistRepository
            ->expects($this->once())
            ->method('findByUserAndCard')
            ->with($this->user, $cardId)
            ->willReturn(null);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->wishlistService->addToWishlist($this->user, $cardId);

        $this->assertSame(0, $result->getPriority());
        $this->assertNull($result->getNotes());
        $this->assertNull($result->getMaxPrice());
    }

    public function testAddToWishlistThrowsExceptionWhenCardAlreadyExists(): void
    {
        $cardId = 'base1-4';
        $existingItem = new Wishlist();

        $this->wishlistRepository
            ->expects($this->once())
            ->method('findByUserAndCard')
            ->with($this->user, $cardId)
            ->willReturn($existingItem);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This card is already in your wishlist');

        $this->wishlistService->addToWishlist($this->user, $cardId);
    }

    public function testRemoveFromWishlistSuccess(): void
    {
        $cardId = 'base1-4';
        $wishlistItem = new Wishlist();

        $this->wishlistRepository
            ->expects($this->once())
            ->method('findByUserAndCard')
            ->with($this->user, $cardId)
            ->willReturn($wishlistItem);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($wishlistItem);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->wishlistService->removeFromWishlist($this->user, $cardId);
    }

    public function testRemoveFromWishlistThrowsExceptionWhenCardNotFound(): void
    {
        $cardId = 'base1-4';

        $this->wishlistRepository
            ->expects($this->once())
            ->method('findByUserAndCard')
            ->with($this->user, $cardId)
            ->willReturn(null);

        $this->entityManager->expects($this->never())->method('remove');
        $this->entityManager->expects($this->never())->method('flush');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This card is not in your wishlist');

        $this->wishlistService->removeFromWishlist($this->user, $cardId);
    }

    public function testUpdateWishlistItemSuccess(): void
    {
        $cardId = 'base1-4';
        $wishlistItem = new Wishlist();
        $wishlistItem->setCardId($cardId);
        $wishlistItem->setUser($this->user);
        $wishlistItem->setPriority(0);

        $this->wishlistRepository
            ->expects($this->once())
            ->method('findByUserAndCard')
            ->with($this->user, $cardId)
            ->willReturn($wishlistItem);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $newPriority = 5;
        $newNotes = 'Updated notes';
        $newMaxPrice = 75.50;

        $result = $this->wishlistService->updateWishlistItem(
            $this->user,
            $cardId,
            $newPriority,
            $newNotes,
            $newMaxPrice
        );

        $this->assertSame($wishlistItem, $result);
        $this->assertSame($newPriority, $result->getPriority());
        $this->assertSame($newNotes, $result->getNotes());
        $this->assertSame('75.5', $result->getMaxPrice());
    }

    public function testUpdateWishlistItemPartialUpdate(): void
    {
        $cardId = 'base1-4';
        $wishlistItem = new Wishlist();
        $wishlistItem->setCardId($cardId);
        $wishlistItem->setUser($this->user);
        $wishlistItem->setPriority(3);
        $wishlistItem->setNotes('Original notes');
        $wishlistItem->setMaxPrice('50.00');

        $this->wishlistRepository
            ->expects($this->once())
            ->method('findByUserAndCard')
            ->with($this->user, $cardId)
            ->willReturn($wishlistItem);

        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->wishlistService->updateWishlistItem(
            $this->user,
            $cardId,
            5,
            null,
            null
        );

        $this->assertSame(5, $result->getPriority());
        $this->assertSame('Original notes', $result->getNotes());
        $this->assertSame('50.00', $result->getMaxPrice());
    }

    public function testUpdateWishlistItemThrowsExceptionWhenCardNotFound(): void
    {
        $cardId = 'base1-4';

        $this->wishlistRepository
            ->expects($this->once())
            ->method('findByUserAndCard')
            ->with($this->user, $cardId)
            ->willReturn(null);

        $this->entityManager->expects($this->never())->method('flush');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This card is not in your wishlist');

        $this->wishlistService->updateWishlistItem($this->user, $cardId, 5);
    }

    public function testGetUserWishlist(): void
    {
        $filters = ['minPriority' => 3];
        $expectedItems = [new Wishlist(), new Wishlist()];

        $this->wishlistRepository
            ->expects($this->once())
            ->method('findByUser')
            ->with($this->user, $filters)
            ->willReturn($expectedItems);

        $result = $this->wishlistService->getUserWishlist($this->user, $filters);

        $this->assertSame($expectedItems, $result);
        $this->assertCount(2, $result);
    }

    public function testGetUserWishlistEmpty(): void
    {
        $this->wishlistRepository
            ->expects($this->once())
            ->method('findByUser')
            ->with($this->user, [])
            ->willReturn([]);

        $result = $this->wishlistService->getUserWishlist($this->user);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetWishlistCount(): void
    {
        $expectedCount = 42;

        $this->wishlistRepository
            ->expects($this->once())
            ->method('countByUser')
            ->with($this->user)
            ->willReturn($expectedCount);

        $result = $this->wishlistService->getWishlistCount($this->user);

        $this->assertSame($expectedCount, $result);
    }

    public function testGetWishlistStatsByPriority(): void
    {
        $expectedStats = [
            0 => 5,
            1 => 3,
            2 => 8,
            3 => 12,
            4 => 6,
            5 => 10,
        ];

        $this->wishlistRepository
            ->expects($this->once())
            ->method('getCountByPriority')
            ->with($this->user)
            ->willReturn($expectedStats);

        $result = $this->wishlistService->getWishlistStatsByPriority($this->user);

        $this->assertSame($expectedStats, $result);
        $this->assertCount(6, $result);
    }

    public function testIsInWishlistTrue(): void
    {
        $cardId = 'base1-4';
        $wishlistItem = new Wishlist();

        $this->wishlistRepository
            ->expects($this->once())
            ->method('findByUserAndCard')
            ->with($this->user, $cardId)
            ->willReturn($wishlistItem);

        $result = $this->wishlistService->isInWishlist($this->user, $cardId);

        $this->assertTrue($result);
    }

    public function testIsInWishlistFalse(): void
    {
        $cardId = 'base1-4';

        $this->wishlistRepository
            ->expects($this->once())
            ->method('findByUserAndCard')
            ->with($this->user, $cardId)
            ->willReturn(null);

        $result = $this->wishlistService->isInWishlist($this->user, $cardId);

        $this->assertFalse($result);
    }
}
