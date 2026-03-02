<?php

namespace App\Modules\Maintenance\Services;

use App\Models\Panne;
use App\Models\PanneIntervention;
use App\Models\Room;
use App\Modules\Maintenance\Models\MaintenanceArea;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PanneService
{
    /**
     * Signaler une panne (chambre ou espace).
     */
    public function report(
        int $hotelId,
        int $panneTypeId,
        int $panneCategoryId,
        string $locationType,
        ?int $roomId,
        ?int $maintenanceAreaId,
        string $description,
        User $reporter
    ): Panne {
        $this->ensureHotelAccess($hotelId, $reporter);
        $this->ensureLocation($locationType, $roomId, $maintenanceAreaId, $hotelId);

        return DB::transaction(function () use (
            $hotelId, $panneTypeId, $panneCategoryId, $locationType, $roomId, $maintenanceAreaId,
            $description, $reporter
        ) {
            $panne = Panne::create([
                'hotel_id' => $hotelId,
                'panne_type_id' => $panneTypeId,
                'panne_category_id' => $panneCategoryId,
                'location_type' => $locationType,
                'room_id' => $roomId,
                'maintenance_area_id' => $maintenanceAreaId,
                'description' => $description,
                'reported_by' => $reporter->id,
                'reported_at' => now(),
                'status' => Panne::STATUS_SIGNALEE,
            ]);

            PanneIntervention::create([
                'panne_id' => $panne->id,
                'user_id' => $reporter->id,
                'action' => 'reported',
                'notes' => 'Panne signalée.',
                'created_at' => now(),
            ]);

            if ($locationType === Panne::LOCATION_ROOM && $roomId) {
                $room = Room::find($roomId);
                if ($room && $room->hotel_id === $hotelId) {
                    $room->update(['technical_state' => 'issue']);
                    $room->syncStatusFromStates();
                }
            }
            if ($locationType === Panne::LOCATION_AREA && $maintenanceAreaId) {
                MaintenanceArea::where('id', $maintenanceAreaId)->where('hotel_id', $hotelId)
                    ->update(['technical_state' => 'issue']);
            }

            return $panne;
        });
    }

    /**
     * Passer une panne en "en cours de maintenance".
     */
    public function startMaintenance(Panne $panne, User $user, ?string $notes = null): void
    {
        $this->ensurePanneHotel($panne, $user);
        if ($panne->status !== Panne::STATUS_SIGNALEE) {
            throw new \InvalidArgumentException('Seules les pannes signalées peuvent être mises en cours.');
        }

        DB::transaction(function () use ($panne, $user, $notes) {
            $panne->update(['status' => Panne::STATUS_EN_COURS]);
            PanneIntervention::create([
                'panne_id' => $panne->id,
                'user_id' => $user->id,
                'action' => 'started',
                'notes' => $notes ?? 'Intervention démarrée.',
                'created_at' => now(),
            ]);
            $this->syncLocationTechnicalState($panne, 'maintenance');
        });
    }

    /**
     * Marquer une panne comme résolue.
     */
    public function resolve(Panne $panne, User $resolver, ?string $notes = null): void
    {
        $this->ensurePanneHotel($panne, $resolver);
        if ($panne->status === Panne::STATUS_RESOLUE) {
            throw new \InvalidArgumentException('Cette panne est déjà résolue.');
        }

        DB::transaction(function () use ($panne, $resolver, $notes) {
            $panne->update([
                'status' => Panne::STATUS_RESOLUE,
                'resolved_by' => $resolver->id,
                'resolved_at' => now(),
            ]);
            PanneIntervention::create([
                'panne_id' => $panne->id,
                'user_id' => $resolver->id,
                'action' => 'resolved',
                'notes' => $notes ?? 'Panne résolue.',
                'created_at' => now(),
            ]);
            $this->syncLocationTechnicalState($panne, 'normal');
        });
    }

    /**
     * Ajouter une note d'intervention (sans changer le statut).
     */
    public function addInterventionNote(Panne $panne, User $user, string $notes): void
    {
        $this->ensurePanneHotel($panne, $user);
        PanneIntervention::create([
            'panne_id' => $panne->id,
            'user_id' => $user->id,
            'action' => 'note',
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    private function ensureHotelAccess(int $hotelId, User $user): void
    {
        if ($user->hotel_id !== null && $user->hotel_id !== $hotelId) {
            throw new \InvalidArgumentException('Cet hôtel n\'est pas accessible.');
        }
    }

    private function ensurePanneHotel(Panne $panne, User $user): void
    {
        if ($user->hotel_id === null || $panne->hotel_id !== $user->hotel_id) {
            throw new \InvalidArgumentException('Cette panne n\'appartient pas à votre hôtel.');
        }
    }

    private function ensureLocation(string $locationType, ?int $roomId, ?int $maintenanceAreaId, int $hotelId): void
    {
        if ($locationType === Panne::LOCATION_ROOM) {
            if (!$roomId || !Room::where('id', $roomId)->where('hotel_id', $hotelId)->exists()) {
                throw new \InvalidArgumentException('Chambre invalide.');
            }
        }
        if ($locationType === Panne::LOCATION_AREA) {
            if (!$maintenanceAreaId || !MaintenanceArea::where('id', $maintenanceAreaId)->where('hotel_id', $hotelId)->exists()) {
                throw new \InvalidArgumentException('Espace invalide.');
            }
        }
    }

    private function syncLocationTechnicalState(Panne $panne, string $state): void
    {
        if ($panne->location_type === Panne::LOCATION_ROOM && $panne->room_id) {
            $room = $panne->room;
            if ($room) {
                $room->update(['technical_state' => $state]);
                $room->syncStatusFromStates();
            }
        }
        if ($panne->location_type === Panne::LOCATION_AREA && $panne->maintenance_area_id) {
            MaintenanceArea::where('id', $panne->maintenance_area_id)->update(['technical_state' => $state]);
        }
    }
}
