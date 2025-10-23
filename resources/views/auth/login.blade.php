@extends('layouts.app')

@section('title','KYCN - Login')

@section('body')
    <div class="min-vh-100 d-flex align-items-center justify-content-center"
         style="background: linear-gradient(135deg, #ff8a00, #da1b60);">
        <div class="container" style="max-width:420px">
            <div class="bg-white rounded-3 shadow p-4 position-relative">
                <h1 class="h4 mb-3 text-center">KYCN Admin</h1>

                @error('password')
                <div class="alert alert-danger py-2">{{ $message }}</div>
                @enderror

                <form method="post" action="{{ route('login.perform') }}">
                    @csrf
                    <div class="mb-2">
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               name="password" placeholder="Password" required autofocus>
                        @error('password')
                        <div class="invalid-feedback">Incorrect password.</div>
                        @enderror
                    </div>
                    <button class="btn btn-primary w-100">Enter</button>
                </form>
            </div>
        </div>
    </div>
@endsection
