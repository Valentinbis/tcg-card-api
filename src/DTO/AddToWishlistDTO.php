<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AddToWishlistDTO
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $cardId;

    #[Assert\Range(min: 0, max: 10)]
    public ?int $priority = 0;

    #[Assert\Length(max: 500)]
    public ?string $notes = null;

    #[Assert\PositiveOrZero]
    public ?float $maxPrice = null;
}
