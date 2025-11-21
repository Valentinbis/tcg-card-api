<?php


namespace App\Tests\Unit\Entity;

use App\Entity\Card;
use App\Entity\Set;
use PHPUnit\Framework\TestCase;

class CardTest extends TestCase
{
    private Card $card;

    protected function setUp(): void
    {
        $this->card = new Card();
    }

    public function testGettersAndSetters(): void
    {
        // Test ID
        $this->card->setId('sv01-001');
        self::assertSame('sv01-001', $this->card->getId());

        // Test Name
        $this->card->setName('Pikachu');
        self::assertSame('Pikachu', $this->card->getName());

        // Test Name FR
        $this->card->setNameFr('Pikachu');
        self::assertSame('Pikachu', $this->card->getNameFr());

        // Test Supertype
        $this->card->setSupertype('Pokémon');
        self::assertSame('Pokémon', $this->card->getSupertype());

        // Test Subtypes (array)
        $subtypes = ['Basic'];
        $this->card->setSubtypes($subtypes);
        self::assertSame($subtypes, $this->card->getSubtypes());

        // Test HP
        $this->card->setHp('60');
        self::assertSame('60', $this->card->getHp());

        // Test Types (array)
        $types = ['Lightning'];
        $this->card->setTypes($types);
        self::assertSame($types, $this->card->getTypes());
    }

    public function testEvolutionChain(): void
    {
        // Test EvolvesFrom
        $this->card->setEvolvesFrom('Pichu');
        self::assertSame('Pichu', $this->card->getEvolvesFrom());

        // Test EvolvesTo (array)
        $evolvesTo = ['Raichu'];
        $this->card->setEvolvesTo($evolvesTo);
        self::assertSame($evolvesTo, $this->card->getEvolvesTo());
    }

    public function testGameplayData(): void
    {
        // Test Rules (array)
        $rules = ['Rule 1', 'Rule 2'];
        $this->card->setRules($rules);
        self::assertSame($rules, $this->card->getRules());

        // Test Ancient Trait
        $ancientTrait = ['name' => 'Trait Name', 'text' => 'Trait Description'];
        $this->card->setAncientTrait($ancientTrait);
        self::assertSame($ancientTrait, $this->card->getAncientTrait());

        // Test Abilities (array)
        $abilities = [['name' => 'Static', 'text' => 'Ability text', 'type' => 'Ability']];
        $this->card->setAbilities($abilities);
        self::assertSame($abilities, $this->card->getAbilities());

        // Test Attacks (array)
        $attacks = [
            [
                'name' => 'Thunder Shock',
                'cost' => ['Lightning'],
                'damage' => '20',
                'text' => 'Flip a coin...'
            ]
        ];
        $this->card->setAttacks($attacks);
        self::assertSame($attacks, $this->card->getAttacks());
    }

    public function testWeaknessResistance(): void
    {
        // Test Weaknesses (array)
        $weaknesses = [['type' => 'Fighting', 'value' => '×2']];
        $this->card->setWeaknesses($weaknesses);
        self::assertSame($weaknesses, $this->card->getWeaknesses());

        // Test Resistances (array)
        $resistances = [['type' => 'Metal', 'value' => '-20']];
        $this->card->setResistances($resistances);
        self::assertSame($resistances, $this->card->getResistances());

        // Test Retreat Cost (array)
        $retreatCost = ['Colorless'];
        $this->card->setRetreatCost($retreatCost);
        self::assertSame($retreatCost, $this->card->getRetreatCost());
    }

    public function testCardMetadata(): void
    {
        // Test Number (int type)
        $this->card->setNumber(1);
        self::assertSame(1, $this->card->getNumber());

        // Test Artist
        $this->card->setArtist('Ken Sugimori');
        self::assertSame('Ken Sugimori', $this->card->getArtist());

        // Test Rarity
        $this->card->setRarity('Common');
        self::assertSame('Common', $this->card->getRarity());

        // Test Flavor Text
        $this->card->setFlavorText('When several of these Pokémon gather...');
        self::assertSame('When several of these Pokémon gather...', $this->card->getFlavorText());

        // Test National Pokedex Numbers
        $nationalPokedexNumbers = [25];
        $this->card->setNationalPokedexNumbers($nationalPokedexNumbers);
        self::assertSame($nationalPokedexNumbers, $this->card->getNationalPokedexNumbers());
    }

    public function testLegalities(): void
    {
        $legalities = [
            'standard' => 'Legal',
            'expanded' => 'Legal',
            'unlimited' => 'Legal'
        ];
        
        $this->card->setLegalities($legalities);
        self::assertSame($legalities, $this->card->getLegalities());
    }

    public function testImages(): void
    {
        $images = [
            'small' => 'https://example.com/small.png',
            'large' => 'https://example.com/large.png'
        ];
        
        $this->card->setImages($images);
        self::assertSame($images, $this->card->getImages());
    }

    public function testTcgplayer(): void
    {
        $tcgplayer = [
            'url' => 'https://tcgplayer.com/product/123',
            'updatedAt' => '2025-01-01',
            'prices' => ['normal' => ['low' => 1.0, 'mid' => 2.0, 'high' => 3.0]]
        ];
        
        $this->card->setTcgplayer($tcgplayer);
        self::assertSame($tcgplayer, $this->card->getTcgplayer());
    }

    public function testCardmarket(): void
    {
        $cardmarket = [
            'url' => 'https://cardmarket.com/product/123',
            'updatedAt' => '2025-01-01',
            'prices' => ['averageSellPrice' => 2.5]
        ];
        
        $this->card->setCardmarket($cardmarket);
        self::assertSame($cardmarket, $this->card->getCardmarket());
    }

    public function testSetRelation(): void
    {
        $set = new Set();
        $set->setId('sv01');
        $set->setName('Scarlet & Violet');
        
        $this->card->setSet($set);
        self::assertSame($set, $this->card->getSet());
        self::assertSame('sv01', $this->card->getSet()->getId());
    }

    public function testNullableFields(): void
    {
        // Vérifier que les champs nullable acceptent null
        $this->card->setNameFr(null);
        self::assertNull($this->card->getNameFr());

        $this->card->setSupertype(null);
        self::assertNull($this->card->getSupertype());

        $this->card->setSubtypes(null);
        self::assertNull($this->card->getSubtypes());

        $this->card->setHp(null);
        self::assertNull($this->card->getHp());

        $this->card->setEvolvesFrom(null);
        self::assertNull($this->card->getEvolvesFrom());
    }

    public function testDefaultValues(): void
    {
        $card = new Card();
        
        // Les valeurs par défaut doivent être null pour les champs optionnels
        self::assertNull($card->getNameFr());
        self::assertNull($card->getSupertype());
        self::assertNull($card->getSubtypes());
        self::assertNull($card->getHp());
        self::assertNull($card->getTypes());
    }
}
