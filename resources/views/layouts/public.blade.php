@extends('layouts.base')

@section('body-class', 'bg-light')

@section('content')
    <div class="min-vh-100 d-flex align-items-start justify-content-center"
         style="background: linear-gradient(135deg, #ff8a00, #da1b60);">
        <div class="w-100" style="max-width: 980px;">
            @yield('public')
        </div>
    </div>
@endsection
