<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CardVariantEnum;
use App\Repository\WishlistRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: WishlistRepository::class)]
#[ORM\Table(name: 'wishlist')]
#[ORM\Index(name: 'idx_wishlist_user', columns: ['user_id'])]
#[ORM\Index(name: 'idx_wishlist_card', columns: ['card_id'])]
#[ORM\UniqueConstraint(name: 'unique_user_card', columns: ['user_id', 'card_id'])]
#[ORM\HasLifecycleCallbacks]
class Wishlist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['wishlist:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    #[Groups(['wishlist:read'])]
    private ?string $cardId = null;

    /** Variante de la carte (normal, reverse, holo) */
    #[ORM\Column(type: 'string', length: 10, options: ['default' => 'normal'], enumType: CardVariantEnum::class)]
    #[Groups(['wishlist:read'])]
    private CardVariantEnum $variant = CardVariantEnum::NORMAL;

    #[ORM\Column(type: 'integer')]
    #[Groups(['wishlist:read'])]
    private int $priority = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['wishlist:read'])]
    private ?string $notes = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['wishlist:read'])]
    private ?string $maxPrice = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['wishlist:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['wishlist:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getCardId(): ?string
    {
        return $this->cardId;
    }

    public function setCardId(string $cardId): static
    {
        $this->cardId = $cardId;
        return $this;
    }

    public function getVariant(): CardVariantEnum
    {
        return $this->variant;
    }
    public function setVariant(CardVariantEnum $variant): static
    {
        $this->variant = $variant;
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getMaxPrice(): ?string
    {
        return $this->maxPrice;
    }

    public function setMaxPrice(?string $maxPrice): static
    {
        $this->maxPrice = $maxPrice;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
