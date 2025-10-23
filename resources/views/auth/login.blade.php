@extends('layouts.base')

@section('title','Admin Login')

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center"
         style="background: linear-gradient(135deg, #ff8a00, #da1b60);">
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

                <div class="mt-3">
                    <a href="{{ route('public.form') }}"
                       class="btn btn-sm btn-outline-secondary"
                    >
                        Register
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
