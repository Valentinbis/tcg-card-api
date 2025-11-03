<?php

namespace App\Tests\Unit\Service;

use App\DTO\CardViewDTO;
use App\Entity\Card;
use App\Entity\User;
use App\Entity\UserCard;
use App\Enum\LanguageEnum;
use App\Repository\CardRepository;
use App\Repository\UserCardRepository;
use App\Service\CardService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class CardServiceTest extends TestCase
{
    private EntityManagerInterface $em;
    private CardService $cardService;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->cardService = new CardService($this->em);
    }

    public function testGetUserCardsWithFiltersReturnsCards(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');
        
        // Mock Card
        $card = $this->createMock(Card::class);
        $card->method('getId')->willReturn('card-1');
        $card->method('getName')->willReturn('Pikachu');
        $card->method('getNameFr')->willReturn('Pikachu');
        $card->method('getNumber')->willReturn(25);
        $card->method('getRarity')->willReturn('Common');
        $card->method('getNationalPokedexNumbers')->willReturn([25]);
        $card->method('getImages')->willReturn([]);
        $card->method('getTypes')->willReturn(['Lightning']);

        // Mock QueryBuilder
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('orderBy')->willReturnSelf();
        
        $query = $this->createMock(AbstractQuery::class);
        $query->method('getResult')->willReturn([$card]);
        $qb->method('getQuery')->willReturn($query);

        // Mock CardRepository
        $cardRepo = $this->createMock(CardRepository::class);
        $cardRepo->method('createQueryBuilder')->willReturn($qb);

        // Mock UserCardRepository
        $userCardRepo = $this->createMock(UserCardRepository::class);
        $userCardRepo->method('findBy')->willReturn([]);

        $this->em->method('getRepository')
            ->willReturnMap([
                [Card::class, $cardRepo],
                [UserCard::class, $userCardRepo],
            ]);

        $result = $this->cardService->getUserCardsWithFilters(
            $user,
            null,
            null,
            null,
            null,
            0,
            10,
            'number',
            'ASC'
        );

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
        self::assertArrayHasKey('total', $result);
        self::assertCount(1, $result['data']);
        self::assertSame(1, $result['total']);
        self::assertInstanceOf(CardViewDTO::class, $result['data'][0]);
    }

    public function testGetUserCardsWithTypeFilter(): void
    {
        $user = new User();
        
        $card1 = $this->createMock(Card::class);
        $card1->method('getId')->willReturn('card-1');
        $card1->method('getName')->willReturn('Pikachu');
        $card1->method('getNameFr')->willReturn('Pikachu');
        $card1->method('getNumber')->willReturn(25);
        $card1->method('getRarity')->willReturn('Common');
        $card1->method('getNationalPokedexNumbers')->willReturn([25]);
        $card1->method('getImages')->willReturn([]);
        $card1->method('getTypes')->willReturn(['Lightning']);

        $card2 = $this->createMock(Card::class);
        $card2->method('getId')->willReturn('card-2');
        $card2->method('getName')->willReturn('Charmander');
        $card2->method('getNameFr')->willReturn('Salamèche');
        $card2->method('getNumber')->willReturn(4);
        $card2->method('getRarity')->willReturn('Common');
        $card2->method('getNationalPokedexNumbers')->willReturn([4]);
        $card2->method('getImages')->willReturn([]);
        $card2->method('getTypes')->willReturn(['Fire']);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('orderBy')->willReturnSelf();
        
        $query = $this->createMock(AbstractQuery::class);
        $query->method('getResult')->willReturn([$card1, $card2]);
        $qb->method('getQuery')->willReturn($query);

        $cardRepo = $this->createMock(CardRepository::class);
        $cardRepo->method('createQueryBuilder')->willReturn($qb);

        $userCardRepo = $this->createMock(UserCardRepository::class);
        $userCardRepo->method('findBy')->willReturn([]);

        $this->em->method('getRepository')
            ->willReturnMap([
                [Card::class, $cardRepo],
                [UserCard::class, $userCardRepo],
            ]);

        $result = $this->cardService->getUserCardsWithFilters(
            $user,
            'Lightning', // Filtre par type Lightning
            null,
            null,
            null,
            0,
            10,
            'number',
            'ASC'
        );

        self::assertCount(1, $result['data']);
        self::assertSame(1, $result['total']);
    }

    public function testUpdateUserCardLanguagesCreatesNewUserCard(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');

        $userCardRepo = $this->createMock(UserCardRepository::class);
        $userCardRepo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')
            ->with(UserCard::class)
            ->willReturn($userCardRepo);

        $this->em->expects(self::once())->method('persist');
        $this->em->expects(self::once())->method('flush');

        $this->cardService->updateUserCardLanguages($user, 1, ['fr', 'reverse']);
    }

    public function testUpdateUserCardLanguagesUpdatesExistingUserCard(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');

        $existingUserCard = new UserCard();
        $existingUserCard->setUserId($user->getId());
        $existingUserCard->setCardId(1);
        $existingUserCard->setLanguages([LanguageEnum::FR]);

        $userCardRepo = $this->createMock(UserCardRepository::class);
        $userCardRepo->method('findOneBy')->willReturn($existingUserCard);

        $this->em->method('getRepository')
            ->with(UserCard::class)
            ->willReturn($userCardRepo);

        $this->em->expects(self::once())->method('persist');
        $this->em->expects(self::once())->method('flush');

        $this->cardService->updateUserCardLanguages($user, 1, ['fr', 'reverse', 'jap']);
    }
}
