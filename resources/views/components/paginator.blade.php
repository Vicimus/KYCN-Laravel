@props([
    'items',
    'append' => null,
    'modal' => false,
    'showSummary' => true,
    'perPageOptions' => [10, 25, 50, 100],
])

@php
    use Illuminate\Support\Arr;

    $appends = is_array($append) ? $append : request()->query();
    if ($modal) {
        $appends = array_merge($appends, ['modal' => 1]);
    }

    $currentPerPage = (int) (request('per_page') ?? (method_exists($items, 'perPage') ? $items->perPage() : 50));
    $hiddenAppends = Arr::except($appends, ['page', 'per_page']);

    $hasPages = $items && method_exists($items, 'hasPages') && $items->hasPages();
    $first = method_exists($items, 'firstItem') ? (int) $items->firstItem() : null;
    $last = method_exists($items, 'lastItem') ? (int) $items->lastItem() : null;
    $total = method_exists($items, 'total') ? (int) $items->total() : null;

    $summary = ($showSummary && $first !== null && $last !== null && $total !== null)  ? 'Showing '.number_format($first).'–'.number_format($last).' of '.number_format($total) : null;
@endphp

@if ($hasPages)
    <div class="d-flex justify-content-between p-3 gap-3 bg-light flex-column flex-lg-row align-items-lg-center">
        <div class="text-secondary fs-md d-flex align-items-center gap-3">
            @if ($summary)
                <span>{{ $summary }}</span>
            @endif

            <form method="GET" class="d-flex align-items-center gap-2">
                @foreach ($hiddenAppends as $k => $v)
                    @if (is_array($v))
                        @foreach ($v as $vv)
                            <input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endif
                @endforeach

                <label for="per-page" class="fs-md text-secondary m-0">Rows per page</label>
                <select id="per-page"
                        name="per_page"
                        class="form-select form-select-sm w-auto"
                        onchange="this.form.submit()">
                    @foreach ($perPageOptions as $n)
                        <option value="{{ $n }}" @selected($currentPerPage === (int) $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="d-flex justify-content-lg-end">
            {{ $items->appends($appends)->onEachSide(1)->links() }}
        </div>
    </div>
@endif
