<?php

namespace App\Entity;

use App\Repository\MovementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Enums\MovementEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MovementRepository::class)]
#[ORM\Table(name: 'movement')]
#[ORM\HasLifecycleCallbacks]
class Movement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['movements.show'])]
    private ?int $id = null;

    #[ORM\Column(type: 'float', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Merci de renseigner un montant')]
    #[Assert\Type(type: 'float', message: 'Le montant doit être un nombre décimal')]
    #[Groups(['movements.create', 'movements.show'])]
    private ?float $amount = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type(type: 'string', message: 'La description doit être du texte')]
    #[Groups(['movements.create', 'movements.show'])]
    private ?string $description = null;

    // The type of the movement (expense, income)
    #[Assert\Choice(choices: [MovementEnum::Expense->value, MovementEnum::Income->value])]
    #[ORM\Column(length: 255)]
    #[Groups(['movements.create', 'movements.show'])]
    private ?string $type = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['movements.create'])]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'movements')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Recurrence::class, inversedBy: 'movements', cascade: ['persist', 'remove']) ]
    #[Groups(['movements.create', 'movements.show'])]
    private ?Recurrence $recurrence = null;

    #[ORM\ManyToOne(inversedBy: 'movements')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['movements.create', 'movements.show'])]
    private ?Category $category = null;

    /**
     * One Movement has Many Movements.
     * @var Collection<int, Movement>
     */
    #[ORM\OneToMany(targetEntity: Movement::class, mappedBy: 'parent')]
    private Collection $children;

    /** Many Movements have One Movement. */
    #[ORM\ManyToOne(targetEntity: Movement::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    #[Groups(['movements.create'])]
    private Movement|null $parent = null;

    public function __construct() {
        $this->children = new ArrayCollection();
    }
    
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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

    #[SerializedName('date')]
    #[Groups(['movements.show'])]
    public function getFormattedDate(): string
    {
        return $this->date->format('d-m-Y');
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

    public function getParent(): ?Movement
    {
        return $this->parent;
    }

    public function setParent(?Movement $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }
}
