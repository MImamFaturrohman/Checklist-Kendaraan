<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        @include('partials.favicon')

        @vite(['resources/css/auth.css', 'resources/js/app.js'])
    </head>
    <body class="auth-page-body">
        <div class="container-fluid px-0">
            <div class="row justify-content-center mx-0">
                <div class="col-11 col-sm-10 col-md-7 col-lg-5 col-xl-4 px-2">
                    <div class="card auth-card border-0 p-4 p-md-5 bg-white">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
