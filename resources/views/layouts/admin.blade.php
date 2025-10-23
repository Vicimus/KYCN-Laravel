@extends('layouts.base')

@section('title', 'Admin')

@section('content')
    <nav class="navbar navbar-light bg-light border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold"
               href="{{ route('admin.dealers.index') }}"
            >
                KYCN Admin
            </a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <a class="text-decoration-none btn btn-sm btn-primary fs-md"
                   href="{{ route('public.form') }}"
                >
                    New Registration
                </a>
                <a class="text-decoration-none btn btn-sm btn-primary fs-md"
                   href="{{ route('admin.dealers.create') }}"
                >
                    Create Dealer
                </a>
                <form method="post" action="{{ route('admin.logout') }}" class="m-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-dark fs-md">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    @yield('admin')
@endsection
