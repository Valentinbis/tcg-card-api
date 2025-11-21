<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'boosters')]
class Booster
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 50)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $artworkFront = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $artworkBack = null;

    #[ORM\ManyToMany(targetEntity: Card::class, mappedBy: 'boosters')]
    /** @var Collection<int, Card> */
    private Collection $cards;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    // === Getters & Setters ===

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getArtworkFront(): ?string
    {
        return $this->artworkFront;
    }

    public function setArtworkFront(?string $artworkFront): self
    {
        $this->artworkFront = $artworkFront;

        return $this;
    }

    public function getArtworkBack(): ?string
    {
        return $this->artworkBack;
    }

    public function setArtworkBack(?string $artworkBack): self
    {
        $this->artworkBack = $artworkBack;

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
            $card->addBooster($this);
        }

        return $this;
    }

    public function removeCard(Card $card): self
    {
        if ($this->cards->removeElement($card)) {
            $card->removeBooster($this);
        }

        return $this;
    }
}
