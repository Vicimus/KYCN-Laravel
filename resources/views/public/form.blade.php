@extends('layouts.public')

@section('public')
    <div class="container">
        <div class="card shadow-sm m-auto" style="max-width: 900px;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                    <img src="{{ $logo }}" alt="Logo" style="height:42px">
                    <h3 class="m-0">Know Your Car Night</h3>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">
                        <span>{{ session('success') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $e)
                            {{ $e }}<br>
                        @endforeach
                    </div>
                @endif

                <form method="post" id="kycnForm" action="{{ route('public.form.store', request()->query()) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="fs-md">Dealership Name</label>
                        <input name="dealership_name"
                               class="form-control form-control-sm"
                               placeholder="e.g., Thornhill Hyundai"
                               value="{{ old('dealership_name', $dealer?->name) }}">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="fs-md">First Name <span class="text-danger">*</span></label>
                            <input name="first_name" class="form-control form-control-sm" required value="{{ old('first_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="fs-md">Last Name <span class="text-danger">*</span></label>
                            <input name="last_name" class="form-control form-control-sm" required value="{{ old('last_name') }}">
                        </div>

                        <div class="col-12">
                            <span class="fs-md">Number of Attendees (Including Yourself) <span class="text-danger">*</span></span><br>
                            <div class="btn-group">
                                <input type="radio" class="btn-check" name="number_of_attendees" id="a1" value="1"
                                       {{ old('number_of_attendees') == 1 ? 'checked' : '' }} required>
                                <label class="btn btn-sm btn-outline-secondary" for="a1">1</label>

                                <input type="radio" class="btn-check" name="number_of_attendees" id="a2" value="2"
                                       {{ old('number_of_attendees') == 2 ? 'checked' : '' }} required>
                                <label class="btn btn-sm btn-outline-secondary" for="a2">2</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="fs-md">Email <span class="text-danger">*</span></label>
                            <input name="email" type="email" class="form-control form-control-sm" required value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="fs-md">Phone <span class="text-danger">*</span></label>
                            <input name="phone" class="form-control form-control-sm" required value="{{ old('phone') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="fs-md">Know Your Car Night Date</label>
                            <input name="know_your_car_date" type="date" class="form-control form-control-sm"
                                   value="{{ old('know_your_car_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="fs-md">Vehicle Purchased</label>
                            <input name="vehicle_purchased" type="date" class="form-control form-control-sm"
                                   value="{{ old('vehicle_purchased') }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn btn-sm btn-secondary" type="reset">Reset</button>
                        <button class="btn btn-sm btn-primary" type="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        (function () {
            const justSucceeded = {{ session()->has('success') ? 'true' : 'false' }};
            if (justSucceeded) {
                const f = document.getElementById('kycnForm');
                const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
                if (!hasErrors) {
                    f.reset();
                }
                const first = f.querySelector('[name="first_name"]');
                first && first.focus();

                document.querySelector('button[type="reset"]').click();
            }
        })();
    </script>
@endsection
