@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 set-max-width fs-md mb-3" data-autohide="true" role="status">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show py-2 set-max-width fs-md mb-3" role="status">
        @foreach($errors->all() as $e)
            {{ $e }}<br>
        @endforeach
    </div>
@endif
