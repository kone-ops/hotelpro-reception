<?php

/**
 * Routes API - Version 1
 * 
 * API RESTful pour intégration avec logiciels de gestion hôtelière externes
 * Authentification: Laravel Sanctum
 * 
 * Documentation: /api/documentation (à créer)
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\HotelApiController;
use App\Http\Controllers\Api\ReservationApiController;

/*
|--------------------------------------------------------------------------
| API V1 Routes - Endpoints principaux
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    
    // ============================================
    // ENDPOINTS PUBLICS (sans authentification)
    // ============================================
    
    // Hôtels
    Route::get('/hotels', [HotelApiController::class, 'index'])
        ->name('hotels.index');
    
    Route::get('/hotels/{id}', [HotelApiController::class, 'show'])
        ->name('hotels.show');
    
    Route::get('/hotels/{id}/room-types', [HotelApiController::class, 'roomTypes'])
        ->name('hotels.room-types');
    
    Route::get('/hotels/{id}/rooms', [HotelApiController::class, 'rooms'])
        ->name('hotels.rooms');
    
    Route::get('/hotels/{id}/availability', [HotelApiController::class, 'availability'])
        ->name('hotels.availability');
    
    // Créer une réservation (rate limited)
    Route::post('/hotels/{id}/reservations', [HotelApiController::class, 'createReservation'])
        ->middleware('throttle:10,1')
        ->name('hotels.create-reservation');
    
    // ============================================
    // ENDPOINTS PROTÉGÉS (authentification Sanctum)
    // ============================================
    
    Route::middleware('auth:sanctum')->group(function () {
        
        // User info
        Route::get('/user', function (Request $request) {
            return response()->json([
                'success' => true,
                'data' => $request->user()
            ]);
        })->name('user');
        
        // Réservations d'un hôtel
        Route::get('/hotels/{id}/reservations', [HotelApiController::class, 'reservations'])
            ->name('hotels.reservations');
        
        // Gestion des réservations
        Route::prefix('reservations')->name('reservations.')->group(function () {
            Route::get('/', [ReservationApiController::class, 'index'])->name('index');
            Route::get('/{reservation}', [ReservationApiController::class, 'show'])->name('show');
            Route::post('/{reservation}/validate', [ReservationApiController::class, 'validate'])->name('validate');
            Route::post('/{reservation}/reject', [ReservationApiController::class, 'reject'])->name('reject');
        });
        
        // Statistiques
        Route::get('/stats', [ReservationApiController::class, 'stats'])->name('stats');
    });
});

/*
|--------------------------------------------------------------------------
| Utilitaires
|--------------------------------------------------------------------------
*/

// CSRF Token pour les formulaires (CORS)
Route::get('/csrf-token', function () {
    return response()->json([
        'success' => true,
        'token' => csrf_token(),
    ]);
})->name('api.csrf-token');

// Health check
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'version' => '1.0.0'
    ]);
})->name('api.health');

/*
|--------------------------------------------------------------------------
| BACKWARD COMPATIBILITY (API sans version - à déprécier)
|--------------------------------------------------------------------------
*/

Route::get('/reservations', [ReservationApiController::class, 'index'])
    ->name('api.reservations.index')
    ->middleware('auth:sanctum');

Route::get('/reservations/{reservation}', [ReservationApiController::class, 'show'])
    ->name('api.reservations.show')
    ->middleware('auth:sanctum');
