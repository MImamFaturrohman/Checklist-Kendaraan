<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\View;

class AdminTablePagination
{
    /** @var list<int> */
    public const PER_PAGE_OPTIONS = [5, 10, 25, 50, 100];

    public static function resolvePerPage(mixed $requested, int $default = 10): int
    {
        $value = is_numeric($requested) ? (int) $requested : $default;

        return in_array($value, self::PER_PAGE_OPTIONS, true) ? $value : $default;
    }

    public static function linksHtml(LengthAwarePaginator $paginator, ?string $path = null): string
    {
        if ($path !== null) {
            $paginator->withPath($path);
        }

        $paginator->appends(request()->query());

        return View::make('components.admin-pagination', [
            'paginator' => $paginator,
        ])->render();
    }

    /**
     * @return array{current_page: int, last_page: int, total: int, per_page: int, pagination_html: string}
     */
    public static function jsonMeta(LengthAwarePaginator $paginator, ?string $path = null): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'pagination_html' => self::linksHtml($paginator, $path),
        ];
    }
}
