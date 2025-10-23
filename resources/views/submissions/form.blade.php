@php
    $logoUrl = $dealer?->dealership_logo ?: 'https://vicimus.com/wp-content/uploads/2023/08/bumper.svg';
    $prefillName = old('dealership_name', $dealer->name ?? '');
@endphp

        <!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Know Your Car Night Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body class="p-0">
<div class="d-flex w-100 p-3">
    <div class="d-flex flex-column bg-white rounded-2 m-auto" style="max-width: 900px; width:100%;">
        <div class="d-flex align-items-center justify-content-between px-3 py-5 gap-5">
            <img src="{{ $logoUrl }}" alt="Dealership Logo" style="height:40px;">
            <h3 class="m-0">Know Your Car Night</h3>
        </div>

        @if (session('success'))
            <div class="alert alert-success mb-0">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger mb-0">
                @foreach ($errors->all() as $e)
                    {{ $e }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('submissions.store') }}" class="needs-validation" novalidate>
            @csrf
            <input type="hidden" name="embed" value="{{ $embed ? 1 : 0 }}">
            @if ($dealerCode)
                <input type="hidden" name="d" value="{{ $dealerCode }}">
            @endif

            <div class="d-flex flex-column rounded-2 shadow-sm">
                <div class="p-3 d-flex flex-column gap-3">
                    <div>
                        <label class="form-label">Dealership Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="dealership_name"
                               value="{{ $prefillName }}" placeholder="e.g., Thornhill Hyundai" required>
                    </div>

                    <div class="row g-3">
                        <div class="col">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="first_name"
                                   value="{{ old('first_name') }}" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="last_name"
                                   value="{{ old('last_name') }}" required>
                        </div>
                    </div>

                    <div class="d-flex flex-column align-items-start">
                        <span>Number of Attendees (Including Yourself) <span class="text-danger">*</span></span>
                        <div class="btn-group btn-toggle-group" role="group">
                            <input type="radio" class="btn-check" id="attendees1" name="number_of_attendees" value="1"
                                   @checked(old('number_of_attendees')=='1') required>
                            <label class="btn btn-sm btn-outline-secondary" for="attendees1">1</label>
                            <input type="radio" class="btn-check" id="attendees2" name="number_of_attendees" value="2"
                                   @checked(old('number_of_attendees')=='2') required>
                            <label class="btn btn-sm btn-outline-secondary" for="attendees2">2</label>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                                <input type="email" class="form-control form-control-sm" name="email"
                                       value="{{ old('email') }}" required>
                            </div>
                        </div>
                        <div class="col">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                <input type="tel" class="form-control form-control-sm" name="phone"
                                       value="{{ old('phone') }}" required>
                            </div>
                            <div class="form-text">Numbers only, please.</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col">
                            <label class="form-label">Know Your Car Night Date</label>
                            <input type="date" class="form-control form-control-sm" name="know_your_car_date"
                                   value="{{ old('know_your_car_date') }}">
                        </div>
                        <div class="col">
                            <label class="form-label">Vehicle Purchased</label>
                            <input type="date" class="form-control form-control-sm" name="vehicle_purchased"
                                   value="{{ old('vehicle_purchased') }}">
                        </div>
                    </div>
                </div>

                <div class="p-3 d-flex justify-content-end border-top">
                    <div class="btn-group">
                        <button type="reset" class="btn btn-sm btn-outline-secondary">Clear</button>
                        <button class="btn btn-sm btn-primary"><i class="fa-solid fa-paper-plane me-1"></i> Submit
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function postHeight() {
        const h = Math.max(document.documentElement.scrollHeight, document.body.scrollHeight);
        window.parent?.postMessage({type: 'kycn:frameHeight', h}, '*');
    }

    window.addEventListener('load', postHeight);
    window.addEventListener('resize', postHeight);
    new MutationObserver(postHeight).observe(document.body, {childList: true, subtree: true});
</script>
</body>
</html>
