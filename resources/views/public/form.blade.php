@extends('layouts.public')

@section('public')
    <div class="container py-4">
        <div class="card mx-auto shadow-sm" style="max-width: 980px;">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ $logo }}" alt="Logo" style="height:42px">
                    <h3 class="m-0">Know Your Car Night</h3>
                    <div class="ms-auto">
                        <a href="{{ route('admin.login.show') }}" class="text-decoration-none small text-muted">
                            Admin
                        </a>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $e)
                            {{ $e }}<br>
                        @endforeach
                    </div>
                @endif

                <form method="post" action="{{ route('public.form.store', request()->query()) }}">
                    @csrf

                    <!-- Dealership Name is visible; prefilled only if we know it -->
                    <div class="mb-3">
                        <label class="form-label">Dealership Name</label>
                        <input name="dealership_name"
                               class="form-control"
                               placeholder="e.g., Thornhill Hyundai"
                               value="{{ old('dealership_name', $dealer?->name) }}">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name *</label>
                            <input name="first_name" class="form-control" required value="{{ old('first_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name *</label>
                            <input name="last_name" class="form-control" required value="{{ old('last_name') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Number of Attendees (Including Yourself) *</label><br>
                            <div class="btn-group">
                                <input type="radio" class="btn-check" name="number_of_attendees" id="a1" value="1"
                                       {{ old('number_of_attendees') == 1 ? 'checked' : '' }} required>
                                <label class="btn btn-outline-secondary" for="a1">1</label>

                                <input type="radio" class="btn-check" name="number_of_attendees" id="a2" value="2"
                                       {{ old('number_of_attendees') == 2 ? 'checked' : '' }} required>
                                <label class="btn btn-outline-secondary" for="a2">2</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input name="email" type="email" class="form-control" required value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone *</label>
                            <input name="phone" class="form-control" required value="{{ old('phone') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Know Your Car Night Date</label>
                            <input name="know_your_car_date" type="date" class="form-control"
                                   value="{{ old('know_your_car_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vehicle Purchased</label>
                            <input name="vehicle_purchased" type="date" class="form-control"
                                   value="{{ old('vehicle_purchased') }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
