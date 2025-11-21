<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PriceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PriceRepository::class)]
#[ORM\Table(name: 'prices')]
#[ORM\Index(name: 'idx_price_card', columns: ['card_id'])]
#[ORM\Index(name: 'idx_price_updated', columns: ['last_updated'])]
class Price
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['price:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Groups(['price:read'])]
    private string $cardId;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['price:read'])]
    private ?string $marketPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['price:read'])]
    private ?string $lowPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['price:read'])]
    private ?string $highPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['price:read'])]
    private ?string $averagePrice = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['price:read'])]
    private \DateTimeImmutable $lastUpdated;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->lastUpdated = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCardId(): string
    {
        return $this->cardId;
    }

    public function setCardId(string $cardId): self
    {
        $this->cardId = $cardId;

        return $this;
    }

    public function getMarketPrice(): ?float
    {
        return null !== $this->marketPrice ? (float) $this->marketPrice : null;
    }

    public function setMarketPrice(?float $marketPrice): self
    {
        $this->marketPrice = null !== $marketPrice ? (string) $marketPrice : null;

        return $this;
    }

    public function getLowPrice(): ?float
    {
        return null !== $this->lowPrice ? (float) $this->lowPrice : null;
    }

    public function setLowPrice(?float $lowPrice): self
    {
        $this->lowPrice = null !== $lowPrice ? (string) $lowPrice : null;

        return $this;
    }

    public function getHighPrice(): ?float
    {
        return null !== $this->highPrice ? (float) $this->highPrice : null;
    }

    public function setHighPrice(?float $highPrice): self
    {
        $this->highPrice = null !== $highPrice ? (string) $highPrice : null;

        return $this;
    }

    public function getAveragePrice(): ?float
    {
        return null !== $this->averagePrice ? (float) $this->averagePrice : null;
    }

    public function setAveragePrice(?float $averagePrice): self
    {
        $this->averagePrice = null !== $averagePrice ? (string) $averagePrice : null;

        return $this;
    }

    public function getLastUpdated(): \DateTimeImmutable
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(\DateTimeImmutable $lastUpdated): self
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
