<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\CardConditionEnum;
use App\Enum\CardVariantEnum;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateCollectionDTO
{
    #[Assert\Positive]
    public ?int $quantity = null;

    #[Assert\Type(CardConditionEnum::class)]
    public ?CardConditionEnum $condition = null;

    #[Assert\PositiveOrZero]
    public ?float $purchasePrice = null;

    public ?\DateTimeImmutable $purchaseDate = null;

    #[Assert\Length(max: 500)]
    public ?string $notes = null;

    #[Assert\Type(CardVariantEnum::class)]
    public ?CardVariantEnum $variant = null;
}
