<?php

namespace App\Providers;

use App\Events\RoomReleased;
use App\Listeners\CreateCleaningTaskOnRoomReleased;
use App\Modules\Housekeeping\Events\CleaningCompleted;
use App\Modules\Laundry\Listeners\CreateLaundryCollectionOnCleaningCompleted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(RoomReleased::class, CreateCleaningTaskOnRoomReleased::class);
        Event::listen(CleaningCompleted::class, CreateLaundryCollectionOnCleaningCompleted::class);
    }
}


