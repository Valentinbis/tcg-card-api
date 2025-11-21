<?php


namespace App\Tests\Unit\Entity;

use App\Entity\Booster;
use App\Entity\Card;
use PHPUnit\Framework\TestCase;

class BoosterTest extends TestCase
{
    private Booster $booster;

    protected function setUp(): void
    {
        $this->booster = new Booster();
    }

    public function testGettersAndSetters(): void
    {
        // Test Name (primary key)
        $this->booster->setName('Scarlet & Violet Booster');
        self::assertSame('Scarlet & Violet Booster', $this->booster->getName());

        // Test Logo
        $this->booster->setLogo('https://example.com/logo.png');
        self::assertSame('https://example.com/logo.png', $this->booster->getLogo());

        // Test Artwork Front
        $this->booster->setArtworkFront('https://example.com/front.png');
        self::assertSame('https://example.com/front.png', $this->booster->getArtworkFront());

        // Test Artwork Back
        $this->booster->setArtworkBack('https://example.com/back.png');
        self::assertSame('https://example.com/back.png', $this->booster->getArtworkBack());
    }

    public function testCardCollection(): void
    {
        // Test initial collection is empty
        self::assertCount(0, $this->booster->getCards());

        // Create a mock Card
        $card = $this->createMock(Card::class);
        $card->method('addBooster')->willReturn($card);
        $card->method('removeBooster')->willReturn($card);

        // Test adding a card
        $this->booster->addCard($card);
        self::assertCount(1, $this->booster->getCards());
        self::assertTrue($this->booster->getCards()->contains($card));

        // Test adding same card twice doesn't duplicate
        $this->booster->addCard($card);
        self::assertCount(1, $this->booster->getCards());

        // Test removing a card
        $this->booster->removeCard($card);
        self::assertCount(0, $this->booster->getCards());
        self::assertFalse($this->booster->getCards()->contains($card));
    }

    public function testNullableFields(): void
    {
        $this->booster->setLogo(null);
        self::assertNull($this->booster->getLogo());

        $this->booster->setArtworkFront(null);
        self::assertNull($this->booster->getArtworkFront());

        $this->booster->setArtworkBack(null);
        self::assertNull($this->booster->getArtworkBack());
    }

    public function testDefaultValues(): void
    {
        $booster = new Booster();
        
        self::assertNull($booster->getLogo());
        self::assertNull($booster->getArtworkFront());
        self::assertNull($booster->getArtworkBack());
        
        // Test cards collection is initialized
        self::assertCount(0, $booster->getCards());
    }
}
