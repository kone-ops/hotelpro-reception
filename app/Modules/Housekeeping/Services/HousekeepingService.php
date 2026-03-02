<?php

namespace App\Modules\Housekeeping\Services;

use App\Core\RulesEngine\RoomStateValidator;
use App\Models\Room;
use App\Models\RoomStateHistory;
use App\Models\User;
use App\Modules\Housekeeping\Events\CleaningCompleted;
use App\Modules\Housekeeping\Events\CleaningStarted;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use App\Modules\Laundry\Models\ClientLinen;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class HousekeepingService
{
    public function __construct(
        protected RoomStateValidator $stateValidator,
        protected NotificationService $notificationService
    ) {}

    /**
     * Démarrer le nettoyage d'une chambre (pending → in_progress).
     */
    public function startCleaning(Room $room, User $user): void
    {
        $this->ensureRoomBelongsToUserHotel($room, $user);

        $previous = $room->cleaning_state ?? 'none';
        if (!in_array($previous, ['pending'], true)) {
            throw new \InvalidArgumentException("La chambre {$room->room_number} n'est pas en attente de nettoyage.");
        }

        DB::transaction(function () use ($room, $user, $previous) {
            $room->update(['cleaning_state' => 'in_progress']);
            $room->syncStatusFromStates();

            RoomStateHistory::create([
                'room_id' => $room->id,
                'state_type' => 'cleaning',
                'previous_value' => $previous,
                'new_value' => 'in_progress',
                'changed_by' => $user->id,
                'service' => 'housekeeping',
                'notes' => 'Nettoyage démarré',
                'changed_at' => now(),
            ]);

            HousekeepingTask::where('room_id', $room->id)
                ->where('status', HousekeepingTask::STATUS_PENDING)
                ->update([
                    'status' => HousekeepingTask::STATUS_IN_PROGRESS,
                    'assigned_to' => $user->id,
                    'started_at' => now(),
                ]);
        });

        event(new CleaningStarted($room->fresh(), $user));
    }

    /**
     * Terminer le nettoyage d'une chambre (in_progress → done, occupation_state → free, status → available).
     * Si $clientLinenDescription est renseigné, crée un enregistrement "Linge client – Chambre" et notifie la buanderie.
     */
    public function completeCleaning(Room $room, User $user, ?string $notes = null, ?string $clientLinenDescription = null): void
    {
        $this->ensureRoomBelongsToUserHotel($room, $user);

        $previousCleaning = $room->cleaning_state ?? 'none';
        if (!in_array($previousCleaning, ['in_progress', 'pending'], true)) {
            throw new \InvalidArgumentException("La chambre {$room->room_number} n'a pas de nettoyage en cours.");
        }

        $previousOccupation = $room->occupation_state ?? 'released';
        $task = HousekeepingTask::where('room_id', $room->id)
            ->whereIn('status', [HousekeepingTask::STATUS_PENDING, HousekeepingTask::STATUS_IN_PROGRESS])
            ->first();

        DB::transaction(function () use ($room, $user, $previousCleaning, $previousOccupation, $notes, $clientLinenDescription, $task) {
            $room->update([
                'cleaning_state' => 'done',
                'occupation_state' => 'free',
            ]);
            $room->syncStatusFromStates();

            RoomStateHistory::create([
                'room_id' => $room->id,
                'state_type' => 'cleaning',
                'previous_value' => $previousCleaning,
                'new_value' => 'done',
                'changed_by' => $user->id,
                'service' => 'housekeeping',
                'notes' => $notes ?? 'Nettoyage terminé',
                'changed_at' => now(),
            ]);

            RoomStateHistory::create([
                'room_id' => $room->id,
                'state_type' => 'occupation',
                'previous_value' => $previousOccupation,
                'new_value' => 'free',
                'changed_by' => $user->id,
                'service' => 'housekeeping',
                'notes' => 'Chambre prête après nettoyage',
                'changed_at' => now(),
            ]);

            HousekeepingTask::where('room_id', $room->id)
                ->whereIn('status', [HousekeepingTask::STATUS_PENDING, HousekeepingTask::STATUS_IN_PROGRESS])
                ->update([
                    'status' => HousekeepingTask::STATUS_DONE,
                    'completed_at' => now(),
                    'notes' => $notes,
                ]);

            if (!empty(trim((string) $clientLinenDescription))) {
                $clientLinen = ClientLinen::create([
                    'hotel_id' => $room->hotel_id,
                    'source' => ClientLinen::SOURCE_ROOM,
                    'room_id' => $room->id,
                    'reservation_id' => null,
                    'housekeeping_task_id' => $task?->id,
                    'received_at' => now(),
                    'received_by' => $user->id,
                    'status' => ClientLinen::STATUS_PENDING_PICKUP,
                    'description' => trim($clientLinenDescription),
                    'notes' => $notes,
                    'client_name' => null,
                ]);
                $this->notificationService->notifyLaundry(
                    $room->hotel_id,
                    'client_linen_room',
                    'Linge client – Chambre',
                    "Linge client signalé en chambre {$room->room_number} par le service des étages.",
                    'info',
                    null,
                    $clientLinen,
                    route('laundry.client-linen.index', ['source' => 'room']),
                    'Voir le linge client – Chambre'
                );
            }
        });

        event(new CleaningCompleted($room->fresh(), $user, $notes));
    }

    protected function ensureRoomBelongsToUserHotel(Room $room, User $user): void
    {
        if ($user->hotel_id !== $room->hotel_id) {
            throw new \InvalidArgumentException('Cette chambre n\'appartient pas à votre hôtel.');
        }
    }
}
