<?php

namespace App\Entity;

use App\Enums\RecurrenceEnum;
use App\Repository\RecurrenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RecurrenceRepository::class)]
#[ORM\Table(name: 'recurrence')]
class Recurrence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // We use the RecurrenceEnum to define the possible values (daily, weekly, bimonthly, quarterly, monthly, yearly)
    #[Assert\Choice(choices: [RecurrenceEnum::Daily, RecurrenceEnum::Weekly, RecurrenceEnum::Bimonthly, RecurrenceEnum::Quarterly, RecurrenceEnum::Monthly, RecurrenceEnum::Yearly])]
    #[ORM\Column(enumType: RecurrenceEnum::class)]
    #[Groups(['movements.show'])]
    private ?string $name = null;

    #[Assert\DateTime('d/m/Y')]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $startDate;

    #[Assert\DateTime('d/m/Y')]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $endDate;
    
    #[ORM\OneToMany(mappedBy: 'recurrence', targetEntity: Movement::class)]
    private Collection $movements;

    public function __construct()
    {
        $this->movements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return Collection<int, Movement>
     */
    public function getMovements(): Collection
    {
        return $this->movements;
    }

    public function addMovement(Movement $movement): static
    {
        if (!$this->movements->contains($movement)) {
            $this->movements->add($movement);
            $movement->setRecurrence($this);
        }

        return $this;
    }

    public function removeMovement(Movement $movement): static
    {
        if ($this->movements->removeElement($movement)) {
            // set the owning side to null (unless already changed)
            if ($movement->getRecurrence() === $this) {
                $movement->setRecurrence(null);
            }
        }

        return $this;
    }
}
