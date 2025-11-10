<?php

declare(strict_types=1);

namespace App\Service;

class PaginationService
{
    /**
     * @return array{current_page: int, per_page: int, total_items: int, total_pages: int|float}
     */
    public function paginate(int $totalItems, int $limit, int $page): array
    {
        $totalPages = ceil($totalItems / $limit);

        return [
            'current_page' => $page,
            'per_page' => $limit,
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
        ];
    }
}
