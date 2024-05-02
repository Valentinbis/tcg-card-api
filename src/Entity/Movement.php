<?php

namespace App\Entity;

use App\Repository\MovementRepository;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Enums\MovementEnum;

#[ORM\Entity(repositoryClass: MovementRepository::class)]
class Movement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $amount = null;

    // The type of the movement (expense, income)
    #[Assert\Choice(choices: [MovementEnum::Expense, MovementEnum::Income])]
    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'movements')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'movements')]
    private ?Recurrence $recurrence = null;

    #[ORM\ManyToOne(inversedBy: 'movements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\OneToOne(mappedBy: 'movement', cascade: ['persist', 'remove'])]
    private ?History $history = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable("now", new DateTimeZone('Europe/Paris'));
        $this->updatedAt = new \DateTimeImmutable("now", new DateTimeZone('Europe/Paris'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
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

    public function getRecurrence(): ?Recurrence
    {
        return $this->recurrence;
    }

    public function setRecurrence(?Recurrence $recurrence): static
    {
        $this->recurrence = $recurrence;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getHistory(): ?History
    {
        return $this->history;
    }

    public function setHistory(?History $history): static
    {
        // unset the owning side of the relation if necessary
        if ($history === null && $this->history !== null) {
            $this->history->setMovement(null);
        }

        // set the owning side of the relation if necessary
        if ($history !== null && $history->getMovement() !== $this) {
            $history->setMovement($this);
        }

        $this->history = $history;

        return $this;
    }
}