<?php

namespace App\Modules\Maintenance\Services;

use App\Core\RulesEngine\RoomStateValidator;
use App\Models\Room;
use App\Models\RoomStateHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MaintenanceService
{
    public function __construct(
        protected RoomStateValidator $stateValidator
    ) {}

    /**
     * Mettre à jour l'état technique d'une chambre (issue, maintenance, out_of_service, normal).
     * Pour out_of_service : raison, période (from/until) et out_of_service_by sont enregistrés.
     */
    public function updateTechnicalState(
        Room $room,
        string $newState,
        User $user,
        ?string $notes = null,
        ?string $outOfServiceReason = null,
        ?\DateTimeInterface $outOfServiceFrom = null,
        ?\DateTimeInterface $outOfServiceUntil = null
    ): void {
        $this->ensureRoomBelongsToUserHotel($room, $user);

        $previous = $room->technical_state ?? 'normal';
        $result = $this->stateValidator->validateTransition($room, 'technical', $newState, $user);
        if (!$result['valid']) {
            throw new \InvalidArgumentException($result['message']);
        }

        DB::transaction(function () use ($room, $newState, $user, $notes, $previous, $outOfServiceReason, $outOfServiceFrom, $outOfServiceUntil) {
            $updates = ['technical_state' => $newState];
            if (in_array($newState, ['maintenance', 'out_of_service'], true)) {
                $updates['occupation_state'] = 'free';
            }
            if ($newState === 'out_of_service') {
                $updates['out_of_service_reason'] = $outOfServiceReason;
                $updates['out_of_service_from'] = $outOfServiceFrom;
                $updates['out_of_service_until'] = $outOfServiceUntil;
                $updates['out_of_service_by'] = $user->id;
            }
            if ($newState === 'normal') {
                $updates['out_of_service_reason'] = null;
                $updates['out_of_service_from'] = null;
                $updates['out_of_service_until'] = null;
                $updates['out_of_service_by'] = null;
            }
            $room->update($updates);
            $room->syncStatusFromStates();

            $historyNotes = $notes ?? $this->labelForState($newState);
            if ($newState === 'out_of_service' && $outOfServiceReason) {
                $historyNotes = $outOfServiceReason . (strlen($historyNotes) ? ' — ' . $historyNotes : '');
            }
            RoomStateHistory::create([
                'room_id' => $room->id,
                'state_type' => 'technical',
                'previous_value' => $previous,
                'new_value' => $newState,
                'changed_by' => $user->id,
                'service' => 'maintenance',
                'notes' => $historyNotes,
                'changed_at' => now(),
            ]);
        });
    }

    protected function labelForState(string $state): string
    {
        return match ($state) {
            'normal' => 'Remise en service',
            'issue' => 'Problème signalé',
            'maintenance' => 'Mise en maintenance',
            'out_of_service' => 'Hors service',
            default => $state,
        };
    }

    protected function ensureRoomBelongsToUserHotel(Room $room, User $user): void
    {
        if ($user->hotel_id === null || $room->hotel_id !== $user->hotel_id) {
            throw new \InvalidArgumentException('Cette chambre n\'appartient pas à votre hôtel.');
        }
    }
}
