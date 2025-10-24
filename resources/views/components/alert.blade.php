{{--
    Accepted `type` values (Bootstrap contextual classes):
    - primary
    - secondary
    - success
    - danger
    - warning (default)
    - info
    - light
    - dark

    Docs: https://getbootstrap.com/docs/4.0/components/alerts/
--}}
@props([
    'type' => 'warning',
    'heading' => 'Something went wrong',
    'text' => null,
])

<div class="alert alert-{{ $type }} w-100 m-0" role="alert">
    <strong>{{ $heading }}</strong>

    @if (!empty($text))
        <hr>
        <p class="m-0 fs-md">{{ $text }}</p>
    @endif
</div>
