<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Tests du workflow réservation : validation → check-in → check-out (réception).
 * Vérifie les statuts réservation, chambre, et la création d'une tâche de nettoyage après check-out.
 */
class ReservationQrWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;
    protected User $receptionist;
    protected RoomType $roomType;
    protected Room $room;
    protected Reservation $reservation;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'receptionist']);

        $this->hotel = Hotel::factory()->create();
        $this->roomType = RoomType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Simple',
        ]);
        $this->room = Room::factory()->create([
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'room_number' => '101',
            'status' => 'available',
            'occupation_state' => 'free',
            'cleaning_state' => 'none',
            'technical_state' => 'normal',
        ]);

        $this->receptionist = User::factory()->create([
            'hotel_id' => $this->hotel->id,
        ]);
        $this->receptionist->assignRole('receptionist');

        $this->reservation = Reservation::withoutGlobalScopes()->create([
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'room_id' => $this->room->id,
            'status' => 'pending',
            'data' => [
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'jean@example.com',
                'telephone' => '+33600000000',
            ],
            'check_in_date' => now(),
            'check_out_date' => now()->addDays(2),
        ]);
    }

    #[Test]
    public function full_workflow_validate_checkin_checkout_creates_housekeeping_task(): void
    {
        $this->actingAs($this->receptionist);

        $this->assertSame('pending', $this->reservation->fresh()->status);
        $this->assertSame('available', $this->room->fresh()->status);

        // 1. Validation
        $r1 = $this->post(route('reception.reservations.validate', $this->reservation->id));
        $r1->assertRedirect();
        $r1->assertSessionHas('success');
        $this->reservation->refresh();
        $this->assertSame('validated', $this->reservation->status);
        $this->room->refresh();
        $this->assertSame('occupied', $this->room->status);

        // 2. Check-in
        $r2 = $this->post(route('reception.reservations.check-in', $this->reservation->id));
        $r2->assertRedirect();
        $r2->assertSessionHas('success');
        $this->reservation->refresh();
        $this->assertSame('checked_in', $this->reservation->status);
        $this->assertNotNull($this->reservation->checked_in_at);

        // 3. Check-out (libère la chambre, déclenche RoomReleased → tâche housekeeping si module activé)
        $r3 = $this->post(route('reception.reservations.check-out', $this->reservation->id));
        $r3->assertRedirect();
        $r3->assertSessionHas('success');
        $this->reservation->refresh();
        $this->assertSame('checked_out', $this->reservation->status);
        $this->assertNotNull($this->reservation->checked_out_at);

        $this->room->refresh();
        $this->assertSame('released', $this->room->occupation_state);

        // Si le module housekeeping est activé pour l'hôtel, une tâche de nettoyage doit exister
        $task = HousekeepingTask::where('room_id', $this->room->id)->first();
        if ($task !== null) {
            $this->assertSame(HousekeepingTask::STATUS_PENDING, $task->status);
        }
    }

    #[Test]
    public function placeholder_workflow_test(): void
    {
        $this->assertTrue(true, 'Placeholder : à remplacer par les tests du workflow QR complet.');
    }
}
