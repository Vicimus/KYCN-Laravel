<?php

use Illuminate\Support\Facades\Route;

if (!function_exists('sortLink')) {
    /**
     * @param string      $column
     * @param string      $label
     * @param string|null $currentOrder
     * @param string|null $routeName
     * @param array       $routeParams
     * @param string      $paramKey
     *
     * @return string
     */
    function sortLink(string $column, string $label, ?string $currentOrder = null, ?string $routeName = null, array $routeParams = [], string $paramKey = 'order'): string
    {
        $currentOrder = $currentOrder ?? '';
        $current = ltrim($currentOrder, '-');
        $isAsc = $currentOrder === '' || $currentOrder[0] !== '-';

        $nextOrder = ($current === $column && $isAsc) ? "-$column" : $column;

        $icon = $current === $column
            ? ($isAsc ? 'fas fa-sort-up' : 'fas fa-sort-down')
            : 'fas fa-sort text-muted';

        $route = $routeName ?? Route::currentRouteName();

        $queryParams = request()->except($paramKey);
        $queryParams[$paramKey] = $nextOrder;

        $url = route($route, $routeParams + $queryParams);

        return '<a href="'.e($url).'" class="text-decoration-none text-dark">'
            .'<span class="me-1">'.e($label).'</span>'
            .'<i class="'.$icon.'"></i>'
            .'</a>';
    }
}
