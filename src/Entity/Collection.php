<?php

namespace App\Entity;

use App\Enum\CardConditionEnum;
use App\Enum\CardVariantEnum;
use App\Repository\CollectionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CollectionRepository::class)]
#[ORM\Table(name: 'collection')]
#[ORM\Index(name: 'idx_collection_user', columns: ['user_id'])]
#[ORM\Index(name: 'idx_collection_card', columns: ['card_id'])]
#[ORM\UniqueConstraint(name: 'unique_user_card_collection', columns: ['user_id', 'card_id'])]
#[ORM\HasLifecycleCallbacks]
class Collection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['collection:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Groups(['collection:read'])]
    private string $cardId;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 1])]
    #[Groups(['collection:read'])]
    private int $quantity = 1;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, enumType: CardConditionEnum::class)]
    #[Groups(['collection:read'])]
    private ?CardConditionEnum $condition = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['collection:read'])]
    private ?string $purchasePrice = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['collection:read'])]
    private ?\DateTimeImmutable $purchaseDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['collection:read'])]
    private ?string $notes = null;


    /**
     * Variante de la carte (normal, reverse, holo)
     */
    #[ORM\Column(type: Types::STRING, length: 10, options: ['default' => 'normal'], enumType: CardVariantEnum::class)]
    #[Groups(['collection:read'])]
    private CardVariantEnum $variant = CardVariantEnum::NORMAL;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['collection:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['collection:read'])]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
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

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getCondition(): ?CardConditionEnum
    {
        return $this->condition;
    }

    public function setCondition(?CardConditionEnum $condition): self
    {
        $this->condition = $condition;
        return $this;
    }

    public function getPurchasePrice(): ?float
    {
        return $this->purchasePrice !== null ? (float) $this->purchasePrice : null;
    }

    public function setPurchasePrice(?float $purchasePrice): self
    {
        $this->purchasePrice = $purchasePrice !== null ? (string) $purchasePrice : null;
        return $this;
    }

    public function getPurchaseDate(): ?\DateTimeImmutable
    {
        return $this->purchaseDate;
    }

    public function setPurchaseDate(?\DateTimeImmutable $purchaseDate): self
    {
        $this->purchaseDate = $purchaseDate;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }


    public function getVariant(): CardVariantEnum
    {
        return $this->variant;
    }

    public function setVariant(CardVariantEnum $variant): self
    {
        $this->variant = $variant;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}