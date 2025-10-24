@extends('layouts.app')

@section('title','Admin Login')

@section('content')
    <div class="container" style="max-width:420px">
        <div class="bg-white rounded-3 shadow p-3">
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
                           class="form-control form-control-sm @error('password') is-invalid @enderror"
                           required autofocus
                    >
                </div>
                <button class="btn btn-sm btn-primary w-100">Enter</button>
            </form>
        </div>
    </div>
@endsection
