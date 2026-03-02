<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <link href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
        <style>
            body {
                font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            }
            .min-h-screen {
                min-height: 100vh;
            }
            .bg-gray-100 {
                background-color: #f3f4f6;
            }
            .bg-gray-900 {
                background-color: #111827;
            }
            .bg-white {
                background-color: #ffffff;
            }
            .bg-gray-800 {
                background-color: #1f2937;
            }
            .text-gray-500 {
                color: #6b7280;
            }
            .text-gray-900 {
                color: #111827;
            }
            .shadow-md {
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }
            .rounded-lg {
                border-radius: 0.5rem;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            {{-- Sélecteur de langue (visible sur login, register, etc.) --}}
            <div class="position-absolute top-0 end-0 mt-3 me-4">
                @foreach(config('app.supported_locales', ['fr', 'en']) as $loc)
                    <a href="{{ route('locale.switch', $loc) }}" class="text-sm {{ app()->getLocale() === $loc ? 'fw-bold text-primary' : 'text-gray-500 hover:text-gray-700' }} text-decoration-none">{{ $loc === 'fr' ? 'Français' : 'English' }}</a>
                    @if(!$loop->last)<span class="text-gray-400 mx-1">|</span>@endif
                @endforeach
            </div>

            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                @yield('content')
            </div>
        </div>
    </body>
</html>
