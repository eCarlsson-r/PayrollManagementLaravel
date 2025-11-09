<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Payroll Management System') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/css/login.css', 'resources/css/magic.css', 'resources/js/app.js'])
    </head>
    <body> 
        <div class="container">
            <div class="text-center">
                <img src="{{URL::asset('/logo.png')}}" id="logoimg" alt=" Logo" />
            </div>
            @yield('content')
        </div>

        @yield('script')
    </body>
</html>