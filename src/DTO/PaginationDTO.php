<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PaginationDTO
{    
    public function __construct(
    public ?int $page,
    public ?int $limit,
    public ?string $sort,
    public ?string $order
) {
    // Vous pouvez ajouter une logique supplémentaire ici si nécessaire
}
    // public function __construct(
    //     #[Assert\Positive()]
    //     public readonly int $page = 1
    // )
    // {
    // }

}
