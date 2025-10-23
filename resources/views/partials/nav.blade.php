@php
    $isAdmin = (bool) session('is_admin');
@endphp

<nav class="navbar navbar-light bg-light border-bottom">
    <div class="container-fluid">
        <a class="text-decoration-none text-dark fw-bold"
           href="{{ $isAdmin ? route('admin.dealers.index') : route('public.form') }}"
        >
            KYCN {{ $isAdmin ? 'Admin' : '' }}
        </a>

        <div class="ms-auto d-flex align-items-center gap-2">
            <a class="btn btn-sm btn-primary" href="{{ route('public.form') }}">
                New Registration
            </a>

            @if ($isAdmin)
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.dealers.create') }}">
                    Create Dealer
                </a>

                <form method="post" action="{{ route('admin.logout') }}" class="m-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-dark">Logout</button>
                </form>
            @else
                <a class="btn btn-sm btn-secondary" href="{{ route('admin.login.show') }}">
                    Admin
                </a>
            @endif
        </div>
    </div>
</nav>
