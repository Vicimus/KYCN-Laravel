@if (session('success') && !$errors->any())
    <div class="alert alert-success shadow-sm p-4 mb-4 text-center set-max-width">
        <div class="fs-5 fw-bold text-uppercase">Registration Received</div>
        <div class="fs-6 mt-1">Thanks! We’ll be in touch with the details shortly.</div>
    </div>
@endif

@if (session('info') && !$errors->any())
    <div class="alert alert-info shadow-sm p-4 mb-4 text-center set-max-width">
        <div class="fs-5 fw-bold text-uppercase">{{ session('info') }}</div>
        <div class="fs-6 mt-1">Please review your details or adjust if needed.</div>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger shadow-sm p-4 mb-4 text-center set-max-width">
        <div class="fs-5 fw-bold text-uppercase mb-1">Error(s)</div>
        <ul class="list-unstyled m-0">
            @foreach($errors->all() as $e)
                <li class="fs-6">
                    {{ $e }}
                    @if(!$loop->last)&nbsp;|&nbsp;@endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
