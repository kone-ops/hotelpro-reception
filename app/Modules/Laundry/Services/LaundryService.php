<?php

namespace App\Modules\Laundry\Services;

use App\Core\SettingsResolver;
use App\Models\Room;
use App\Models\User;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use App\Modules\Laundry\Models\LaundryCollection;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class LaundryService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Créer une collecte de linge à partir d'un nettoyage terminé (appelé par le listener).
     */
    public function createCollectionFromCleaning(Room $room, User $collectedBy, ?int $housekeepingTaskId = null): LaundryCollection
    {
        $hotel = $room->hotel;
        if (!SettingsResolver::isModuleEnabled($hotel, 'laundry')) {
            throw new \InvalidArgumentException('Le module buanderie n\'est pas activé pour cet hôtel.');
        }

        return DB::transaction(function () use ($room, $collectedBy, $housekeepingTaskId) {
            $collection = LaundryCollection::create([
                'hotel_id' => $room->hotel_id,
                'room_id' => $room->id,
                'housekeeping_task_id' => $housekeepingTaskId,
                'collected_at' => now(),
                'collected_by' => $collectedBy->id,
                'status' => LaundryCollection::STATUS_PENDING,
                'notes' => 'Collecte automatique après nettoyage chambre ' . $room->room_number,
            ]);

            $this->notificationService->notifyLaundry(
                $room->hotel_id,
                'laundry_collection_created',
                'Nouvelle collecte de linge',
                "Linge à traiter pour la chambre {$room->room_number}.",
                'info',
                $collection,
                route('laundry.collections.show', $collection),
                'Voir la collecte'
            );

            return $collection;
        });
    }

    /**
     * Mettre à jour le statut d'une collecte (en lavage, terminée).
     */
    public function updateCollectionStatus(LaundryCollection $collection, string $status, ?User $user = null): void
    {
        $allowed = [LaundryCollection::STATUS_PENDING, LaundryCollection::STATUS_IN_WASH, LaundryCollection::STATUS_DONE];
        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Statut invalide.');
        }
        $collection->update(['status' => $status]);
    }

    /**
     * Enregistrer ou mettre à jour les lignes (quantités par type de linge) d'une collecte.
     */
    public function saveCollectionLines(LaundryCollection $collection, array $lines): void
    {
        foreach ($lines as $itemTypeId => $quantity) {
            $quantity = max(0, (int) $quantity);
            $collection->lines()->updateOrCreate(
                ['laundry_item_type_id' => $itemTypeId],
                ['quantity' => $quantity]
            );
        }
    }
}
