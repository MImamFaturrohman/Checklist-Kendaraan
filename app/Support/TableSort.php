<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TableSort
{
    /**
     * Apply user-requested sorting to an Eloquent builder with a whitelist.
     *
     * If the request contains no valid sort/dir (or scope_sort/scope_dir),
     * the $defaultOrder callback is called instead so existing behaviour
     * is preserved exactly.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<string, string|callable>  $allowed  key = data-sort attr value, value = DB column or closure(Builder,string $dir)
     * @param  callable(Builder):void  $defaultOrder
     */
    public static function apply(
        Builder $query,
        Request $request,
        array $allowed,
        callable $defaultOrder,
        ?string $scope = null
    ): void {
        $sortKey = $scope ? "{$scope}_sort" : 'sort';
        $dirKey  = $scope ? "{$scope}_dir"  : 'dir';

        $sort = (string) $request->input($sortKey, '');
        $dir  = strtolower((string) $request->input($dirKey, 'asc'));

        if ($sort === '' || ! array_key_exists($sort, $allowed) || ! in_array($dir, ['asc', 'desc'], true)) {
            $defaultOrder($query);

            return;
        }

        $column = $allowed[$sort];

        if (is_callable($column)) {
            $column($query, $dir);
        } else {
            $query->orderBy($column, $dir);
        }
    }

    /**
     * Return the active sort state from the request, or null if none / invalid.
     *
     * @param  array<string, mixed>  $allowed
     * @return array{sort: string, dir: string}|null
     */
    public static function current(Request $request, array $allowed = [], ?string $scope = null): ?array
    {
        $sortKey = $scope ? "{$scope}_sort" : 'sort';
        $dirKey  = $scope ? "{$scope}_dir"  : 'dir';

        $sort = (string) $request->input($sortKey, '');
        $dir  = strtolower((string) $request->input($dirKey, 'asc'));

        if ($sort === '' || (count($allowed) > 0 && ! array_key_exists($sort, $allowed))) {
            return null;
        }

        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'asc';
        }

        return ['sort' => $sort, 'dir' => $dir];
    }
}
