@extends('layouts.app')

@php
    use App\Models\Dealer;

    /** @var Dealer $dealer */
    $isEdit = ($mode ?? null) === 'edit';
    $title = $isEdit ? 'Edit Dealership' : 'Create Dealership';
    $action = $isEdit ? route('admin.dealers.update', $dealer) : route('admin.dealers.store');
@endphp

@section('title', $title)

@section('content')
    <div class="d-flex flex-column bg-white rounded-2 shadow-sm w-100">
        <div class="p-3">
            <div class="fw-bold">{{ $title }}</div>
        </div>

        <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="m-0 px-3 pb-3">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="row g-3 mb-3">
                @if($isEdit)
                    <div class="col-md-2">
                        <div class="d-flex flex-column">
                            <span class="fs-md mb-1">Current Logo</span>
                            @if($dealer->dealership_logo_url)
                                <div class="logo-thumb">
                                    <img src="{{ $dealer->dealership_logo_url }}" alt="{{ $dealer->name }} Logo"/>
                                </div>
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" value="1" id="remove_logo" name="remove_logo">
                                    <label class="form-check-label fs-md" for="remove_logo">Remove current logo</label>
                                </div>
                            @else
                                <span class="badge text-bg-secondary fs-md">No Logo</span>
                            @endif
                        </div>
                    </div>
                @endif
                <div class="col-md-{{ $isEdit ? '10' : '12' }}">
                    <label for="dealership_name" class="fs-md">Dealership Name</label>
                    <input name="name"
                           id="dealership_name"
                           class="form-control form-control-sm"
                           value="{{ old('name', $isEdit ? $dealer->name : '') }}"
                           autocomplete="off"
                           required>
                    @error('name')
                    <div class="text-danger fs-md">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="logo_upload_file" class="fs-md">Logo (upload)</label>
                    <input type="file"
                           class="form-control form-control-sm"
                           id="logo_upload_file"
                           name="logo_file"
                           accept="image/*">
                    <div class="fs-sm text-secondary mt-1">PNG/JPG/WebP/SVG, up to 2MB.</div>
                    @error('logo_file')
                    <div class="text-danger fs-md">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="logo_upload_src" class="fs-md">Logo URL (optional)</label>
                    <input name="dealership_logo"
                           type="url"
                           id="logo_upload_src"
                           class="form-control form-control-sm"
                           placeholder="https://..."
                           value="{{ old('dealership_logo', $dealer->dealership_logo_url) }}">
                    @error('dealership_logo')
                    <div class="text-danger fs-md">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="kyc_date" class="fs-md">Know Your Car Night Date</label>
                    <input type="date"
                           class="form-control form-control-sm @error('know_your_car_date') is-invalid @enderror"
                           id="kyc_date"
                           name="know_your_car_date"
                           value="{{ old('know_your_car_date', optional($dealer->know_your_car_date)->format('Y-m-d')) }}">
                    @error('know_your_car_date')
                    <div class="text-danger fs-md">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('admin.dealers.index') }}" class="btn btn-sm btn-secondary">
                    Back to Dealers
                </a>

                <button class="btn btn-sm btn-primary">{{ $isEdit ? 'Save Changes' : 'Create Dealership' }}</button>
            </div>
        </form>
    </div>
@endsection
