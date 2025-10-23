@extends('layouts.admin')

@section('content')
    <div class="container py-4" style="max-width:960px;">
        <h2 class="mb-4">Create Dealership</h2>
        <form method="post" action="{{ route('dealers.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Dealership Name</label>
                <input name="name" class="form-control" value="{{ old('name') }}" required>
                @error('name')
                <div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Logo (upload)</label>
                    <input type="file" class="form-control" name="logo_file" accept="image/*">
                    <div class="form-text">PNG/JPG/WebP/SVG, up to 2MB.</div>
                    @error('logo_file')
                    <div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Logo URL (optional)</label>
                    <input name="dealership_logo" type="url" class="form-control" placeholder="https://...">
                    @error('dealership_logo')
                    <div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">Create Dealership</button>
                <a href="{{ route('admin.dealers.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>
@endsection
