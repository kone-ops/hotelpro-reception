<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReservationValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $hotel;
    protected $admin;
    protected $reservation;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'hotel-admin']);
        Role::create(['name' => 'receptionist']);

        $this->hotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $this->hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        $this->admin = User::factory()->create(['hotel_id' => $this->hotel->id]);
        $this->admin->assignRole('hotel-admin');

        $this->reservation = Reservation::withoutGlobalScopes()->create([
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $roomType->id,
            'room_id' => $room->id,
            'status' => 'pending',
            'data' => ['nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'jean@test.com'],
            'check_in_date' => now(),
            'check_out_date' => now()->addDays(2),
        ]);
    }

    #[Test]
    public function hotel_admin_can_validate_reservation(): void
    {
        Mail::fake();
        $this->actingAs($this->admin);

        $response = $this->post(route('hotel.reservations.validate', $this->reservation->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('reservations', [
            'id' => $this->reservation->id,
            'status' => 'validated',
        ]);
        Mail::assertSent(\App\Mail\ReservationValidated::class);
    }

    #[Test]
    public function hotel_admin_can_reject_reservation(): void
    {
        Mail::fake();
        $this->actingAs($this->admin);

        $response = $this->post(route('hotel.reservations.reject', $this->reservation->id), [
            'reason' => 'Dates non disponibles',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('reservations', [
            'id' => $this->reservation->id,
            'status' => 'rejected',
        ]);
        Mail::assertSent(\App\Mail\ReservationRejected::class);
    }

    #[Test]
    public function validating_reservation_marks_room_as_occupied(): void
    {
        $this->actingAs($this->admin);
        $room = $this->reservation->room;
        $this->assertEquals('available', $room->status);

        $this->post(route('hotel.reservations.validate', $this->reservation->id));

        $room->refresh();
        $this->assertEquals('occupied', $room->status);
    }

    #[Test]
    public function rejecting_reservation_keeps_room_available(): void
    {
        $this->actingAs($this->admin);
        $room = $this->reservation->room;

        $this->post(route('hotel.reservations.reject', $this->reservation->id), [
            'reason' => 'Test',
        ]);

        $room->refresh();
        $this->assertEquals('available', $room->status);
    }

    #[Test]
    public function admin_cannot_validate_reservation_from_other_hotel(): void
    {
        $otherHotel = Hotel::factory()->create();
        $otherRoomType = RoomType::factory()->create(['hotel_id' => $otherHotel->id]);
        $otherRoom = Room::factory()->create([
            'hotel_id' => $otherHotel->id,
            'room_type_id' => $otherRoomType->id,
        ]);
        $otherReservation = Reservation::withoutGlobalScopes()->create([
            'hotel_id' => $otherHotel->id,
            'room_type_id' => $otherRoomType->id,
            'room_id' => $otherRoom->id,
            'status' => 'pending',
            'data' => [],
            'check_in_date' => now(),
            'check_out_date' => now()->addDays(2),
        ]);

        $this->actingAs($this->admin);
        $response = $this->post(route('hotel.reservations.validate', $otherReservation->id));

        $response->assertStatus(403);
    }
}

