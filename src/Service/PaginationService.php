<?php
namespace App\Service;

class PaginationService
{
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