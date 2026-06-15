<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @PwaHead <!-- Add this directive to include the PWA meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Payroll Management System') }}</title>
        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .auth-body {
                min-height: 100vh;
                margin: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
                background:
                    radial-gradient(1100px 600px at 15% -10%, rgba(99,102,241,.18), transparent 60%),
                    radial-gradient(900px 500px at 110% 10%, rgba(168,85,247,.16), transparent 55%),
                    #f5f6f9;
            }
            .auth-wrap { width: 100%; max-width: 430px; }
            .auth-card {
                background: #fff;
                border: 1px solid var(--surface-border);
                border-radius: 22px;
                box-shadow: 0 24px 60px rgba(17,24,39,.12);
                padding: 2rem 1.9rem;
            }
            .auth-brand { text-align: center; margin-bottom: 1.25rem; }
            .auth-brand img { height: 46px; width: auto; }
            .auth-foot { text-align: center; color: var(--ink-soft); font-size: .8rem; margin-top: 1.1rem; }
            .auth-card .nav-tabs { border-bottom: 1px solid var(--surface-border); margin-bottom: 1.1rem; gap: .25rem; }
            .auth-card .nav-tabs .nav-link {
                border: none; color: var(--ink-soft); font-weight: 600; border-radius: 10px 10px 0 0; padding: .5rem .9rem;
            }
            .auth-card .nav-tabs .nav-link.active {
                color: var(--brand-dark); background: transparent; border-bottom: 2px solid var(--brand);
            }
        </style>
    </head>
    <body class="auth-body">
        <div class="auth-wrap">
            <div class="auth-card">
                <div class="auth-brand">
                    <img src="{{ asset('banner.png') }}" id="logoimg" alt="Logo" />
                </div>
                @yield('content')
            </div>
            <p class="auth-foot">&copy; Carlsson Studio 2026</p>
        </div>
        @RegisterServiceWorkerScript <!-- This registers the service worker -->
    </body>
</html>
