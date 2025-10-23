@extends('layouts.base')

@section('title', 'New Register')

@section('content')
    <nav class="navbar navbar-light bg-light border-bottom">
        <div class="container-fluid">
            <div class="ms-auto">
                <a href="{{ route('admin.login.show') }}"
                   class="text-decoration-none btn btn-sm btn-secondary fs-md"
                >
                    Admin
                </a>
            </div>
        </div>
    </nav>
    @yield('public')
@endsection
