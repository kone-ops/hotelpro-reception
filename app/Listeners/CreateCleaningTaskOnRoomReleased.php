<?php

namespace App\Listeners;

use App\Core\SettingsResolver;
use App\Events\RoomReleased;
use App\Models\RoomStateHistory;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class CreateCleaningTaskOnRoomReleased
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(RoomReleased $event): void
    {
        $room = $event->room->fresh();
        $hotel = $room->hotel;

        if (!SettingsResolver::isModuleEnabled($hotel, 'housekeeping')) {
            return;
        }

        DB::transaction(function () use ($room, $event) {
            $previousCleaning = $room->cleaning_state ?? 'none';

            $room->update(['cleaning_state' => 'pending']);
            $room->syncStatusFromStates();

            RoomStateHistory::create([
                'room_id' => $room->id,
                'state_type' => 'cleaning',
                'previous_value' => $previousCleaning,
                'new_value' => 'pending',
                'changed_by' => $event->releasedBy->id,
                'service' => 'reception',
                'notes' => 'Chambre libérée – tâche de nettoyage créée',
                'changed_at' => now(),
            ]);

            HousekeepingTask::create([
                'hotel_id' => $room->hotel_id,
                'room_id' => $room->id,
                'type' => HousekeepingTask::TYPE_CLEANING,
                'status' => HousekeepingTask::STATUS_PENDING,
            ]);
        });

        $this->notificationService->notifyHousekeeping(
            $hotel->id,
            'room_released',
            'Chambre à nettoyer',
            "La chambre {$room->room_number} a été libérée et doit être nettoyée.",
            'info',
            $room,
            route('housekeeping.rooms.index'),
            'Voir les chambres à nettoyer'
        );
    }
}
