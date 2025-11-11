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
    <div class="d-flex flex-column bg-white rounded-2 shadow-sm w-100 set-max-width">
        <div class="p-3">
            <div class="fw-bold">{{ $title }}</div>
        </div>

        <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="m-0 px-3 pb-3">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="mb-3">
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

            @if($isEdit)
                <div class="mb-3">
                    <span class="fs-md d-block mb-1">Current Logo</span>
                    @if($dealer->dealership_logo_url)
                        <img src="{{ $dealer->dealership_logo_url }}"
                             alt="{{ $dealer->name }} Logo"
                             class="dealer-form-logo"
                        />
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" value="1" id="remove_logo" name="remove_logo">
                            <label class="form-check-label fs-md" for="remove_logo">Remove current logo</label>
                        </div>
                    @else
                        <span class="text-secondary">No logo</span>
                    @endif
                </div>
            @endif

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
                <a href="{{ route('admin.dealers.index') }}"
                   class="btn btn-sm btn-secondary"
                   title="Back to Dealers"
                >
                    <i class="fas fa-chevron-left"></i>
                </a>

                <button class="btn btn-sm btn-primary"
                        title="{{ $isEdit ? 'Save Changes' : 'Create Dealership' }}"
                >
                    <i class="fas fa-floppy-disk"></i>
                </button>
            </div>
        </form>
    </div>
@endsection
