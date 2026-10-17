<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Format de pagination compact pour Inertia : éléments + pages numérotées
 * (avec « … ») et liens précédent/suivant, en conservant la query string.
 */
class Paginated
{
    public static function from(LengthAwarePaginator $paginator, callable $map): array
    {
        $paginator->withQueryString();
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();

        $pages = collect(range(1, max(1, $last)))
            ->filter(fn (int $page) => $page === 1 || $page === $last || abs($page - $current) <= 1)
            ->values();

        $items = [];
        $previous = null;

        foreach ($pages as $page) {
            if ($previous !== null && $page - $previous > 1) {
                $items[] = ['type' => 'ellipsis'];
            }

            $items[] = ['type' => 'page', 'page' => $page, 'url' => $paginator->url($page), 'active' => $page === $current];
            $previous = $page;
        }

        return [
            'data' => collect($paginator->items())->map($map)->values()->all(),
            'meta' => [
                'current_page' => $current,
                'last_page' => $last,
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'per_page' => $paginator->perPage(),
            ],
            'links' => [
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
                'pages' => $last > 1 ? $items : [],
            ],
        ];
    }
}
