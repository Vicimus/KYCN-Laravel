@extends('layouts.app')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
@endphp

@section('title', 'Dealer Details')

@section('content')
    <div class="d-flex flex-column bg-white rounded-2 shadow-sm w-100 overflow-hidden">
        <div class="d-flex justify-content-between align-items-start p-3 gap-3">
            <div class="d-flex align-items-center gap-2">
                <div class="logo-thumb">
                    <img src="{{ $dealer->dealership_logo_url }}" alt="{{ $dealer->name }} Logo"/>
                </div>
                <div class="fw-bold">{{ $dealer->name }}</div>
            </div>

            @if($rows->count() > 0)
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
            @endif
        </div>

        <div class="border-top px-3 py-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="start_date" class="fs-md">Start Date</label>
                    <input type="date"
                           class="form-control form-control-sm"
                           id="start_date"
                           name="start_date"
                           value="{{ request('start_date') }}">
                </div>

                <div class="col-md-3">
                    <label for="end_date" class="fs-md">End Date</label>
                    <input type="date"
                           class="form-control form-control-sm"
                           id="end_date"
                           name="end_date"
                           value="{{ request('end_date') }}">
                </div>

                <div class="col-md-4">
                    <label for="submissionFilter" class="fs-md">Search Submissions</label>
                    <input type="search"
                           id="submissionFilter"
                           name="q"
                           class="form-control form-control-sm"
                           value="{{ request('q') }}"
                           autocomplete="off"
                           placeholder="Search name, email, notes...">
                </div>

                <div class="col-md-2 d-flex justify-content-md-end">
                    <div class="btn-group">
                        <button id="page-submit-button"
                                class="btn btn-sm btn-primary"
                                type="submit" title="Apply Filter">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a id="page-reset-button" class="btn btn-sm btn-outline-secondary" title="Clear">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($rows->count() > 0)
            <div class="table-responsive border-top">
                <table class="table kycn-table sm-table m-0 text-nowrap" id="dealerSubmissionsTable">
                    <thead>
                    <tr>
                        <th>{!! sortLink('event_date', 'Event Date', $orderParam, null, ['dealer' => $dealer]) !!}</th>
                        <th>{!! sortLink('name', 'Name', $orderParam, null, ['dealer' => $dealer]) !!}</th>
                        <th class="text-center">{!! sortLink('guest_count', 'Guests', $orderParam, null, ['dealer' => $dealer]) !!}</th>
                        <th class="text-center">{!! sortLink('appointment', 'Appointment', $orderParam, null, ['dealer' => $dealer]) !!}</th>
                        <th data-sort-key="notes">Notes</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($rows as $r)
                        <tr>
                            <td data-column="know_your_car_date"
                                data-value="{{ Carbon::parse($r->know_your_car_date)->timestamp ?? '' }}"
                            >
                                <div class="d-flex flex-column align-items-start"
                                     title="{{ $r->know_your_car_date?->toDateString() }}"
                                >
                                    <span class="fs-sm text-secondary">
                                        {{ $r->know_your_car_date?->format('l') }}
                                    </span>
                                    <strong class="fs-md">
                                        {{ $r->know_your_car_date?->format('F jS, Y') ?? '—' }}
                                    </strong>
                                </div>
                            </td>

                            <td data-column="name" data-value="{{ Str::lower($r->full_name) }}">
                                {{ $r->full_name }}
                                <div class="text-secondary fs-sm">{{ $r->email }}</div>
                            </td>

                            <td class="align-middle text-center"
                                data-column="guest_count"
                                data-value="{{ (int) $r->guest_count }}">
                                <span class="badge text-bg-primary">
                                    {{ (int) $r->guest_count }}
                                </span>
                            </td>

                            <td class="align-middle text-center"
                                data-column="appointment"
                                data-value="{{ $r->wants_appointment ? 1 : 0 }}">
                                <span class="badge {{ $r->wants_appointment ? 'text-bg-success' : 'text-bg-secondary'  }}">
                                    {{ $r->wants_appointment ? 'Yes' : 'No' }}
                                </span>
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
        initSubmit(
            ['start_date', 'end_date', 'q'],
            {
                autoSubmitNames: ['start_date', 'end_date'],
                searchSubmitNames: ['q'],
                swapPairs: ['start_date', 'end_date'],
            },
        );

        // const table = document.getElementById('dealerSubmissionsTable');
        // if (!table) {
        //     return;
        // }
        //
        // const tbody = table.querySelector('tbody');
        //
        // table.querySelectorAll('th[data-sort-key]').forEach((header) => {
        //     header.style.cursor = 'pointer';
        //     header.addEventListener('click', () => {
        //         const sortKey = header.dataset.sortKey;
        //         const sortType = header.dataset.sortType || 'string';
        //         const currentDir = header.dataset.sortDir === 'asc' ? 'desc' : 'asc';
        //         header.dataset.sortDir = currentDir;
        //
        //         const rows = Array.from(tbody.querySelectorAll('tr'));
        //
        //         rows.sort((a, b) => {
        //             const cellA = a.querySelector(`[data-column="${sortKey}"]`);
        //             const cellB = b.querySelector(`[data-column="${sortKey}"]`);
        //             const valueA = cellA?.dataset.value || '';
        //             const valueB = cellB?.dataset.value || '';
        //
        //             if (sortType === 'number') {
        //                 return currentDir === 'asc'
        //                     ? Number(valueA) - Number(valueB)
        //                     : Number(valueB) - Number(valueA);
        //             }
        //
        //             if (sortType === 'date') {
        //                 return currentDir === 'asc'
        //                     ? Number(valueA) - Number(valueB)
        //                     : Number(valueB) - Number(valueA);
        //             }
        //
        //             return currentDir === 'asc'
        //                 ? valueA.localeCompare(valueB)
        //                 : valueB.localeCompare(valueA);
        //         });
        //
        //         rows.forEach((row) => tbody.appendChild(row));
        //     });
        // });
    });
</script>
@endpush
