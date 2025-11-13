@extends('layouts.app')

@section('title', 'New Registration')

@section('content')
    @php
        $dealerOptions = $dealerOptions ?? collect();
        $initialDateText = $dealer?->know_your_car_date?->format('l, F jS, Y');
        $defaultDateMessage = 'Select a dealership to view the event date.';
    @endphp

    @include('partials.alert')

    <div class="card shadow-sm set-max-width">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between gap-3 mb-1">
                <div class="rsvp-form-logo">
                    <img src="{{ $logo }}"
                         id="rsvpFormLogoImage"
                         alt="{{ $dealer?->name ?? 'Dealership' }} logo"
                         loading="lazy"
                         referrerpolicy="no-referrer"/>
                </div>
                <div class="d-flex flex-column align-items-end">
                    <h3 class="m-0">Know Your Car Night</h3>
                    <div id="kycnDateDisplay"
                         class="text-end fw-bold text-secondary mb-3"
                         style="letter-spacing: 0.05em; font-size: 0.95rem;"
                         data-default-text="{{ $defaultDateMessage }}">
                        {{ $initialDateText ?? $defaultDateMessage }}
                    </div>
                </div>
            </div>

            <form id="kycnForm" method="post" action="{{ route('public.form.store', request()->query()) }}">
                @csrf

                <div class="visually-hidden" aria-hidden="true">
                    <label>Leave this field empty</label>
                    <input type="text" name="website">
                </div>

                <input type="date"
                       class="d-none"
                       name="know_your_car_date"
                       id="knowYourCarDateInput"
                       value="{{ optional($dealer?->know_your_car_date)->toDateString() }}">

                <div class="mb-3">
                    <label class="fs-md">Dealership Name</label>
                    @if($dealer)
                        <p class="fw-bold mb-1">{{ $dealer->name }}</p>
                        <input type="hidden" name="dealership_name" value="{{ $dealer->name }}">
                    @elseif($dealerOptions->isNotEmpty())
                        <select name="dealership_name"
                                id="dealership_select"
                                class="form-select form-select-sm @error('dealership_name') is-invalid @enderror"
                                required>
                            <option value="">Select a dealership...</option>
                            @foreach($dealerOptions as $d)
                                @php
                                    $dLogo = $d->logo_url ?? config('brand.logos.bumper');
                                @endphp
                                <option value="{{ $d->name }}"
                                        data-date="{{ optional($d->know_your_car_date)->toDateString() }}"
                                        data-logo="{{ $dLogo }}"
                                        {{ old('dealership_name') === $d->name ? 'selected' : '' }}>
                                    {{ $d->name }} — {{ optional($d->know_your_car_date)->format('M j, Y') }}
                                </option>
                            @endforeach
                        </select>
                        @error('dealership_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @else
                        <input name="dealership_name"
                               class="form-control form-control-sm"
                               placeholder="e.g., Thornhill Hyundai"
                               value="{{ old('dealership_name') }}"
                               autocomplete="organization"
                               required
                        >
                        <small class="text-secondary">
                            No upcoming KYCN events available; please enter your dealership.
                        </small>
                    @endif
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="fs-md" for="first_name">First Name <span class="text-danger">*</span></label>
                        <input id="first_name" name="first_name"
                               class="form-control form-control-sm @error('first_name') is-invalid @enderror"
                               required value="{{ old('first_name') }}" autocomplete="given-name" maxlength="60">
                        @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="fs-md" for="last_name">Last Name <span class="text-danger">*</span></label>
                        <input id="last_name" name="last_name"
                               class="form-control form-control-sm @error('last_name') is-invalid @enderror"
                               required value="{{ old('last_name') }}" autocomplete="family-name" maxlength="60">
                        @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="fs-md">Number of Attendees (Including Yourself) <span
                                    class="text-danger">*</span></label><br>
                        <div class="btn-group" role="group" aria-label="Number of attendees">
                            <input type="radio" class="btn-check" name="number_of_attendees" id="a1" value="1"
                                   {{ old('number_of_attendees', '1') == '1' ? 'checked' : '' }} required>
                            <label class="btn btn-sm btn-outline-primary" for="a1">1</label>

                            <input type="radio" class="btn-check" name="number_of_attendees" id="a2" value="2"
                                   {{ old('number_of_attendees') == '2' ? 'checked' : '' }} required>
                            <label class="btn btn-sm btn-outline-primary" for="a2">2</label>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="fs-md" for="email">Email <span class="text-danger">*</span></label>
                        <input id="email" name="email" type="email"
                               class="form-control form-control-sm @error('email') is-invalid @enderror"
                               required value="{{ old('email') }}" autocomplete="email" inputmode="email"
                               maxlength="120">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="fs-md" for="phone">Phone <span class="text-danger">*</span></label>
                        <input id="phone" name="phone"
                               class="form-control form-control-sm @error('phone') is-invalid @enderror"
                               required value="{{ old('phone') }}" autocomplete="tel"
                               inputmode="tel" pattern="[0-9\s\-\(\)\+]{7,}" maxlength="20"
                               placeholder="e.g., 226-555-0199">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="fs-md" for="vehicle_purchased">Vehicle Purchased</label>
                        <input id="vehicle_purchased"
                               name="vehicle_purchased"
                               type="date"
                               class="form-control form-control-sm"
                               value="{{ old('vehicle_purchased') }}"
                               max="{{ now()->toDateString() }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button class="btn btn-sm btn-primary px-4 py-2 fw-bold text-uppercase" type="submit" title="RSVP">
                        RSVP
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if (session('success') && !$errors->any())
        <script>
            (function () {
                const f = document.getElementById('kycnForm');
                if (f) {
                    f.reset();
                    f.querySelector('[name="first_name"]')?.focus();
                }
            })();
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('dealership_select');

            if (!select) {
                return;
            }

            const logoEl = document.getElementById('rsvpFormLogoImage');
            const dateDisplay = document.getElementById('kycnDateDisplay');
            const eventDate = document.getElementById('knowYourCarDateInput');

            const defaultText = dateDisplay.dataset.defaultText || dateDisplay.textContent || '';
            const locale = document.documentElement.lang || 'en-US';

            const pretty = (iso) => {
                if (!iso) {
                    return defaultText;
                }

                const d = new Date(iso + 'T12:00:00');
                const day = d.getDate();
                const ord = (n) => {
                    const j = n % 10;
                    const k = n % 100;

                    return j === 1 && k !== 11 ? 'st' : j === 2 && k !== 12 ? 'nd' : j === 3 && k !== 13 ? 'rd' : 'th';
                };
                const weekday = d.toLocaleDateString(locale, { weekday: 'long' });
                const month = d.toLocaleDateString(locale, { month: 'long' });

                return `${weekday}, ${month} ${day}${ord(day)}, ${d.getFullYear()}`;
            };

            function updateDateFromSelection() {
                const opt = select.options[select.selectedIndex];
                const iso = opt?.dataset?.date?.trim?.() || '';

                eventDate.value = iso;
                eventDate.setAttribute('value', iso);

                if (dateDisplay) {
                    dateDisplay.textContent = pretty(iso);
                }
            }

            function updateLogoFromSelection() {
                const opt = select.options[select.selectedIndex];

                logoEl.src = opt?.dataset?.logo?.trim?.() || @json($logo);
            }

            select.addEventListener('change', () => {
                updateDateFromSelection();
                updateLogoFromSelection();
            });
            updateDateFromSelection();
            updateLogoFromSelection();
        });
    </script>
@endpush
