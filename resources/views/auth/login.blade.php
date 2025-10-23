@extends('layouts.base')

@section('title','Admin Login')

@section('content')
    <nav class="navbar navbar-light bg-light border-bottom">
        <div class="container-fluid">
            <div class="ms-auto">
                <a href="{{ route('public.form') }}"
                   class="text-decoration-none btn btn-sm btn-secondary fs-md"
                >
                    New Registration
                </a>
            </div>
        </div>
    </nav>
    <div class="container" style="max-width:420px">
        <div class="bg-white rounded-3 shadow p-4">
            <h1 class="h4 mb-3 text-center">KYCN Admin</h1>

            @if (session('error'))
                <div class="alert alert-danger py-2">{{ session('error') }}</div>
            @endif

            @error('password')
            <div class="alert alert-danger py-2">{{ $message }}</div>
            @enderror

            <form method="post" action="{{ route('admin.login.perform') }}">
                @csrf
                <div class="mb-3">
                    <input type="password"
                           name="password"
                           placeholder="Password"
                           class="form-control @error('password') is-invalid @enderror"
                           required autofocus>
                    @error('password')
                    <div class="invalid-feedback">Incorrect password.</div>@enderror
                </div>
                <button class="btn btn-sm btn-primary w-100">Enter</button>
            </form>
        </div>
    </div>
@endsection
