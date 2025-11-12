@php
    $isAdmin = (bool) session('is_admin');
    $onPublicForm = request()->routeIs('public.form');
    $onAdminIndex = request()->routeIs('admin.dealers.index');
    $onAdminAny = request()->routeIs('admin.*');
    $onDealerView = !$onPublicForm && $onAdminIndex;
    $onDealerCreate = request()->routeIs('admin.dealers.create');
    $onDealerEdit = request()->routeIs('admin.dealers.edit');
    $onDealerShow = request()->routeIs('admin.dealers.show');
    $showBackToList = $isAdmin && $onAdminAny && !$onAdminIndex;
@endphp

<nav class="navbar navbar-light bg-light border-bottom">
    <div class="container py-0 px-3">
        <div class="d-flex align-items-center">
            <a class="text-decoration-none text-dark fw-bold"
               href="{{ $isAdmin ? route('admin.dealers.index') : route('public.form') }}"
            >
                KYCN {{ $isAdmin ? 'Admin' : '' }}
            </a>
        </div>

        <div class="ms-auto d-flex align-items-center">
            @if(!$onPublicForm)
                <div class="btn-group pe-3 me-3 border-end rounded-0" role="group">
                    <a class="btn btn-sm btn-success" href="{{ route('public.form') }}">
                        New Register
                    </a>
                </div>
            @endif

            @if ($isAdmin)
                <div class="d-flex">
                    <div class="btn-group pe-3 me-3 border-end rounded-0" role="group">
                        @if ((!$onAdminIndex && !$showBackToList) || ($onDealerCreate || $onDealerEdit || $onDealerShow))
                            <a class="btn btn-sm btn-primary" href="{{ route('admin.dealers.index') }}">
                                Go to Dealers
                            </a>
                        @endif

                        @if($onDealerView && (!$onDealerCreate || !$onDealerEdit))
                            <a class="btn btn-sm btn-primary"
                               href="{{ route('admin.dealers.create') }}"
                            >
                                Create a Dealer
                            </a>
                        @endif
                    </div>

                    <form method="post" action="{{ route('admin.logout') }}" class="m-0">
                        @csrf
                        <button class="btn btn-sm btn-outline-dark" title="Logout">
                            <i class="fas fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            @else
                <a class="btn btn-sm btn-secondary" href="{{ route('admin.login.show') }}" title="Admin">
                    <i class="fas fa-user-shield"></i>
                </a>
            @endif
        </div>
    </div>
</nav>
