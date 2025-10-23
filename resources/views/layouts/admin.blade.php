@extends('layouts.base')

@section('body-class', 'bg-white')

@section('content')
    <nav class="navbar navbar-light bg-light border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand fw-semibold" href="{{ route('admin.dealers.index') }}">KYCN Admin</a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.dealers.create') }}">Create Dealer</a>
                <form method="post" action="{{ route('admin.logout') }}" class="m-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-dark">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    @yield('admin')
@endsection
