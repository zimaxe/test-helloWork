<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('paginator')]
class Paginator
{
    public function __construct(
        public int $currentPage = 1,
        public int $totalPages = 1,
        public int $totalItems = 1,
        public int $itemsPerPage = 25,
        public int $maxPages = 500
    ) {
    }

    public function paginate(): array
    {
        $calculatedTotalPages = (int) ceil($this->totalItems / $this->itemsPerPage);

        if ($calculatedTotalPages > $this->maxPages) {
            $calculatedTotalPages = $this->maxPages;
        }

        $this->totalPages = $calculatedTotalPages;

        $pagination = ['links' => []];

        for ($page = max(1, $this->currentPage - 2); $page <= min($this->currentPage + 2, $this->totalPages); ++$page) {
            $pagination['links'][$page] = $page;
        }

        return $pagination;
    }
}
