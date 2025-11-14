<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateCollectionDTO
{
    #[Assert\Positive]
    public ?int $quantity = null;

    #[Assert\Choice(['mint', 'near_mint', 'excellent', 'good', 'light_played', 'played', 'poor'])]
    public ?string $condition = null;

    #[Assert\PositiveOrZero]
    public ?float $purchasePrice = null;

    public ?\DateTimeImmutable $purchaseDate = null;

    #[Assert\Length(max: 500)]
    public ?string $notes = null;

    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Choice(['fr', 'jap', 'reverse'])
    ])]
    public ?array $languages = null;
}
