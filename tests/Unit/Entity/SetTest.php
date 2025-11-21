<?php


namespace App\Tests\Unit\Entity;

use App\Entity\Card;
use App\Entity\Set;
use PHPUnit\Framework\TestCase;

class SetTest extends TestCase
{
    private Set $set;

    protected function setUp(): void
    {
        $this->set = new Set();
    }

    public function testGettersAndSetters(): void
    {
        // Test ID
        $this->set->setId('sv01');
        self::assertSame('sv01', $this->set->getId());

        // Test Name
        $this->set->setName('Scarlet & Violet');
        self::assertSame('Scarlet & Violet', $this->set->getName());

        // Test Series
        $this->set->setSeries('Scarlet & Violet');
        self::assertSame('Scarlet & Violet', $this->set->getSeries());

        // Test Printed Total
        $this->set->setPrintedTotal(198);
        self::assertSame(198, $this->set->getPrintedTotal());

        // Test Total
        $this->set->setTotal(252);
        self::assertSame(252, $this->set->getTotal());

        // Test PTCGO Code
        $this->set->setPtcgoCode('SVI');
        self::assertSame('SVI', $this->set->getPtcgoCode());
    }

    public function testLegalities(): void
    {
        $legalities = [
            'standard' => 'Legal',
            'expanded' => 'Legal',
            'unlimited' => 'Legal'
        ];
        
        $this->set->setLegalities($legalities);
        self::assertSame($legalities, $this->set->getLegalities());
    }

    public function testImages(): void
    {
        $images = [
            'symbol' => 'https://example.com/symbol.png',
            'logo' => 'https://example.com/logo.png'
        ];
        
        $this->set->setImages($images);
        self::assertSame($images, $this->set->getImages());
    }

    public function testDates(): void
    {
        // Test Release Date
        $releaseDate = new \DateTime('2023-03-31');
        $this->set->setReleaseDate($releaseDate);
        self::assertSame($releaseDate, $this->set->getReleaseDate());

        // Test Updated At
        $updatedAt = new \DateTime('2025-01-01 12:00:00');
        $this->set->setUpdatedAt($updatedAt);
        self::assertSame($updatedAt, $this->set->getUpdatedAt());
    }

    public function testCardCollection(): void
    {
        // Test initial collection is empty
        self::assertCount(0, $this->set->getCards());

        // Test adding a card
        $card = new Card();
        $card->setId('sv01-001');
        $card->setName('Test Card');

        $this->set->addCard($card);
        self::assertCount(1, $this->set->getCards());
        self::assertTrue($this->set->getCards()->contains($card));
        
        // Verify bidirectional relationship
        self::assertSame($this->set, $card->getSet());

        // Test adding same card twice doesn't duplicate
        $this->set->addCard($card);
        self::assertCount(1, $this->set->getCards());
    }

    public function testNullableFields(): void
    {
        $this->set->setSeries(null);
        self::assertNull($this->set->getSeries());

        $this->set->setPrintedTotal(null);
        self::assertNull($this->set->getPrintedTotal());

        $this->set->setTotal(null);
        self::assertNull($this->set->getTotal());

        $this->set->setLegalities(null);
        self::assertNull($this->set->getLegalities());

        $this->set->setPtcgoCode(null);
        self::assertNull($this->set->getPtcgoCode());

        $this->set->setReleaseDate(null);
        self::assertNull($this->set->getReleaseDate());

        $this->set->setUpdatedAt(null);
        self::assertNull($this->set->getUpdatedAt());

        $this->set->setImages(null);
        self::assertNull($this->set->getImages());
    }

    public function testDefaultValues(): void
    {
        $set = new Set();
        
        self::assertNull($set->getSeries());
        self::assertNull($set->getPrintedTotal());
        self::assertNull($set->getTotal());
        self::assertNull($set->getLegalities());
        self::assertNull($set->getPtcgoCode());
        self::assertNull($set->getReleaseDate());
        self::assertNull($set->getUpdatedAt());
        self::assertNull($set->getImages());
        
        // Test cards collection is initialized
        self::assertCount(0, $set->getCards());
    }
}
