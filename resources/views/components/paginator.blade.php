@props([
    'items',
    'append' => null,
    'modal' => false,
    'showSummary' => true,
])

@php
    $appends = is_array($append) ? $append : request()->query();
    if ($modal) {
        $appends = array_merge($appends, ['modal' => 1]);
    }

    $hasPages = $items && method_exists($items, 'hasPages') && $items->hasPages();
    $first = method_exists($items, 'firstItem') ? (int) $items->firstItem() : null;
    $last = method_exists($items, 'lastItem') ? (int) $items->lastItem() : null;
    $total = method_exists($items, 'total') ? (int) $items->total() : null;

    $summary = ($showSummary && $first !== null && $last !== null && $total !== null)  ? 'Showing '.number_format($first).'–'.number_format($last).' of '.number_format($total) : null;
@endphp

@if ($hasPages)
    <div class="d-flex justify-content-between p-3 gap-3 bg-light flex-column flex-lg-row align-items-lg-center">
        <div class="text-secondary fs-md">
            {{ $summary }}
        </div>
        <div class="d-flex justify-content-lg-end">
            {{ $items->appends($appends)->onEachSide(1)->links() }}
        </div>
    </div>
@endif
