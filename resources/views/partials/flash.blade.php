@if (session('success'))
    <div class="fs-md text-success" data-autohide="true" role="status">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="fs-md text-danger" role="status">
        @foreach($errors->all() as $e)
            {{ $e }}
            @if(!$loop->last)&nbsp;|&nbsp;@endif
        @endforeach
    </div>
@endif
