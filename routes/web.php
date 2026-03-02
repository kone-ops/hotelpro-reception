<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboard;
use App\Http\Controllers\HotelAdmin\DashboardController as HotelAdminDashboard;
use App\Http\Controllers\Reception\DashboardController as ReceptionDashboard;
use App\Http\Controllers\PublicFormController;
use App\Http\Controllers\HotelAdmin\QrController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Changement de langue (stocké en session, redirection vers la page précédente ou dashboard)
Route::get('/locale/{locale}', function (string $locale) {
    $supported = \App\Http\Middleware\SetLocale::supportedLocales();
    if (! in_array($locale, $supported, true)) {
        abort(400, 'Locale not supported');
    }
    session()->put('locale', $locale);
    return redirect()->back();
})->name('locale.switch');

// Redirection dynamique après login
Route::get('/dashboard', function () {
    $user = auth()->user();
    if (!$user) return redirect()->route('login');
    if ($user->hasRole('super-admin')) return redirect()->route('super.dashboard');
    if ($user->hasRole('hotel-admin')) return redirect()->route('hotel.dashboard');
    if ($user->hasRole('receptionist')) return redirect()->route('reception.dashboard');
    if ($user->hasRole('housekeeping')) return redirect()->route('housekeeping.dashboard');
    if ($user->hasRole('laundry')) return redirect()->route('laundry.dashboard');
    if ($user->hasRole('maintenance')) return redirect()->route('maintenance.dashboard');
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:super-admin'])->prefix('super')->name('super.')->group(function () {
    Route::get('/', [SuperAdminDashboard::class, 'index'])->name('dashboard');
    
    // Hotels management (via modals uniquement, pas de pages create/edit)
    Route::resource('hotels', \App\Http\Controllers\SuperAdmin\HotelController::class)->except(['create', 'edit']);
    Route::post('hotels/delete-multiple', [\App\Http\Controllers\SuperAdmin\HotelController::class, 'destroyMultiple'])->name('hotels.destroy-multiple');
    Route::get('hotels/{hotel}/room-types', [\App\Http\Controllers\SuperAdmin\HotelController::class, 'getRoomTypes'])->name('hotels.room-types');
    // Types de linge (Buanderie) par hôtel
    Route::get('hotels/{hotel}/laundry-item-types', [\App\Http\Controllers\SuperAdmin\LaundryItemTypeController::class, 'index'])->name('hotels.laundry-item-types.index');
    Route::post('hotels/{hotel}/laundry-item-types', [\App\Http\Controllers\SuperAdmin\LaundryItemTypeController::class, 'store'])->name('hotels.laundry-item-types.store');
    Route::put('hotels/{hotel}/laundry-item-types/{laundryItemType}', [\App\Http\Controllers\SuperAdmin\LaundryItemTypeController::class, 'update'])->name('hotels.laundry-item-types.update');
    Route::delete('hotels/{hotel}/laundry-item-types/{laundryItemType}', [\App\Http\Controllers\SuperAdmin\LaundryItemTypeController::class, 'destroy'])->name('hotels.laundry-item-types.destroy');
    Route::get('notifications', [\App\Http\Controllers\SuperAdmin\HotelNotificationController::class, 'index'])->name('notifications.index');
    Route::get('modules', [\App\Http\Controllers\SuperAdmin\HotelController::class, 'modulesIndex'])->name('modules.index');
    Route::put('hotels/{hotel}/modules', [\App\Http\Controllers\SuperAdmin\HotelController::class, 'updateModules'])->name('hotels.modules.update');
    Route::get('hotels/{hotel}/notifications', [\App\Http\Controllers\SuperAdmin\HotelNotificationController::class, 'show'])->name('hotels.notifications.show');
    Route::put('hotels/{hotel}/notifications', [\App\Http\Controllers\SuperAdmin\HotelNotificationController::class, 'update'])->name('hotels.notifications.update');
    
    // Hotel Design & Form Configuration
    Route::get('hotels/{hotel}/design', [\App\Http\Controllers\SuperAdmin\HotelDesignController::class, 'show'])->name('hotels.design');
    Route::put('hotels/{hotel}/design', [\App\Http\Controllers\SuperAdmin\HotelDesignController::class, 'update'])->name('hotels.design.update');
    
    // Gestion des champs personnalisés
    Route::post('hotels/{hotel}/design/fields', [\App\Http\Controllers\SuperAdmin\HotelDesignController::class, 'storeField'])->name('hotels.design.fields.store');
    Route::put('hotels/{hotel}/design/fields/{formField}', [\App\Http\Controllers\SuperAdmin\HotelDesignController::class, 'updateField'])->name('hotels.design.fields.update');
    Route::delete('hotels/{hotel}/design/fields/{formField}', [\App\Http\Controllers\SuperAdmin\HotelDesignController::class, 'destroyField'])->name('hotels.design.fields.destroy');
    Route::post('hotels/{hotel}/design/fields/delete-multiple', [\App\Http\Controllers\SuperAdmin\HotelDesignController::class, 'destroyMultipleFields'])->name('hotels.design.fields.destroy-multiple');
    
    // Users management
    Route::resource('users', \App\Http\Controllers\SuperAdmin\UserController::class);
    Route::post('users/delete-multiple', [\App\Http\Controllers\SuperAdmin\UserController::class, 'destroyMultiple'])->name('users.destroy-multiple');
    
    // Forms management (lecture seule - champs prédéfinis)
    Route::get('/forms', [\App\Http\Controllers\SuperAdmin\FormFieldController::class, 'index'])->name('forms.index');
    
    // Reservations management
    Route::get('/reservations', [\App\Http\Controllers\SuperAdmin\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{id}', [\App\Http\Controllers\SuperAdmin\ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/delete-multiple', [\App\Http\Controllers\SuperAdmin\ReservationController::class, 'destroyMultiple'])->name('reservations.destroy-multiple');
    // Route de gestion désactivée (champs prédéfinis selon cahier de charge)
    // Route::resource('forms', \App\Http\Controllers\SuperAdmin\FormFieldController::class);
    
    // Activity & Reports
    Route::get('/activity', [\App\Http\Controllers\SuperAdmin\ActivityController::class, 'index'])->name('activity');
    Route::get('/activity/load-more', [\App\Http\Controllers\SuperAdmin\ActivityController::class, 'loadMore'])->name('activity.load-more');
    Route::get('/reports', [\App\Http\Controllers\SuperAdmin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/hotel/{hotel}', [\App\Http\Controllers\SuperAdmin\ReportController::class, 'hotel'])->name('reports.hotel');
    
    // Hotel Data Management (Reset, Purge, Import, Export)
    Route::get('/hotel-data', [\App\Http\Controllers\SuperAdmin\HotelDataController::class, 'index'])->name('hotel-data.index');
    Route::get('/hotel-data/{hotel}', [\App\Http\Controllers\SuperAdmin\HotelDataController::class, 'show'])->name('hotel-data.show');
    Route::post('/hotel-data/{hotel}/reset', [\App\Http\Controllers\SuperAdmin\HotelDataController::class, 'reset'])->name('hotel-data.reset');
    Route::post('/hotel-data/{hotel}/purge', [\App\Http\Controllers\SuperAdmin\HotelDataController::class, 'purge'])->name('hotel-data.purge');
    Route::get('/hotel-data/{hotel}/export', [\App\Http\Controllers\SuperAdmin\HotelDataController::class, 'export'])->name('hotel-data.export');
    Route::post('/hotel-data/{hotel}/import', [\App\Http\Controllers\SuperAdmin\HotelDataController::class, 'import'])->name('hotel-data.import');
    
    // System Optimization
    Route::get('/optimization', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'index'])->name('optimization.index');
    Route::get('/optimization/stats', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'getStats'])->name('optimization.stats');
    Route::post('/optimization/clear-caches', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'clearCaches'])->name('optimization.clear-caches');
    Route::post('/optimization/optimize-database', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'optimizeDatabase'])->name('optimization.optimize-database');
    Route::post('/optimization/clean-old-data', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'cleanOldData'])->name('optimization.clean-old-data');
    Route::post('/optimization/full', [\App\Http\Controllers\SuperAdmin\OptimizationController::class, 'fullOptimization'])->name('optimization.full');
    
    // UI Settings
    Route::get('/ui-settings', [\App\Http\Controllers\Super\UiSettingController::class, 'index'])->name('ui-settings.index');
    Route::put('/ui-settings', [\App\Http\Controllers\Super\UiSettingController::class, 'update'])->name('ui-settings.update');
    Route::post('/ui-settings/reset', [\App\Http\Controllers\Super\UiSettingController::class, 'reset'])->name('ui-settings.reset');
    
    // Paramètres applicatifs (super-admin)
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/reset', [\App\Http\Controllers\Admin\SettingsController::class, 'reset'])->name('settings.reset');
    Route::post('/settings/clear-cache', [\App\Http\Controllers\Admin\SettingsController::class, 'clearCache'])->name('settings.clear-cache');
    Route::get('/settings/impression', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.impression');
    Route::put('/settings/impression', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.impression.update');
    Route::post('/settings/impression/reset', [\App\Http\Controllers\SettingController::class, 'reset'])->name('settings.impression.reset');
    
    // Global Database Management (Purge globale)
    Route::get('/database', [\App\Http\Controllers\SuperAdmin\DatabaseController::class, 'index'])->name('database.index');
    Route::get('/database/export', [\App\Http\Controllers\SuperAdmin\DatabaseController::class, 'exportGlobal'])->name('database.export');
    Route::post('/database/purge', [\App\Http\Controllers\SuperAdmin\DatabaseController::class, 'purgeGlobal'])->name('database.purge');
    Route::post('/database/import', [\App\Http\Controllers\SuperAdmin\DatabaseController::class, 'importGlobal'])->name('database.import');
});

// Routes hotel - réservations accessibles aux réceptionnistes ET aux admins hotel
Route::middleware(['auth', 'reception.or.admin', 'hotel.access'])->prefix('hotel')->name('hotel.')->group(function () {
    // Gestion des réservations - accessibles aux réceptionnistes ET aux admins hotel
    Route::get('/reservations', [\App\Http\Controllers\HotelAdmin\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{id}', [\App\Http\Controllers\HotelAdmin\ReservationController::class, 'show'])->name('reservations.show');
    Route::get('/reservations/{id}/edit', [\App\Http\Controllers\HotelAdmin\ReservationController::class, 'edit'])->name('reservations.edit');
    Route::put('/reservations/{id}', [\App\Http\Controllers\HotelAdmin\ReservationController::class, 'update'])->name('reservations.update');
    Route::post('/reservations/{id}/validate', [\App\Http\Controllers\HotelAdmin\ReservationController::class, 'validateReservation'])->name('reservations.validate');
    Route::post('/reservations/{id}/reject', [\App\Http\Controllers\HotelAdmin\ReservationController::class, 'reject'])->name('reservations.reject');
});

// Routes hotel - réservées aux admins hotel uniquement
Route::middleware(['auth', 'role:hotel-admin', 'hotel.access'])->prefix('hotel')->name('hotel.')->group(function () {
    Route::get('/', [HotelAdminDashboard::class, 'index'])->name('dashboard');
    Route::get('/qr', [QrController::class, 'show'])->name('qr');
    Route::get('/qr/download', [QrController::class, 'download'])->name('qr.download');
    
    // Calendrier des réservations
    Route::get('/calendar', [\App\Http\Controllers\HotelAdmin\CalendarController::class, 'index'])->name('calendar');
    Route::get('/calendar/reservations', [\App\Http\Controllers\HotelAdmin\CalendarController::class, 'getReservations'])->name('calendar.reservations');
    
    // Gestion des Types de Chambres
    Route::resource('room-types', \App\Http\Controllers\HotelAdmin\RoomTypeController::class);
    Route::patch('/room-types/{roomType}/toggle', [\App\Http\Controllers\HotelAdmin\RoomTypeController::class, 'toggleAvailability'])->name('room-types.toggle');
    
    // Gestion des Chambres
    Route::resource('rooms', \App\Http\Controllers\HotelAdmin\RoomController::class);
    Route::post('rooms/delete-multiple', [\App\Http\Controllers\HotelAdmin\RoomController::class, 'destroyMultiple'])->name('rooms.destroy-multiple');
    Route::get('/rooms-bulk/create', [\App\Http\Controllers\HotelAdmin\RoomController::class, 'bulkCreate'])->name('rooms.bulk-create');
    Route::post('/rooms-bulk/store', [\App\Http\Controllers\HotelAdmin\RoomController::class, 'bulkStore'])->name('rooms.bulk-store');
    Route::patch('/rooms/{room}/status', [\App\Http\Controllers\HotelAdmin\RoomController::class, 'updateStatus'])->name('rooms.update-status');
    // Espaces (même principe et contenus que Service technique)
    Route::get('/areas', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/{category}', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'category'])->name('areas.category');
    Route::get('/areas/{category}/create', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'create'])->name('areas.create');
    Route::post('/areas/{category}', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'store'])->name('areas.store');
    Route::post('/area/{area}/state', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'updateState'])->name('areas.update-state');
    Route::delete('/area/{area}', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'destroy'])->name('areas.destroy');
    
    // Paramètres d'impression (hotel-admin, même interface que super.settings.impression)
    Route::get('/settings/impression', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.impression');
    Route::put('/settings/impression', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.impression.update');
    Route::post('/settings/impression/reset', [\App\Http\Controllers\SettingController::class, 'reset'])->name('settings.impression.reset');
    
    // ❌ Module "Gestion des formulaires" retiré (champs prédéfinis)
    // Route::get('/fields', [\App\Http\Controllers\HotelAdmin\FormFieldController::class, 'index'])->name('fields.index');
});

// Routes de réception - accessibles aux réceptionnistes ET aux admins hotel
Route::middleware(['auth', 'reception.or.admin', 'hotel.access'])->prefix('reception')->name('reception.')->group(function () {
    Route::get('/', [ReceptionDashboard::class, 'index'])->name('dashboard');
    
    // Gestion des réservations (CRUD complet)
    Route::get('/reservations', [\App\Http\Controllers\Reception\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{id}', [\App\Http\Controllers\Reception\ReservationController::class, 'show'])->name('reservations.show');
    Route::get('/reservations/{id}/edit', [\App\Http\Controllers\Reception\ReservationController::class, 'edit'])->name('reservations.edit');
    Route::put('/reservations/{id}', [\App\Http\Controllers\Reception\ReservationController::class, 'update'])->name('reservations.update');
    
    // Actions sur les réservations - seulement pour les réceptionnistes
    Route::middleware(['role:receptionist'])->group(function () {
        Route::post('/reservations/{id}/validate', [\App\Http\Controllers\Reception\ReservationController::class, 'validateReservation'])->name('reservations.validate');
        Route::post('/reservations/{id}/reject', [\App\Http\Controllers\Reception\ReservationController::class, 'reject'])->name('reservations.reject');
        Route::post('/reservations/{id}/check-in', [\App\Http\Controllers\Reception\ReservationController::class, 'checkIn'])->name('reservations.check-in');
        Route::post('/reservations/{id}/check-out', [\App\Http\Controllers\Reception\ReservationController::class, 'checkOut'])->name('reservations.check-out');
    });
    
    // Feuilles de police (avec toutes les données) - accessibles aux deux
    Route::get('/police-sheet/{id}/preview', [\App\Http\Controllers\Reception\PoliceSheetController::class, 'preview'])->name('police-sheet.preview');
    Route::get('/police-sheet/{id}/generate', [\App\Http\Controllers\Reception\PoliceSheetController::class, 'generate'])->name('police-sheet.generate');
    Route::post('/police-sheet/batch', [\App\Http\Controllers\Reception\PoliceSheetController::class, 'generateBatch'])->name('police-sheet.batch');
    
    // Gestion rapide des chambres (changement de statut) - seulement pour les réceptionnistes
    Route::middleware(['role:receptionist'])->group(function () {
        Route::get('/rooms', [\App\Http\Controllers\Reception\RoomController::class, 'index'])->name('rooms.index');
        Route::patch('/rooms/{room}/status', [\App\Http\Controllers\Reception\RoomController::class, 'updateStatus'])->name('rooms.update-status');
    });
    
    // Clients en séjour
    Route::get('/guests/staying', [\App\Http\Controllers\Reception\GuestController::class, 'staying'])->name('guests.staying');

    // Linge client – Liste, dépôt à la réception, marquer « client a récupéré »
    Route::get('/client-linen/create', [\App\Http\Controllers\Reception\ClientLinenController::class, 'create'])->name('client-linen.create');
    Route::get('/client-linen', [\App\Http\Controllers\Reception\ClientLinenController::class, 'index'])->name('client-linen.index');
    Route::post('/client-linen', [\App\Http\Controllers\Reception\ClientLinenController::class, 'store'])->name('client-linen.store');
    Route::post('/client-linen/{clientLinen}/mark-picked-up', [\App\Http\Controllers\Reception\ClientLinenController::class, 'markPickedUp'])->name('client-linen.mark-picked-up');
    // Espaces (même principe et contenus que Service technique)
    Route::get('/areas', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/{category}', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'category'])->name('areas.category');
    Route::get('/areas/{category}/create', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'create'])->name('areas.create');
    Route::post('/areas/{category}', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'store'])->name('areas.store');
    Route::post('/area/{area}/state', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'updateState'])->name('areas.update-state');
    Route::delete('/area/{area}', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'destroy'])->name('areas.destroy');
});

// Routes Service des étages (Housekeeping) - Module app/Modules/Housekeeping
Route::middleware(['auth', 'role:housekeeping', 'hotel.access'])->prefix('housekeeping')->name('housekeeping.')->group(function () {
    Route::get('/', [\App\Modules\Housekeeping\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/rooms', [\App\Modules\Housekeeping\Controllers\RoomController::class, 'index'])->name('rooms.index');
    Route::post('/rooms/{room}/start-cleaning', [\App\Modules\Housekeeping\Controllers\RoomController::class, 'startCleaning'])->name('rooms.start-cleaning');
    Route::post('/rooms/{room}/complete-cleaning', [\App\Modules\Housekeeping\Controllers\RoomController::class, 'completeCleaning'])->name('rooms.complete-cleaning');
    Route::get('/history', [\App\Modules\Housekeeping\Controllers\HistoryController::class, 'index'])->name('history.index');
    // Linge client – Dépôt (linge trouvé en chambre, notifie la buanderie)
    Route::get('/client-linen', [\App\Modules\Housekeeping\Controllers\ClientLinenController::class, 'create'])->name('client-linen.create');
    Route::post('/client-linen', [\App\Modules\Housekeeping\Controllers\ClientLinenController::class, 'store'])->name('client-linen.store');
    // Espaces (même principe et contenus que Service technique)
    Route::get('/areas', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/{category}', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'category'])->name('areas.category');
    Route::get('/areas/{category}/create', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'create'])->name('areas.create');
    Route::post('/areas/{category}', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'store'])->name('areas.store');
    Route::post('/area/{area}/state', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'updateState'])->name('areas.update-state');
    Route::delete('/area/{area}', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'destroy'])->name('areas.destroy');
});

// Routes Service technique (Maintenance) - Module app/Modules/Maintenance
Route::middleware(['auth', 'role:maintenance', 'hotel.access'])->prefix('maintenance')->name('maintenance.')->group(function () {
    Route::get('/', [\App\Modules\Maintenance\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/rooms', [\App\Modules\Maintenance\Controllers\RoomController::class, 'index'])->name('rooms.index');
    Route::post('/rooms/{room}/technical-state', [\App\Modules\Maintenance\Controllers\RoomController::class, 'updateTechnicalState'])->name('rooms.update-technical-state');
    Route::get('/history', [\App\Modules\Maintenance\Controllers\HistoryController::class, 'index'])->name('history.index');
    // Pannes (signalées, en cours, résolues)
    Route::get('/pannes', [\App\Modules\Maintenance\Controllers\PanneController::class, 'index'])->name('pannes.index');
    Route::get('/pannes/create', [\App\Modules\Maintenance\Controllers\PanneController::class, 'create'])->name('pannes.create');
    Route::post('/pannes', [\App\Modules\Maintenance\Controllers\PanneController::class, 'store'])->name('pannes.store');
    Route::get('/pannes/{panne}', [\App\Modules\Maintenance\Controllers\PanneController::class, 'show'])->name('pannes.show');
    Route::post('/pannes/{panne}/start', [\App\Modules\Maintenance\Controllers\PanneController::class, 'startMaintenance'])->name('pannes.start');
    Route::post('/pannes/{panne}/resolve', [\App\Modules\Maintenance\Controllers\PanneController::class, 'resolve'])->name('pannes.resolve');
    Route::post('/pannes/{panne}/note', [\App\Modules\Maintenance\Controllers\PanneController::class, 'addNote'])->name('pannes.note');
    // Types et catégories de pannes
    Route::get('/panne-types', [\App\Modules\Maintenance\Controllers\PanneTypeCategoryController::class, 'index'])->name('panne-types.index');
    Route::post('/panne-categories', [\App\Modules\Maintenance\Controllers\PanneTypeCategoryController::class, 'storeCategory'])->name('panne-categories.store');
    Route::put('/panne-categories/{panneCategory}', [\App\Modules\Maintenance\Controllers\PanneTypeCategoryController::class, 'updateCategory'])->name('panne-categories.update');
    Route::delete('/panne-categories/{panneCategory}', [\App\Modules\Maintenance\Controllers\PanneTypeCategoryController::class, 'destroyCategory'])->name('panne-categories.destroy');
    Route::post('/panne-types', [\App\Modules\Maintenance\Controllers\PanneTypeCategoryController::class, 'storeType'])->name('panne-types.store');
    Route::put('/panne-types/{panneType}', [\App\Modules\Maintenance\Controllers\PanneTypeCategoryController::class, 'updateType'])->name('panne-types.update');
    Route::delete('/panne-types/{panneType}', [\App\Modules\Maintenance\Controllers\PanneTypeCategoryController::class, 'destroyType'])->name('panne-types.destroy');
    // Espaces (Espaces publics, techniques, extérieurs, Loisirs, Administration)
    Route::get('/areas', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/{category}', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'category'])->name('areas.category');
    Route::get('/areas/{category}/create', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'create'])->name('areas.create');
    Route::post('/areas/{category}', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'store'])->name('areas.store');
    Route::post('/area/{area}/state', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'updateState'])->name('areas.update-state');
    Route::delete('/area/{area}', [\App\Modules\Maintenance\Controllers\MaintenanceAreaController::class, 'destroy'])->name('areas.destroy');
});

// Routes Buanderie (Laundry) - Module app/Modules/Laundry
Route::middleware(['auth', 'role:laundry', 'hotel.access'])->prefix('laundry')->name('laundry.')->group(function () {
    Route::get('/', [\App\Modules\Laundry\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/collections', [\App\Modules\Laundry\Controllers\CollectionController::class, 'index'])->name('collections.index');
    Route::get('/collections/{collection}', [\App\Modules\Laundry\Controllers\CollectionController::class, 'show'])->name('collections.show');
    Route::put('/collections/{collection}', [\App\Modules\Laundry\Controllers\CollectionController::class, 'update'])->name('collections.update');
    Route::post('/collections/{collection}/status', [\App\Modules\Laundry\Controllers\CollectionController::class, 'updateStatus'])->name('collections.update-status');
    Route::get('/item-types', [\App\Modules\Laundry\Controllers\ItemTypeController::class, 'index'])->name('item-types.index');
    Route::post('/item-types', [\App\Modules\Laundry\Controllers\ItemTypeController::class, 'store'])->name('item-types.store');
    Route::put('/item-types/{itemType}', [\App\Modules\Laundry\Controllers\ItemTypeController::class, 'update'])->name('item-types.update');
    Route::delete('/item-types/{itemType}', [\App\Modules\Laundry\Controllers\ItemTypeController::class, 'destroy'])->name('item-types.destroy');
    Route::get('/history', [\App\Modules\Laundry\Controllers\HistoryController::class, 'index'])->name('history.index');
    // Linge client (réception + chambre)
    Route::get('/client-linen', [\App\Modules\Laundry\Controllers\ClientLinenController::class, 'index'])->name('client-linen.index');
    Route::post('/client-linen/{clientLinen}/status', [\App\Modules\Laundry\Controllers\ClientLinenController::class, 'updateStatus'])->name('client-linen.update-status');
});

// Formulaire public (QR code) avec rate limiting
// Limites plus élevées pour permettre un usage normal (rechargements, tests, etc.)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/f/{hotel}', [PublicFormController::class, 'show'])->name('public.form');
});
Route::middleware('throttle:20,60')->group(function () {
    Route::post('/f/{hotel}', [PublicFormController::class, 'store'])->name('public.form.store');
});

// API Routes pour la disponibilité des chambres (utilisées par le formulaire public)
Route::middleware('throttle:120,1')->prefix('api/hotels/{hotel}')->name('api.')->group(function () {
    Route::get('/room-types', [\App\Http\Controllers\Api\RoomAvailabilityController::class, 'getRoomTypes'])->name('room-types');
    Route::get('/available-rooms', [\App\Http\Controllers\Api\RoomAvailabilityController::class, 'getAvailableRooms'])->name('available-rooms');
    Route::get('/rooms/{room}/availability', [\App\Http\Controllers\Api\RoomAvailabilityController::class, 'checkRoomAvailability'])->name('room-availability');
    Route::get('/clients/search', [\App\Http\Controllers\ClientController::class, 'search'])->name('clients.search');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route de suppression de compte désactivée
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Gestion des sessions utilisateur
    Route::get('/sessions', [\App\Http\Controllers\UserSessionController::class, 'index'])->name('sessions.index');
    Route::delete('/sessions/{sessionId}', [\App\Http\Controllers\UserSessionController::class, 'destroy'])->name('sessions.destroy');
    Route::post('/sessions/{sessionId}/trust', [\App\Http\Controllers\UserSessionController::class, 'trust'])->name('sessions.trust');
    Route::post('/sessions/destroy-others', [\App\Http\Controllers\UserSessionController::class, 'destroyOthers'])->name('sessions.destroy-others');
    
    // Page dédiée aux notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/load-more', [\App\Http\Controllers\NotificationController::class, 'loadMore'])->name('notifications.load-more');
    
    // API Routes pour les notifications (avec rate limiting)
    Route::prefix('api/notifications')->name('api.notifications.')->middleware('throttle:60,1')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('/pending-operations', [\App\Http\Controllers\Api\NotificationController::class, 'checkPendingOperations'])->name('pending-operations');
        Route::post('/{notification}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{notification}', [\App\Http\Controllers\Api\NotificationController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/auth.php';
