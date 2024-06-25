<?php

namespace App\Entity;

use App\Repository\MovementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Enums\MovementEnum;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MovementRepository::class)]
#[ORM\Table(name: 'movement')]
#[ORM\HasLifecycleCallbacks]
class Movement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'float', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Merci de renseigner un montant')]
    #[Assert\Type(type: 'float', message: 'Le montant doit être un nombre décimal')]
    #[Groups(['movements.create', 'movements.show'])]
    private ?float $amount = null;

    #[ORM\Column(type: 'float', precision: 10, scale: 2, nullable: true)]
    #[Assert\Type(type: 'float', message: 'Le montant doit être un nombre décimal')]
    #[Groups(['movements.show'])]
    private ?float $bank = null;

    // The type of the movement (expense, income)
    #[Assert\Choice(choices: [MovementEnum::Expense->value, MovementEnum::Income->value])]
    #[ORM\Column(length: 255)]
    #[Groups(['movements.create', 'movements.show'])]
    private ?string $type = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['movements.create', 'movements.show'])]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'movements')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Recurrence::class, inversedBy: 'movements', cascade: ['persist']) ]
    #[Groups(['movements.create', 'movements.show'])]
    private ?Recurrence $recurrence = null;

    #[ORM\ManyToOne(inversedBy: 'movements')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['movements.create', 'movements.show'])]
    private ?Category $category = null;

    #[ORM\PrePersist]
    public function updateTimestamp(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getBank(): ?float
    {
        return $this->bank;
    }

    public function setBank(float $bank): static
    {
        $this->bank = $bank;

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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
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
}
