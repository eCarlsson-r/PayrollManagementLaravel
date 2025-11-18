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
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-4 col-lg-push-4">
                    <div class="form-group text-center">
                        <img src="{{asset('banner.png')}}" id="logoimg" alt=" Logo" />
                    </div>
                    @yield('content')
                </div>
            </div>
        </div>
        @yield('script')
        @RegisterServiceWorkerScript <!-- This registers the service worker -->
    </body>
</html>