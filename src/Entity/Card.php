<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
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

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['card:read'])]
    private ?string $nameFr = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $supertype = null;

    #[ORM\Column(type: 'json', nullable: true)]
    /** @var array<string>|null */
    private ?array $subtypes = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $hp = null;

    #[ORM\Column(type: 'json', nullable: true)]
    /** @var array<string>|null */
    private ?array $types = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $evolvesFrom = null;

    #[ORM\Column(type: 'json', nullable: true)]
    /** @var array<string>|null */
    private ?array $evolvesTo = null;

    #[ORM\Column(type: 'json', nullable: true)]
    /** @var array<string>|null */
    private ?array $rules = null;

    #[ORM\Column(type: 'json', nullable: true)]
    /** @var array<mixed>|null */
    private ?array $ancientTrait = null;

    #[ORM\Column(type: 'json', nullable: true)]
    /** @var array<mixed>|null */
    private ?array $abilities = null;

    #[ORM\Column(type: 'json', nullable: true)]
    /** @var array<mixed>|null */
    private ?array $attacks = null;

    #[ORM\Column(type: 'json', nullable: true)]
    /** @var array<mixed>|null */
    private ?array $weaknesses = null;

    #[ORM\Column(type: 'json', nullable: true)]
    /** @var array<mixed>|null */
    private ?array $resistances = null;

    #[ORM\Column(type: 'json', nullable: true)]
    /** @var array<string>|null */
    private ?array $retreatCost = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $convertedRetreatCost = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
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
    /** @var array<int>|null */
    private ?array $nationalPokedexNumbers = null;

    #[ORM\Column(type: 'json', nullable: true)]
    /** @var array<string, string>|null */
    private ?array $legalities = null;

    #[ORM\Column(type: 'string', length: 5, nullable: true)]
    private ?string $regulationMark = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['card:read'])]
    /** @var array<string, string>|null */
    private ?array $images;

    /**
     * Propriété temporaire pour l'upload d'image dans l'admin.
     */
    private mixed $uploadedImage;

    #[ORM\ManyToOne(targetEntity: Set::class, inversedBy: 'cards')]
    #[ORM\JoinColumn(name: 'set_id', referencedColumnName: 'id', nullable: false)]
    private Set $set;

    // #[ORM\ManyToMany(targetEntity: Booster::class, inversedBy: 'cards', cascade: ['persist'])]
    // #[ORM\JoinTable(name: 'card_booster')]
    // private Collection $boosters;
    #[ORM\ManyToMany(targetEntity: Booster::class, inversedBy: 'cards', cascade: ['persist'])]
    #[ORM\JoinTable(
        name: 'card_booster',
        joinColumns: [
            new ORM\JoinColumn(name: 'card_id', referencedColumnName: 'id'),
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'booster_name', referencedColumnName: 'name'),
        ]
    )]
    /** @var Collection<int, Booster> */
    private Collection $boosters;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CardVariant", mappedBy="card", cascade={"persist", "remove"})
     *
     * @var Collection<int, CardVariant>
     */
    private $variants;

    public function __construct()
    {
        $this->boosters = new ArrayCollection();
        $this->variants = new ArrayCollection();
    }

    /**
     * @return Collection<int, CardVariant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(CardVariant $variant): self
    {
        if (!$this->variants->contains($variant)) {
            $this->variants->add($variant);
            $variant->setCard($this);
        }

        return $this;
    }

    public function removeVariant(CardVariant $variant): self
    {
        if ($this->variants->contains($variant)) {
            $this->variants->removeElement($variant);
            if ($variant->getCard() === $this) {
                $variant->setCard(null);
            }
        }

        return $this;
    }

    // === Getters & Setters (exemples représentatifs) ===

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNameFr(): ?string
    {
        return $this->nameFr;
    }

    public function setNameFr(?string $nameFr): self
    {
        $this->nameFr = $nameFr;

        return $this;
    }

    public function getSupertype(): ?string
    {
        return $this->supertype;
    }

    public function setSupertype(?string $supertype): self
    {
        $this->supertype = $supertype;

        return $this;
    }

    /**
     * @return array<string>|null
     */
    public function getSubtypes(): ?array
    {
        /* @var array<string>|null */
        return $this->subtypes;
    }

    /**
     * @param array<string>|null $subtypes
     */
    public function setSubtypes(?array $subtypes): self
    {
        $this->subtypes = $subtypes;

        return $this;
    }

    public function getHp(): ?string
    {
        return $this->hp;
    }

    public function setHp(?string $hp): self
    {
        $this->hp = $hp;

        return $this;
    }

    public function getTypesAsString(): string
    {
        if (is_array($this->types)) {
            return implode(', ', $this->types);
        }

        return '';
    }

    public function setTypesFromString(?string $typesString): self
    {
        if (is_string($typesString) && !empty($typesString)) {
            $this->types = array_map('trim', explode(',', $typesString));
        } else {
            $this->types = null;
        }

        return $this;
    }

    /**
     * @param array<string>|null $types
     */
    public function setTypes(?array $types): self
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @return array<string>|null
     */
    public function getTypes(): ?array
    {
        /* @var array<string>|null */
        return $this->types;
    }

    public function getEvolvesFrom(): ?string
    {
        return $this->evolvesFrom;
    }

    public function setEvolvesFrom(?string $evolvesFrom): self
    {
        $this->evolvesFrom = $evolvesFrom;

        return $this;
    }

    /**
     * @return array<string>|null
     */
    public function getEvolvesTo(): ?array
    {
        /* @var array<string>|null */
        return $this->evolvesTo;
    }

    /**
     * @param array<string>|null $evolvesTo
     */
    public function setEvolvesTo(?array $evolvesTo): self
    {
        $this->evolvesTo = $evolvesTo;

        return $this;
    }

    /**
     * @return array<string>|null
     */
    public function getRules(): ?array
    {
        /* @var array<string>|null */
        return $this->rules;
    }

    /**
     * @param array<string>|null $rules
     */
    public function setRules(?array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getAncientTrait(): ?array
    {
        return $this->ancientTrait;
    }

    /**
     * @param array<mixed>|null $ancientTrait
     */
    public function setAncientTrait(?array $ancientTrait): self
    {
        $this->ancientTrait = $ancientTrait;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getAbilities(): ?array
    {
        return $this->abilities;
    }

    /**
     * @param array<mixed>|null $abilities
     */
    public function setAbilities(?array $abilities): self
    {
        $this->abilities = $abilities;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getAttacks(): ?array
    {
        return $this->attacks;
    }

    /**
     * @param array<mixed>|null $attacks
     */
    public function setAttacks(?array $attacks): self
    {
        $this->attacks = $attacks;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getWeaknesses(): ?array
    {
        return $this->weaknesses;
    }

    /**
     * @param array<mixed>|null $weaknesses
     */
    public function setWeaknesses(?array $weaknesses): self
    {
        $this->weaknesses = $weaknesses;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getResistances(): ?array
    {
        return $this->resistances;
    }

    /**
     * @param array<mixed>|null $resistances
     */
    public function setResistances(?array $resistances): self
    {
        $this->resistances = $resistances;

        return $this;
    }

    /**
     * @return array<string>|null
     */
    public function getRetreatCost(): ?array
    {
        /* @var array<string>|null */
        return $this->retreatCost;
    }

    /**
     * @param array<string>|null $retreatCost
     */
    public function setRetreatCost(?array $retreatCost): self
    {
        $this->retreatCost = $retreatCost;

        return $this;
    }

    public function getConvertedRetreatCost(): ?int
    {
        return $this->convertedRetreatCost;
    }

    public function setConvertedRetreatCost(?int $convertedRetreatCost): self
    {
        $this->convertedRetreatCost = $convertedRetreatCost;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getArtist(): ?string
    {
        return $this->artist;
    }

    public function setArtist(?string $artist): self
    {
        $this->artist = $artist;

        return $this;
    }

    public function getRarity(): ?string
    {
        return $this->rarity;
    }

    public function setRarity(?string $rarity): self
    {
        $this->rarity = $rarity;

        return $this;
    }

    public function getFlavorText(): ?string
    {
        return $this->flavorText;
    }

    public function setFlavorText(?string $flavorText): self
    {
        $this->flavorText = $flavorText;

        return $this;
    }

    /**
     * @return array<int>|null
     */
    public function getNationalPokedexNumbers(): ?array
    {
        /* @var array<int>|null */
        return $this->nationalPokedexNumbers;
    }

    /**
     * @param array<int>|null $nationalPokedexNumbers
     */
    public function setNationalPokedexNumbers(?array $nationalPokedexNumbers): self
    {
        $this->nationalPokedexNumbers = $nationalPokedexNumbers;

        return $this;
    }

    /**
     * @return array<string, string>|null
     */
    public function getLegalities(): ?array
    {
        /* @var array<string, string>|null */
        return $this->legalities;
    }

    /**
     * @param array<string, string>|null $legalities
     */
    public function setLegalities(?array $legalities): self
    {
        $this->legalities = $legalities;

        return $this;
    }

    public function getRegulationMark(): ?string
    {
        return $this->regulationMark;
    }

    public function setRegulationMark(?string $regulationMark): self
    {
        $this->regulationMark = $regulationMark;

        return $this;
    }

    /**
     * @return array<string, string>|null
     */
    public function getImages(): ?array
    {
        /* @var array<string, string>|null */
        return $this->images;
    }

    /**
     * @param array<string, string>|null $images
     */
    public function setImages(?array $images): self
    {
        $this->images = $images;

        return $this;
    }

    public function getSmallImage(): ?string
    {
        return $this->images['small'] ?? null;
    }

    public function getUploadedImage(): mixed
    {
        return $this->uploadedImage;
    }

    public function setUploadedImage(mixed $uploadedImage): self
    {
        $this->uploadedImage = $uploadedImage;

        return $this;
    }

    public function getSet(): Set
    {
        return $this->set;
    }

    public function setSet(Set $set): self
    {
        $this->set = $set;

        return $this;
    }

    /** @return Collection<int, Booster> */
    public function getBoosters(): Collection
    {
        return $this->boosters;
    }

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
