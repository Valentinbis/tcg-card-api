<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\CardConditionEnum;
use App\Enum\CardVariantEnum;
use Symfony\Component\Validator\Constraints as Assert;

class AddToCollectionDTO
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $cardId;

    #[Assert\Positive]
    public ?int $quantity = 1;

    #[Assert\Type(CardConditionEnum::class)]
    public ?CardConditionEnum $condition = null;

    #[Assert\PositiveOrZero]
    public ?float $purchasePrice = null;

    public ?\DateTimeImmutable $purchaseDate = null;

    #[Assert\Length(max: 500)]
    public ?string $notes = null;

    #[Assert\Type(CardVariantEnum::class)]
    public ?CardVariantEnum $variant = CardVariantEnum::NORMAL;
}
