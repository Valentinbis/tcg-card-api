<?php

namespace App\Tests\Unit\Entity;

use App\Entity\UserCard;
use App\Enum\LanguageEnum;
use PHPUnit\Framework\TestCase;

class UserCardTest extends TestCase
{
    private UserCard $userCard;

    protected function setUp(): void
    {
        $this->userCard = new UserCard();
    }

    public function testGettersAndSetters(): void
    {
        // Test User ID
        $this->userCard->setUserId(1);
        self::assertSame(1, $this->userCard->getUserId());

        // Test Card ID
        $this->userCard->setCardId(42);
        self::assertSame(42, $this->userCard->getCardId());
    }

    public function testLanguages(): void
    {
        // Test empty languages returns empty array
        self::assertSame([], $this->userCard->getLanguages());

        // Test setting languages with enum values
        $languages = [LanguageEnum::FR, LanguageEnum::REVERSE];
        $this->userCard->setLanguages($languages);
        
        $result = $this->userCard->getLanguages();
        self::assertCount(2, $result);
        self::assertContainsOnlyInstancesOf(LanguageEnum::class, $result);
        self::assertSame(LanguageEnum::FR, $result[0]);
        self::assertSame(LanguageEnum::REVERSE, $result[1]);
    }

    public function testLanguagesWithSingleValue(): void
    {
        $languages = [LanguageEnum::JAP];
        $this->userCard->setLanguages($languages);
        
        $result = $this->userCard->getLanguages();
        self::assertCount(1, $result);
        self::assertSame(LanguageEnum::JAP, $result[0]);
    }

    public function testLanguagesWithAllValues(): void
    {
        $languages = [LanguageEnum::FR, LanguageEnum::REVERSE, LanguageEnum::JAP];
        $this->userCard->setLanguages($languages);
        
        $result = $this->userCard->getLanguages();
        self::assertCount(3, $result);
        self::assertSame(LanguageEnum::FR, $result[0]);
        self::assertSame(LanguageEnum::REVERSE, $result[1]);
        self::assertSame(LanguageEnum::JAP, $result[2]);
    }

    public function testEmptyLanguagesArray(): void
    {
        // Setting empty array should return empty array
        $this->userCard->setLanguages([]);
        self::assertSame([], $this->userCard->getLanguages());
    }

    public function testCompositeKey(): void
    {
        // Test that we can set both IDs (composite key)
        $this->userCard->setUserId(10);
        $this->userCard->setCardId(20);
        
        self::assertSame(10, $this->userCard->getUserId());
        self::assertSame(20, $this->userCard->getCardId());
    }
}
