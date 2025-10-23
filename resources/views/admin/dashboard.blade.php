@extends('layouts.app')
@section('title','KYCN Dashboard')

@section('body')
    <div class="d-flex flex-grow-1 flex-row w-100">
        <aside class="sidebar d-flex flex-column bg-dark text-white border-end">
            <div class="p-3 border-bottom border-secondary text-center fw-semibold">KYCN Dashboard</div>
            <div class="p-3 d-flex flex-column gap-3">
                <div>
                    <label class="form-label">Start Date</label>
                    <input class="form-control form-control-sm" type="date" name="start" value="{{ $start }}">
                </div>
                <div>
                    <label class="form-label">End Date</label>
                    <input class="form-control form-control-sm" type="date" name="end" value="{{ $end }}">
                </div>
                <div>
                    <label class="form-label">Dealer</label>
                    <select class="form-select form-select-sm" name="dealer">
                        <option value="">All dealers</option>
                        @foreach($byDealer as $d)
                            <option value="{{ $d->code }}" @selected($d->code === $dealerCode)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex justify-content-end">
                    <div class="btn-group">
                        <button id="submitBtn" class="btn btn-sm btn-primary" title="Apply Filter">
                            <i class="fas fa-filter"></i>
                        </button>
                        <button id="clearBtn" class="btn btn-sm btn-outline-secondary" title="Reset">
                            <i class="fas fa-rotate-left"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="mt-auto p-3 border-top border-secondary text-center">
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-right-from-bracket me-2"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="d-flex flex-grow-1 main-content-wrapper">
            <div class="main-content d-flex w-100">
                <div class="container">
                    <div class="d-flex gap-2 mb-3">
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#newRegModal">
                            New Registration
                        </button>
                        @if($rows->count())
                            <div class="ms-auto btn-group">
                                <a class="btn btn-sm btn-primary"
                                   href="{{ route('export.csv', ['dealer' => $dealerCode, 'start' => $start, 'end' => $end]) }}"
                                >
                                    <i class="fas fa-file-csv"></i>
                                </a>
                                <a class="btn btn-sm btn-primary"
                                   href="{{ route('ics.feed', ['dealer' => $dealerCode]) }}"
                                >
                                    <i class="fas fa-calendar-plus"></i>
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-4 col-xl-3">
                            <h5>Submissions by Dealer</h5>
                            <ul class="list-group shadow-sm">
                                @foreach($byDealer as $d)
                                    @php $isActive = $dealerCode === $d->code; @endphp
                                    <li class="list-group-item d-flex flex-row gap-1 p-0 {{ $isActive ? 'active' : '' }}">
                                        <a class="d-flex justify-content-between align-items-center py-2 px-3 text-decoration-none"
                                           href="{{ route('admin.index', ['dealer' => $d->code, 'start' => $start, 'end' => $end]) }}">
                                            <span class="text-truncate {{ $isActive ? 'text-white' : '' }}">{{ $d->name }}</span>
                                            <span class="badge {{ $isActive ? 'text-bg-light' : 'text-bg-primary' }}">{{ (int) ($d->cnt ?? 0) }}</span>
                                        </a>
                                        @if ($isActive && !empty($rows))
                                            <a href="{{ route('dealers.edit', ['dealer' => $d->id]) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                            >
                                                <i class="fas fa-pencil"></i>
                                            </a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="col-lg-8 col-xl-9">
                            @if(!$rows->count())
                                <div class="alert alert-info">No submissions for this range.</div>
                            @else
                                <div class="border rounded-2 shadow-sm table-responsive">
                                    <table class="table m-0 text-nowrap">
                                        <thead>
                                        <tr>
                                            <th>When</th>
                                            <th>Dealer</th>
                                            <th>Name</th>
                                            <th>Guests</th>
                                            <th>Appt?</th>
                                            <th>Notes</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($rows as $r)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($r->created_at)->format('M jS, Y • g:ia') }}</td>
                                                <td>{{ $r->dealer->name }}</td>
                                                <td>{{ $r->full_name }}</td>
                                                <td>{{ (int)$r->guest_count }}</td>
                                                <td>{{ $r->wants_appointment ? 'Yes' : 'No' }}</td>
                                                <td>
                                                    @php
                                                        $lines = preg_split('/\r\n|\n|\r|\s*\|\s*/', (string)($r->notes ?? ''));
                                                        $lines = array_values(array_filter(array_map('trim', $lines), fn($l)=> $l !== '' && stripos($l,'Submission ID:') !== 0));
                                                    @endphp
                                                    {!! implode('<br>', array_map('e', $lines)) !!}
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal with embedded form --}}
    <div class="modal fade" id="newRegModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New KYCN Registration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="kycnFormFrame"
                            src="{{ route('submissions.create', ['embed' => 1, 'fresh' => 1, 't' => time()]) }}"
                            style="border:0;width:100%;height:100vh">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function initSubmit(keys = [], {autoSubmitNames = [], swapPairs = []} = {}) {
                const getEl = n => document.querySelector(`[name="${n}"]`);
                const submitBtn = document.getElementById('submitBtn');
                const clearBtn = document.getElementById('clearBtn');

                const readVal = (name) => {
                    const el = getEl(name);
                    if (!el) return '';
                    if (el.type === 'checkbox') return el.checked ? (el.value || '1') : '';
                    if (el.tagName === 'SELECT' && el.multiple)
                        return Array.from(el.selectedOptions).map(o => o.value).filter(Boolean).join(',');
                    return (el.value ?? '').trim();
                };

                function navigate(clear = false) {
                    const url = new URL(window.location.href);
                    const sp = url.searchParams;
                    sp.delete('page');

                    if (clear) {
                        keys.forEach(k => sp.delete(k));
                    } else {
                        keys.forEach(k => {
                            const v = readVal(k);
                            if (v !== '') sp.set(k, v); else sp.delete(k);
                        });
                        swapPairs.forEach(([a, b]) => {
                            const va = sp.get(a), vb = sp.get(b);
                            if (va && vb && va > vb) {
                                sp.set(a, vb);
                                sp.set(b, va);
                            }
                        });
                    }
                    url.search = sp.toString();
                    location.assign(url.toString());
                }

                submitBtn?.addEventListener('click', e => {
                    e.preventDefault();
                    navigate(false);
                });
                clearBtn?.addEventListener('click', e => {
                    e.preventDefault();
                    navigate(true);
                });

                autoSubmitNames.forEach(n => getEl(n)?.addEventListener('change', () => submitBtn?.click()));
            }

            initSubmit(['start', 'end', 'dealer'], {
                autoSubmitNames: ['start', 'end', 'dealer'],
                swapPairs: [['start', 'end']]
            });

            window.addEventListener('message', (ev) => {
                if (typeof ev.data === 'string' && ev.data === 'kycn:submitted') {
                    const modalEl = document.getElementById('newRegModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal && modal.hide();
                    location.reload();
                } else if (ev.data && ev.data.type === 'kycn:frameHeight') {
                    const f = document.getElementById('kycnFormFrame');
                    if (f) f.style.height = Math.max(600, ev.data.h || 600) + 'px';
                }
            });

            document.getElementById('newRegModal')?.addEventListener('shown.bs.modal', () => {
                const f = document.getElementById('kycnFormFrame');
                const url = new URL(f.src, window.location.origin);
                url.searchParams.set('t', Date.now().toString());
                url.searchParams.set('fresh', '1');

                f.src = url.toString();
            });
        </script>
    @endpush
@endsection
