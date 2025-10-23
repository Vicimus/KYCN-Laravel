@extends('layouts.admin')

@section('admin')
    <div class="container">
        <div class="d-flex flex-column bg-white rounded-2 shadow-sm w-100">
            <div class="p-3">
                <h3 class="m-0">Edit Dealership</h3>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="post" action="{{ route('admin.dealers.update', $dealer) }}" enctype="multipart/form-data" class="m-0 px-3 pb-3">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Dealership Name</label>
                    <input name="name" class="form-control" value="{{ old('name', $dealer->name) }}" required>
                    @error('name')
                    <div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Current Logo</label>
                    @if($dealer->dealership_logo)
                        <img src="{{ $dealer->dealership_logo }}" style="height:46px" class="me-3 border p-1 bg-white">
                    @else
                        <span class="text-secondary">No logo</span>
                    @endif
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Replace Logo (upload)</label>
                        <input type="file" class="form-control" name="logo_file" accept="image/*">
                        <div class="form-text">PNG/JPG/WebP/SVG, up to 2MB.</div>
                        @error('logo_file')
                        <div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Or Logo URL</label>
                        <input name="dealership_logo" type="url" class="form-control" placeholder="https://..."
                               value="{{ old('dealership_logo') }}">
                        @error('dealership_logo')
                        <div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" value="1" id="remove_logo" name="remove_logo">
                    <label class="form-check-label" for="remove_logo">
                        Remove current logo
                    </label>
                </div>

                <div class="mt-3">
                    <button class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('admin.dealers.index') }}" class="btn btn-link">Back</a>
                </div>
            </form>
        </div>
    </div>
@endsection
