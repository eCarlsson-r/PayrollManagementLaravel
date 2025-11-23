<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @PwaHead <!-- Add this directive to include the PWA meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Payroll Management System') }}</title>
        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="content container-fluid">
            <div class="d-flex justify-content-center">
                <div class="col-lg-4">
                    <div class="mb-3">
                        <img class="img-fluid" src="{{asset('banner.png')}}" id="logoimg" alt=" Logo" />
                    </div>
                    @yield('content')
                </div>
            </div>
        </div>
        @RegisterServiceWorkerScript <!-- This registers the service worker -->
    </body>
</html>