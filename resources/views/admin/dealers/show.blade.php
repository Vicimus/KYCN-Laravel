@extends('layouts.app')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
@endphp

@section('title', 'Dealer Details')

@section('content')
    <div class="d-flex flex-column bg-white rounded-2 shadow-sm w-100 overflow-hidden">
        <div class="d-flex justify-content-between align-items-center p-3 gap-3">
            <div class="d-flex align-items-center gap-3">
                @if($dealer->dealership_logo_url)
                    <img src="{{ $dealer->dealership_logo_url }}" class="table-dealer-logo" alt="{{ $dealer->name }} Logo"/>
                @endif
                <div class="fw-bold">{{ $dealer->name }}</div>
            </div>

            <div class="btn-group" role="group">
                <a class="btn btn-sm btn-outline-primary"
                   href="{{ route('admin.dealers.export', ['dealer' => $dealer->portal_token]) }}"
                   title="Export Excel"
                >
                    <i class="fas fa-file-excel"></i>
                </a>
                <a class="btn btn-sm btn-outline-primary"
                   href="{{ route('admin.dealers.ics', ['dealer' => $dealer->portal_token]) }}"
                   title="Add to Calendar"
                >
                    <i class="fas fa-calendar-plus"></i>
                </a>
            </div>
        </div>

        <div class="border-top px-3 py-3">
            <form method="get"
                  action="{{ route('admin.dealers.show', $dealer) }}"
                  class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="start_date" class="form-label fw-bold">Start Date</label>
                    <input type="date"
                           class="form-control form-control-sm"
                           id="start_date"
                           name="start_date"
                           value="{{ request('start_date') }}">
                </div>

                <div class="col-md-3">
                    <label for="end_date" class="form-label fw-bold">End Date</label>
                    <input type="date"
                           class="form-control form-control-sm"
                           id="end_date"
                           name="end_date"
                           value="{{ request('end_date') }}">
                </div>

                <div class="col-md-4">
                    <label for="submissionFilter" class="form-label fw-bold">Search Submissions</label>
                    <input type="search"
                           id="submissionFilter"
                           class="form-control form-control-sm"
                           placeholder="Search name, email, notes...">
                </div>

                <div class="col-md-2 d-flex justify-content-md-end">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" type="submit" title="Apply Filter">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="{{ route('admin.dealers.show', $dealer) }}"
                           class="btn btn-sm btn-outline-secondary" title="Clear">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        @if($rows->count() > 0)
            <div class="table-responsive border-top">
                <table class="table kycn-table sm-table m-0 text-nowrap" id="dealerSubmissionsTable">
                    <thead>
                    <tr>
                        <th class="cursor-pointer" data-sort-key="know_your_car_date" data-sort-type="date">Event Date</th>
                        <th class="cursor-pointer" data-sort-key="name">Name</th>
                        <th class="cursor-pointer text-center" data-sort-key="guest_count" data-sort-type="number">Guests</th>
                        <th class="cursor-pointer text-center" data-sort-key="appointment">Appt?</th>
                        <th data-sort-key="notes">Notes</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($rows as $r)
                        <tr>
                            <td data-column="know_your_car_date"
                                data-value="{{ Carbon::parse($r->know_your_car_date)->timestamp ?? '' }}">
                                <strong>
                                    {{ $r->know_your_car_date ? Carbon::parse($r->know_your_car_date)->format('M jS, Y') : '—' }}
                                </strong>
                            </td>

                            <td data-column="name" data-value="{{ Str::lower($r->full_name) }}">
                                {{ $r->full_name }}
                                <div class="text-secondary fs-sm">{{ $r->email }}</div>
                            </td>

                            <td class="text-center"
                                data-column="guest_count"
                                data-value="{{ (int) $r->guest_count }}">
                                {{ (int) $r->guest_count }}
                            </td>

                            <td class="text-center"
                                data-column="appointment"
                                data-value="{{ $r->wants_appointment ? 1 : 0 }}">
                                {{ $r->wants_appointment ? 'Yes' : 'No' }}
                            </td>

                            <td data-column="notes"
                                data-value="{{ Str::lower($r->notes ?? '') }}">
                                {!! nl2br(e((string) $r->notes)) !!}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-3 pb-3">
                @include('components.alert', ['heading' => 'No submissions yet.', 'type' => 'info'])
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const table = document.getElementById('dealerSubmissionsTable');
        const filterInput = document.getElementById('submissionFilter');
        if (!table) {
            return;
        }

        const tbody = table.querySelector('tbody');

        function filterRows() {
            const query = (filterInput?.value || '').toLowerCase();
            tbody.querySelectorAll('tr').forEach((row) => {
                if (!query) {
                    row.classList.remove('d-none');
                    return;
                }

                const text = row.textContent.toLowerCase();
                row.classList.toggle('d-none', !text.includes(query));
            });
        }

        filterInput?.addEventListener('input', filterRows);

        table.querySelectorAll('th[data-sort-key]').forEach((header) => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                const sortKey = header.dataset.sortKey;
                const sortType = header.dataset.sortType || 'string';
                const currentDir = header.dataset.sortDir === 'asc' ? 'desc' : 'asc';
                header.dataset.sortDir = currentDir;

                const rows = Array.from(tbody.querySelectorAll('tr'));

                rows.sort((a, b) => {
                    const cellA = a.querySelector(`[data-column="${sortKey}"]`);
                    const cellB = b.querySelector(`[data-column="${sortKey}"]`);
                    const valueA = cellA?.dataset.value || '';
                    const valueB = cellB?.dataset.value || '';

                    if (sortType === 'number') {
                        return currentDir === 'asc'
                            ? Number(valueA) - Number(valueB)
                            : Number(valueB) - Number(valueA);
                    }

                    if (sortType === 'date') {
                        return currentDir === 'asc'
                            ? Number(valueA) - Number(valueB)
                            : Number(valueB) - Number(valueA);
                    }

                    return currentDir === 'asc'
                        ? valueA.localeCompare(valueB)
                        : valueB.localeCompare(valueA);
                });

                rows.forEach((row) => tbody.appendChild(row));
            });
        });
    });
</script>
@endpush
