@extends('layouts.guest')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-blue-100">
                <svg class="h-12 w-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Page Non Trouvée
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                La page que vous recherchez n'existe pas ou a été déplacée.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            @auth
                @php
                    $user = auth()->user();
                    $dashboardRoute = null;
                    if ($user->hasRole('super-admin')) {
                        $dashboardRoute = 'super.dashboard';
                    } elseif ($user->hasRole('hotel-admin')) {
                        $dashboardRoute = 'hotel.dashboard';
                    } elseif ($user->hasRole('receptionist')) {
                        $dashboardRoute = 'reception.dashboard';
                    }
                @endphp

                @if($dashboardRoute)
                    <a href="{{ route($dashboardRoute) }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Retour au Tableau de Bord
                    </a>
                @endif

                <a href="{{ url()->previous() }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Page Précédente
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Se Connecter
                </a>
            @endauth
        </div>

        <div class="text-xs text-gray-500 mt-8">
            <p>Code d'erreur: 404</p>
            @if(config('app.debug'))
                <p class="mt-2">URL: {{ request()->fullUrl() }}</p>
            @endif
        </div>
    </div>
</div>
@endsection

