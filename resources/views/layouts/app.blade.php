<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>
        KYCN
        @hasSection('title')
            &nbsp;–&nbsp;@yield('title')
        @endif
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no,email=no,address=no">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
    <link rel="icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('head')
</head>
<body>
@include('partials.nav')

<main class="container">
    @include('partials.flash')
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // === Utilities ===
    const on = (el, ev, fn) => el && el.addEventListener(ev, fn);

    // === Submit ===
    function initSubmit(keys = [], options = {}) {
        const getEl = name => document.querySelector(`[name="${name}"]`);
        const submitBtn = document.getElementById('page-submit-button');
        const resetBtn = document.getElementById('page-reset-button');
        const {
            autoSubmitNames = [],
            searchSubmitNames = [],
            swapPairs = [],
            busyMessage = 'Retrieving data...',
        } = options;

        if (!Array.isArray(keys) || keys.length === 0) {
            return;
        }

        const readVal = (name) => {
            const el = getEl(name);
            if (!el) {
                return '';
            }

            if (el.type === 'checkbox') {
                return el.checked ? (el.value || '1') : '';
            }

            if (el.tagName === 'SELECT' && el.multiple) {
                return Array.from(el.selectedOptions).map(o => o.value).filter(Boolean).join(',');
            }

            return (el.value ?? '').trim();
        };

        function navigate({clear = false} = {}) {
            const url = new URL(window.location.href);
            const sp = url.searchParams;

            sp.delete('page');

            if (clear) {
                keys.forEach((k) => sp.delete(k));
            } else {
                keys.forEach((k) => {
                    const v = readVal(k);
                    if (v !== '') {
                        sp.set(k, v);
                    } else {
                        sp.delete(k);
                    }
                });

                swapPairs.forEach(([a, b]) => {
                    const va = sp.get(a), vb = sp.get(b);
                    if (va && vb && va > vb) {
                        sp.set(a, vb);
                        sp.set(b, va);
                    }
                });
            }

            url.search = sp.toString();
            location.assign(url.toString());
        }

        on(submitBtn, 'click', (e) => {
            e.preventDefault();
            navigate({clear: false});
        });

        on(resetBtn, 'click', (e) => {
            e.preventDefault();
            navigate({clear: true});
        });

        autoSubmitNames.forEach(name => on(getEl(name), 'change', () => submitBtn?.click()));

        searchSubmitNames.forEach(name => on(getEl(name), 'keydown', (e) => {
            if (e.key === 'Enter') {
                submitBtn?.click();
            }
        }));
    }

    // === Tooltips ===
    function initTooltip() {
        const nodes = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"], [title]'));
        nodes.forEach(el => new bootstrap.Tooltip(el, {trigger: 'hover'}));
    }

    // === Boot ===
    function boot() {
        initTooltip();
    }

    // ===== Public API (minimal) =====
    window.App = {
        boot,
        ui: {initTooltip},
        forms: {initSubmit},
    };

    // Back-compat shims
    window.__submit = initSubmit;

    on(document, 'DOMContentLoaded', boot);

    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.alert[data-autohide="true"]').forEach(el => {
            setTimeout(() => {
                const alert = new bootstrap.Alert(el);
                try {
                    alert.close();
                } catch (e) {
                }
            }, 5000);
        });
    });
</script>
@stack('scripts')
</body>
</html>
