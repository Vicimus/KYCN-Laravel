@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column bg-white rounded-2 shadow-sm w-100 overflow-hidden">
        <div class="p-3 d-flex justify-content-between flex-column flex-lg-row gap-3 w-100">
            <div class="fw-bold">View Dealerships</div>

            <div class="d-flex flex-column gap-1">
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
        </div>

        @if($dealers->count() > 0)
            <div class="table-responsive border-top">
                <table class="table kycn-table sm-table m-0 text-nowrap">
                    <thead>
                    <tr>
                        <th>Dealership Name</th>
                        <th>Logo</th>
                        <th>URL</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dealers as $d)
                        @php $url = url('/?d='.$d->code); @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.dealers.show', $d) }}"
                                   class="text-decoration-none fw-bold"
                                   title="View {{ $d->name }} Details"
                                >
                                    {{ $d->name }}
                                </a>
                            </td>
                            <td>
                                <img src="{{ $d->dealership_logo ?: asset('images/placeholder.png') }}"
                                     alt="{{ $d->name }} logo"
                                     class="table-dealer-logo"
                                />
                            </td>
                            <td class="text-break">
                                <a href="{{ $url }}"
                                   class="text-decoration-none fw-bold"
                                >
                                    {{ Str::limit($url, 40) }}
                                </a>
                            </td>
                            <td>
                                <span class="fs-md text-secondary">
                                    {{ $d->created_at?->format('F jS, Y \a\t g:i A') }}
                                </span>
                            </td>
                            <td>
                                <span class="fs-md text-secondary">
                                    {{ $d->updated_at?->format('F jS, Y \a\t g:i A') }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            data-copy-url="{{ $url }}"
                                            title="Copy URL"
                                    >
                                        <i class="far fa-copy"></i>
                                    </button>
                                    <a href="{{ route('admin.dealers.edit', $d) }}"
                                       class="btn btn-sm btn-outline-secondary"
                                       title="Edit {{ $d->name }}"
                                    >
                                        <i class="fas fa-user-pen"></i>
                                    </a>
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
