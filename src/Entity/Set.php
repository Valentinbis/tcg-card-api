<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sets')]
class Set
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 20)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $series = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $printedTotal = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $total = null;

    #[ORM\Column(type: 'json', nullable: true)]
    /** @var array<string, string>|null */
    private ?array $legalities = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $ptcgoCode = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $releaseDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'json', nullable: true)]
    /** @var array<string, string>|null */
    private ?array $images = null;

    #[ORM\OneToMany(mappedBy: 'set', targetEntity: Card::class, cascade: ['persist', 'remove'])]
    /** @var Collection<int, Card> */
    private Collection $cards;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    // === Getters & Setters ===

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

    public function getSeries(): ?string
    {
        return $this->series;
    }

    public function setSeries(?string $series): self
    {
        $this->series = $series;

        return $this;
    }

    public function getPrintedTotal(): ?int
    {
        return $this->printedTotal;
    }

    public function setPrintedTotal(?int $printedTotal): self
    {
        $this->printedTotal = $printedTotal;

        return $this;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): self
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return array<string, string>|null
     */
    public function getLegalities(): ?array
    {
        /** @var array<string, string>|null */
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

    public function getPtcgoCode(): ?string
    {
        return $this->ptcgoCode;
    }

    public function setPtcgoCode(?string $ptcgoCode): self
    {
        $this->ptcgoCode = $ptcgoCode;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?\DateTimeInterface $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return array<string, string>|null
     */
    public function getImages(): ?array
    {
        /** @var array<string, string>|null */
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

    /** @return Collection<int, Card> */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): self
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $card->setSet($this);
        }

        return $this;
    }
}
