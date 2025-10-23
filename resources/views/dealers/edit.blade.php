@extends('layouts.app')

@section('title', 'Edit Dealer')

@section('body')
    <div class="container py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="m-0">
                <i class="fas fa-warehouse me-2 text-secondary"></i>
                Edit Dealer
            </h4>
            <a href="{{ route('admin.index', ['dealer' => $dealer->code]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('dealers.update', $dealer) }}" method="POST" class="card shadow-sm border-0">
            @csrf
            @method('PATCH')

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-semibold">Dealer Name</label>
                        <input type="text" id="name" name="name" class="form-control form-control-sm"
                               value="{{ old('name', $dealer->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="code" class="form-label fw-semibold">Dealer Code</label>
                        <input type="text" id="code" name="code" class="form-control form-control-sm"
                               value="{{ old('code', $dealer->code) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="dealership_url" class="form-label fw-semibold">Dealership URL</label>
                        <input type="text" id="dealership_url" name="dealership_url"
                               class="form-control form-control-sm"
                               value="{{ old('dealership_url', $dealer->dealership_url) }}"
                               placeholder="e.g., thornhillhyundai">
                    </div>

                    <div class="col-md-6">
                        <label for="dealership_logo" class="form-label fw-semibold">Logo URL</label>
                        <input type="url" id="dealership_logo" name="dealership_logo"
                               class="form-control form-control-sm"
                               value="{{ old('dealership_logo', $dealer->dealership_logo) }}"
                               placeholder="https://example.com/logo.png"
                        >
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light text-end">
                <div class="btn-group">
                    <a href="{{ route('admin.index', ['dealer' => $dealer->code]) }}"
                       class="btn btn-sm btn-outline-secondary"
                    >
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
