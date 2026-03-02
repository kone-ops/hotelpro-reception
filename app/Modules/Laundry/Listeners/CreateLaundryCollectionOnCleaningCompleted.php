<?php

namespace App\Modules\Laundry\Listeners;

use App\Core\SettingsResolver;
use App\Modules\Housekeeping\Events\CleaningCompleted;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use App\Modules\Laundry\Services\LaundryService;
use Illuminate\Support\Facades\Log;

class CreateLaundryCollectionOnCleaningCompleted
{
    public function __construct(
        protected LaundryService $laundryService
    ) {}

    /**
     * À la fin du nettoyage d'une chambre : créer une collecte de linge si le module est activé.
     */
    public function handle(CleaningCompleted $event): void
    {
        $room = $event->room->fresh();
        $hotel = $room->hotel;

        if (!SettingsResolver::isModuleEnabled($hotel, 'laundry')) {
            return;
        }

        try {
            $task = HousekeepingTask::where('room_id', $room->id)
                ->where('status', HousekeepingTask::STATUS_DONE)
                ->latest('completed_at')
                ->first();

            $this->laundryService->createCollectionFromCleaning(
                $room,
                $event->completedBy,
                $task?->id
            );
        } catch (\Throwable $e) {
            Log::warning('[Laundry] Erreur création collecte après nettoyage', [
                'room_id' => $room->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
