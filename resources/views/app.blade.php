<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        {{-- ALW favicon --}}
        <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
        <link rel="shortcut icon" href="/favicon.svg" />

        {{-- Titulo sa tab sa browser --}}
        <title inertia>{{ config('app.name', 'ALW') }}</title>

        {{-- Inertia head — para sa dynamic meta tags sa matag page --}}
        @inertiaHead

        {{-- Ziggy — i-expose ang Laravel named routes sa JavaScript --}}
        @routes

        {{-- Vite — nag-load sa compiled CSS ug JS assets --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        {{-- Kini ang root div nga gi-mount sa Vue/Inertia --}}
        @inertia
    </body>
</html>
