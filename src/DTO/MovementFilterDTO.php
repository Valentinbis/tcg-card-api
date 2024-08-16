<?php
namespace App\DTO;

class MovementFilterDTO
{
    public function __construct(
        public int $user,
        public ?string $type,
        public ?int $categoryId,
        public ?\DateTimeImmutable $startDate,
        public ?\DateTimeImmutable $endDate
    ) {

    }
}