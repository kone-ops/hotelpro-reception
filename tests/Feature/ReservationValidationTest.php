<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\User;
use App\Models\PreReservation;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReservationValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $hotel;
    protected $admin;
    protected $preReservation;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer les rôles
        Role::create(['name' => 'Super Admin']);
        $hotelAdminRole = Role::create(['name' => 'Hotel Admin']);
        Role::create(['name' => 'Reception']);

        // Créer un hôtel
        $this->hotel = Hotel::factory()->create();

        // Créer un type de chambre et une chambre
        $roomType = RoomType::factory()->create([
            'hotel_id' => $this->hotel->id,
        ]);

        $room = Room::factory()->create([
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        // Créer un admin
        $this->admin = User::factory()->create([
            'hotel_id' => $this->hotel->id,
        ]);
        $this->admin->assignRole($hotelAdminRole);

        // Créer une pré-réservation
        $this->preReservation = PreReservation::factory()->create([
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $roomType->id,
            'room_id' => $room->id,
            'statut' => 'pending',
        ]);
    }

    /** @test */
    public function hotel_admin_can_validate_reservation()
    {
        Mail::fake();

        $this->actingAs($this->admin);

        $response = $this->post(route('hotel.reservations.validate', $this->preReservation->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pre_reservations', [
            'id' => $this->preReservation->id,
            'status' => 'validated',
        ]);

        // Vérifier que l'email a été envoyé
        Mail::assertSent(\App\Mail\ReservationValidated::class);
    }

    /** @test */
    public function hotel_admin_can_reject_reservation()
    {
        Mail::fake();

        $this->actingAs($this->admin);

        $response = $this->post(route('hotel.reservations.reject', $this->preReservation->id), [
            'reason' => 'Dates non disponibles',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pre_reservations', [
            'id' => $this->preReservation->id,
            'status' => 'rejected',
        ]);

        // Vérifier que l'email a été envoyé
        Mail::assertSent(\App\Mail\ReservationRejected::class);
    }

    /** @test */
    public function validating_reservation_marks_room_as_occupied()
    {
        $this->actingAs($this->admin);

        $room = $this->preReservation->room;

        $this->assertEquals('available', $room->status);

        $this->post(route('hotel.reservations.validate', $this->preReservation->id));

        $room->refresh();

        $this->assertEquals('occupied', $room->status);
    }

    /** @test */
    public function rejecting_reservation_keeps_room_available()
    {
        $this->actingAs($this->admin);

        $room = $this->preReservation->room;

        $this->post(route('hotel.reservations.reject', $this->preReservation->id), [
            'reason' => 'Test',
        ]);

        $room->refresh();

        $this->assertEquals('available', $room->status);
    }

    /** @test */
    public function admin_cannot_validate_reservation_from_other_hotel()
    {
        // Créer un autre hôtel
        $otherHotel = Hotel::factory()->create();
        $otherReservation = PreReservation::factory()->create([
            'hotel_id' => $otherHotel->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('hotel.reservations.validate', $otherReservation->id));

        $response->assertStatus(403);
    }
}

