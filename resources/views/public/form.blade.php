@extends('layouts.app')

@section('title', 'New Registration')

@section('content')
    @if (session('success') && !$errors->any())
        <div class="alert alert-success shadow-sm py-4 px-4 mb-4 text-center set-max-width">
            <div class="fs-4 fw-bold text-uppercase">Registration Received</div>
            <div class="fs-6 mt-1">Thanks! We’ll be in touch with the details shortly.</div>
        </div>
    @endif

    <div class="card shadow-sm set-max-width">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-1">
                <img src="{{ $logo }}"
                     alt="{{ $dealer?->name ?? 'Dealership' }} logo"
                     style="height: 42px"
                     loading="lazy"
                     referrerpolicy="no-referrer"/>
                <h3 class="m-0">Know Your Car Night</h3>
            </div>
            @if($dealer?->know_your_car_date)
                <div class="text-end fw-bold text-uppercase text-secondary mb-3" style="letter-spacing: 0.05em; font-size: 0.95rem;">
                    {{ $dealer->know_your_car_date->format('l, F jS, Y') }}
                </div>
            @endif

            <form id="kycnForm" method="post" action="{{ route('public.form.store', request()->query()) }}">
                @csrf

                <div class="visually-hidden" aria-hidden="true">
                    <label>Leave this field empty</label>
                    <input type="text" name="website">
                </div>

                <div class="mb-3">
                    <label class="fs-md">Dealership Name</label>
                    <input name="dealership_name" class="form-control form-control-sm"
                           placeholder="e.g., Thornhill Hyundai"
                           value="{{ old('dealership_name', $dealer?->name) }}"
                           autocomplete="organization"
                    >
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="fs-md" for="first_name">First Name <span class="text-danger">*</span></label>
                        <input id="first_name" name="first_name"
                               class="form-control form-control-sm @error('first_name') is-invalid @enderror"
                               required value="{{ old('first_name') }}" autocomplete="given-name" maxlength="60">
                        @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="fs-md" for="last_name">Last Name <span class="text-danger">*</span></label>
                        <input id="last_name" name="last_name"
                               class="form-control form-control-sm @error('last_name') is-invalid @enderror"
                               required value="{{ old('last_name') }}" autocomplete="family-name" maxlength="60">
                        @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="fs-md">Number of Attendees (Including Yourself) <span
                                    class="text-danger">*</span></label><br>
                        <div class="btn-group" role="group" aria-label="Number of attendees">
                            <input type="radio" class="btn-check" name="number_of_attendees" id="a1" value="1"
                                   {{ old('number_of_attendees', '1') == '1' ? 'checked' : '' }} required>
                            <label class="btn btn-sm btn-outline-secondary" for="a1">1</label>

                            <input type="radio" class="btn-check" name="number_of_attendees" id="a2" value="2"
                                   {{ old('number_of_attendees') == '2' ? 'checked' : '' }} required>
                            <label class="btn btn-sm btn-outline-secondary" for="a2">2</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="fs-md" for="email">Email <span class="text-danger">*</span></label>
                        <input id="email" name="email" type="email"
                               class="form-control form-control-sm @error('email') is-invalid @enderror"
                               required value="{{ old('email') }}" autocomplete="email" inputmode="email"
                               maxlength="120">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="fs-md" for="phone">Phone <span class="text-danger">*</span></label>
                        <input id="phone" name="phone"
                               class="form-control form-control-sm @error('phone') is-invalid @enderror"
                               required value="{{ old('phone') }}" autocomplete="tel"
                               inputmode="tel" pattern="[0-9\s\-\(\)\+]{7,}" maxlength="20"
                               placeholder="e.g., 226-555-0199">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="fs-md" for="vehicle_purchased">Vehicle Purchased</label>
                        <input id="vehicle_purchased" name="vehicle_purchased" type="date"
                               class="form-control form-control-sm"
                               value="{{ old('vehicle_purchased') }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button class="btn btn-primary px-4 py-2 fw-bold text-uppercase" type="submit" title="RSVP">
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
