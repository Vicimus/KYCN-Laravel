@extends('layouts.admin')

@section('admin')
    <div class="container py-4" style="max-width:960px;">
        <h2 class="mb-4">Create Dealership</h2>
        <form method="post" action="{{ route('admin.dealers.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Dealership Name</label>
                <input name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Dealership Logo (URL)</label>
                <input name="dealership_logo" type="url" class="form-control" placeholder="https://...">
            </div>
            <button class="btn btn-primary">Create Dealer</button>
        </form>
    </div>
@endsection
