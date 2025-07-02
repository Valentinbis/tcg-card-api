<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'cards')]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['card:read'])]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['card:read'])]
    private string $name;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $supertype = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $subtypes = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $hp = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $types = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $evolvesFrom = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $evolvesTo = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $rules = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $ancientTrait = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $abilities = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $attacks = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $weaknesses = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $resistances = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $retreatCost = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $convertedRetreatCost = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Groups(['card:read'])]
    private ?string $number = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $artist = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Groups(['card:read'])]
    private ?string $rarity = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $flavorText = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['card:read'])]
    private ?array $nationalPokedexNumbers = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $legalities = null;

    #[ORM\Column(type: 'string', length: 5, nullable: true)]
    private ?string $regulationMark = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['card:read'])]
    private ?array $images;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $tcgplayer = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $cardmarket = null;

    #[ORM\ManyToOne(targetEntity: Set::class, inversedBy: 'cards')]
    #[ORM\JoinColumn(name: 'set_id', referencedColumnName: 'id', nullable: false)]
    private Set $set;

    // #[ORM\ManyToMany(targetEntity: Booster::class, inversedBy: 'cards', cascade: ['persist'])]
    // #[ORM\JoinTable(name: 'card_booster')]
    // private Collection $boosters;
    #[ORM\ManyToMany(targetEntity: Booster::class, inversedBy: 'cards', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'card_booster',
        joinColumns: [
            new ORM\JoinColumn(name: 'card_id', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'booster_name', referencedColumnName: 'name')
        ]
    )]
    private Collection $boosters;

    public function __construct()
    {
        $this->boosters = new ArrayCollection();
    }

    // === Getters & Setters (exemples représentatifs) ===

    public function getId(): string { return $this->id; }
    public function setId(string $id): self { $this->id = $id; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getSupertype(): ?string { return $this->supertype; }
    public function setSupertype(?string $supertype): self { $this->supertype = $supertype; return $this; }

    public function getSubtypes(): ?array { return $this->subtypes; }
    public function setSubtypes(?array $subtypes): self { $this->subtypes = $subtypes; return $this; }

    public function getHp(): ?string { return $this->hp; }
    public function setHp(?string $hp): self { $this->hp = $hp; return $this; }

    public function getTypes(): ?array { return $this->types; }
    public function setTypes(?array $types): self { $this->types = $types; return $this; }

    public function getEvolvesFrom(): ?string { return $this->evolvesFrom; }
    public function setEvolvesFrom(?string $evolvesFrom): self { $this->evolvesFrom = $evolvesFrom; return $this; }

    public function getEvolvesTo(): ?array { return $this->evolvesTo; }
    public function setEvolvesTo(?array $evolvesTo): self { $this->evolvesTo = $evolvesTo; return $this; }

    public function getRules(): ?array { return $this->rules; }
    public function setRules(?array $rules): self { $this->rules = $rules; return $this; }

    public function getAncientTrait(): ?array { return $this->ancientTrait; }
    public function setAncientTrait(?array $ancientTrait): self { $this->ancientTrait = $ancientTrait; return $this; }

    public function getAbilities(): ?array { return $this->abilities; }
    public function setAbilities(?array $abilities): self { $this->abilities = $abilities; return $this; }

    public function getAttacks(): ?array { return $this->attacks; }
    public function setAttacks(?array $attacks): self { $this->attacks = $attacks; return $this; }

    public function getWeaknesses(): ?array { return $this->weaknesses; }
    public function setWeaknesses(?array $weaknesses): self { $this->weaknesses = $weaknesses; return $this; }

    public function getResistances(): ?array { return $this->resistances; }
    public function setResistances(?array $resistances): self { $this->resistances = $resistances; return $this; }

    public function getRetreatCost(): ?array { return $this->retreatCost; }
    public function setRetreatCost(?array $retreatCost): self { $this->retreatCost = $retreatCost; return $this; }

    public function getConvertedRetreatCost(): ?int { return $this->convertedRetreatCost; }
    public function setConvertedRetreatCost(?int $convertedRetreatCost): self { $this->convertedRetreatCost = $convertedRetreatCost; return $this; }

    public function getNumber(): ?string { return $this->number; }
    public function setNumber(?string $number): self { $this->number = $number; return $this; }

    public function getArtist(): ?string { return $this->artist; }
    public function setArtist(?string $artist): self { $this->artist = $artist; return $this; }

    public function getRarity(): ?string { return $this->rarity; }
    public function setRarity(?string $rarity): self { $this->rarity = $rarity; return $this; }

    public function getFlavorText(): ?string { return $this->flavorText; }
    public function setFlavorText(?string $flavorText): self { $this->flavorText = $flavorText; return $this; }

    public function getNationalPokedexNumbers(): ?array { return $this->nationalPokedexNumbers; }
    public function setNationalPokedexNumbers(?array $nationalPokedexNumbers): self { $this->nationalPokedexNumbers = $nationalPokedexNumbers; return $this; }

    public function getLegalities(): ?array { return $this->legalities; }
    public function setLegalities(?array $legalities): self { $this->legalities = $legalities; return $this; }

    public function getRegulationMark(): ?string { return $this->regulationMark; }
    public function setRegulationMark(?string $regulationMark): self { $this->regulationMark = $regulationMark; return $this; }

    public function getImages(): ?array { return $this->images; }
    public function setImages(?array $images): self { $this->images = $images; return $this; }

    public function getTcgplayer(): ?array { return $this->tcgplayer; }
    public function setTcgplayer(?array $tcgplayer): self { $this->tcgplayer = $tcgplayer; return $this; }

    public function getCardmarket(): ?array { return $this->cardmarket; }
    public function setCardmarket(?array $cardmarket): self { $this->cardmarket = $cardmarket; return $this; }

    public function getSet(): Set { return $this->set; }
    public function setSet(Set $set): self { $this->set = $set; return $this; }

    /** @return Collection<int, Booster> */
    public function getBoosters(): Collection { return $this->boosters; }

    public function addBooster(Booster $booster): self
    {
        if (!$this->boosters->contains($booster)) {
            $this->boosters->add($booster);
        }
        return $this;
    }

    public function removeBooster(Booster $booster): self
    {
        $this->boosters->removeElement($booster);
        return $this;
    }
}
