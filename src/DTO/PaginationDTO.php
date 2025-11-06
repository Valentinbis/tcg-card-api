<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PaginationDTO
{    
    public function __construct(
        #[Assert\Positive(message: "La page doit être un entier positif")]
        public ?int $page = 1,
        
        #[Assert\Positive(message: "La limite doit être un entier positif")]
        #[Assert\LessThanOrEqual(100, message: "La limite ne peut pas dépasser {{ compared_value }}")]
        public ?int $limit = 20,
        
        #[Assert\Choice(choices: ['name', 'number', 'rarity', 'created_at'], message: "Tri invalide : {{ value }}")]
        public ?string $sort = 'name',
        
        #[Assert\Choice(choices: ['asc', 'desc'], message: "Ordre invalide : {{ value }}")]
        public ?string $order = 'asc'
    ) {
    }
}
