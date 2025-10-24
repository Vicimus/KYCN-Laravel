@extends('layouts.app')

@section('title','Admin Login')

@section('content')
    <div class="bg-white rounded-3 shadow p-3 m-auto" style="max-width: 420px;">
        <h1 class="h4 mb-3 text-center">KYCN Admin</h1>

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
@endsection
