@extends('layouts.app')

@section('title', 'Dealerships')

@section('content')
    <div class="d-flex flex-column bg-white rounded-2 shadow-sm w-100 overflow-hidden">
        <div class="p-3 d-flex justify-content-between flex-column flex-lg-row gap-3 w-100 align-items-start">
            <div class="d-flex flex-row align-items-end gap-2">
                <div class="d-flex align-items-end gap-2">
                    <input type="text" class="form-control form-control-sm"
                           name="q" value="{{ $q }}" placeholder="Search dealers...">
                    <div class="btn-group" role="group">
                        <button id="page-submit-button"
                                class="btn btn-sm btn-primary"
                                title="Apply Filters"
                        >
                            <i class="fas fa-filter"></i>
                        </button>
                        <button id="page-reset-button"
                                class="btn btn-sm btn-outline-secondary"
                                title="Clear"
                        >
                            <i class="fas fa-rotate-left"></i>
                        </button>
                    </div>
                </div>

                @if(!empty($q))
                    <div class="text-secondary fs-md text-end">
                        Showing {{ $dealers->total() }} result(s) for “{{ $q }}”
                    </div>
                @endif
            </div>

            <div class="btn-group" role="group">
                <a href="{{ route('admin.export.all.xlsx', request()->query()) }}"
                   class="btn btn-sm btn-outline-primary"
                   title="Export Excel (All)"
                >
                    <i class="fas fa-file-excel"></i>
                </a>
                <a href="{{ route('admin.export.all.ics', request()->query()) }}"
                   class="btn btn-sm btn-outline-primary"
                   title="Add to Calendar (All)"
                >
                    <i class="fas fa-calendar-plus"></i>
                </a>
            </div>
        </div>

        @if($dealers->count() > 0)
            <div class="table-responsive border-top">
                <table class="table kycn-table sm-table m-0 text-nowrap">
                    <thead>
                    <tr>
                        <th>Dealership</th>
                        <th>Event Date</th>
                        <th>URL</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dealers as $d)
                        @php
                            $url = url('/?d='.$d->code);
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="round-dealer-logo">
                                        <img src="{{ $d->dealership_logo_url ?? asset('images/placeholder.png') }}"
                                             alt="{{ $d->name }} logo"
                                        />
                                    </div>

                                    <a href="{{ route('admin.dealers.show', $d) }}"
                                       class="text-decoration-none fw-bold"
                                       title="View Dealer Details"
                                    >
                                        {{ $d->name }}
                                    </a>
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex flex-column align-items-start"
                                     title="{{ $d->know_your_car_date?->toDateString() }}"
                                >
                                    <span class="fs-sm text-secondary">
                                        {{ $d->know_your_car_date?->format('l') }}
                                    </span>
                                    <strong class="fs-md">
                                        {{ $d->know_your_car_date?->format('F jS, Y') ?? '—' }}
                                    </strong>
                                </div>
                            </td>
                            <td class="align-middle">
                                @if ($url)
                                    <a href="{{ $url }}"
                                       class="text-decoration-none fs-md fw-bold"
                                       title="Add more registrations for the Dealer"
                                    >
                                        {{ $url }}
                                    </a>
                                @else
                                    <span class="text-secondary fs-md">—</span>
                                @endif
                            </td>
                            <td class="text-end align-middle">
                                <div class="d-flex gap-3 justify-content-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.dealers.show', $d) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="View Dealer Details"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.dealers.edit', $d) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Edit Dealer"
                                        >
                                            <i class="fas fa-user-pen"></i>
                                        </a>
                                    </div>

                                    <div class="btn-group" role="group">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                data-copy-url="{{ $url }}"
                                                title="Copy Dealer URL"
                                        >
                                            <i class="far fa-copy"></i>
                                        </button>
                                        <a href="{{ route('admin.dealers.export', ['dealer' => $d->portal_token]) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Export Excel"
                                        >
                                            <i class="fas fa-file-excel"></i>
                                        </a>
                                        <a href="{{ route('admin.dealers.ics', ['dealer' => $d->portal_token]) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Add to Calendar"
                                        >
                                            <i class="fas fa-calendar-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-3 pb-3">
                @include('components.alert', ['heading' => 'No dealers found.'])
            </div>
        @endif

        @include('components.paginator', ['items' => $dealers])
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            initSubmit(['q'], {searchSubmitNames: ['q']});

            document.addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-copy-url]');
                if (!btn) {
                    return;
                }
                e.preventDefault();

                const {copyUrl} = btn.dataset;
                const icon = btn.querySelector('i');

                icon.classList.remove('far');
                icon.classList.add('fas');
                btn.classList.add('btn-outline-success');

                const finish = () => {
                    setTimeout(() => {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        btn.classList.remove('btn-outline-success');
                    }, 1200);
                };

                const fallback = () => {
                    const ta = document.createElement('textarea');
                    ta.value = copyUrl;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                    finish();
                };

                if (navigator.clipboard?.writeText) {
                    navigator.clipboard.writeText(copyUrl).then(finish).catch(() => fallback());
                } else {
                    fallback();
                }
            });
        });
    </script>
@endsection
