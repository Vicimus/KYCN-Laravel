@props([
    'dealer',
    'larger' => false,
])

@if ($dealer)
    <div class="logo-thumb {{ $dealer->logo_url ? 'normal' : 'initials' }}"
         style="--initials-bg: {{ $dealer->initials_bg }}; --size: {{ $larger ? '96px' : '48px' }}"
    >
        @if ($dealer->logo_url)
            <img src="{{ $dealer->logo_url }}" alt="{{ $dealer->name }} logo" loading="lazy" decoding="async"/>
        @else
            <div class="logo-initials" aria-hidden="true">{{ $dealer->initials }}</div>
        @endif
    </div>
@endif
