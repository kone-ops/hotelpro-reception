<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\User;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoomManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $hotel;
    protected $admin;
    protected $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'hotel-admin']);

        $this->hotel = Hotel::factory()->create();

        // Créer un type de chambre
        $this->roomType = RoomType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Simple',
            'price' => 50000,
        ]);

        // Créer un admin
        $this->admin = User::factory()->create([
            'hotel_id' => $this->hotel->id,
        ]);
        $this->admin->assignRole('hotel-admin');
    }

    #[Test]
    public function hotel_admin_can_create_room(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('hotel.rooms.store'), [
            'room_type_id' => $this->roomType->id,
            'room_number' => '101',
            'floor' => 1,
            'status' => 'available',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('rooms', [
            'hotel_id' => $this->hotel->id,
            'room_number' => '101',
            'floor' => 1,
        ]);
    }

    #[Test]
    public function room_number_must_be_unique_per_hotel(): void
    {
        $this->actingAs($this->admin);

        // Créer une première chambre
        Room::factory()->create([
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'room_number' => '101',
        ]);

        // Essayer de créer une chambre avec le même numéro
        $response = $this->post(route('hotel.rooms.store'), [
            'room_type_id' => $this->roomType->id,
            'room_number' => '101',
            'floor' => 1,
        ]);

        $response->assertSessionHasErrors('room_number');
    }

    #[Test]
    public function hotel_admin_can_bulk_create_rooms(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('hotel.rooms-bulk.store'), [
            'room_type_id' => $this->roomType->id,
            'quantity' => 5,
            'prefix' => '1',
            'start_number' => 1,
            'floor' => 1,
        ]);

        $response->assertRedirect();

        // Vérifier que 5 chambres ont été créées
        $this->assertEquals(5, Room::where('hotel_id', $this->hotel->id)->count());

        // Vérifier les numéros de chambre
        $this->assertDatabaseHas('rooms', ['room_number' => '101']);
        $this->assertDatabaseHas('rooms', ['room_number' => '105']);
    }

    #[Test]
    public function hotel_admin_can_change_room_status(): void
    {
        $this->actingAs($this->admin);

        $room = Room::factory()->create([
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'status' => 'available',
        ]);

        $response = $this->patch(route('hotel.rooms.update-status', $room), [
            'status' => 'occupied',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $room->refresh();
        $this->assertEquals('occupied', $room->status);
    }

    #[Test]
    public function admin_cannot_manage_rooms_from_other_hotel(): void
    {
        // Créer un autre hôtel
        $otherHotel = Hotel::factory()->create();
        $otherRoomType = RoomType::factory()->create([
            'hotel_id' => $otherHotel->id,
        ]);
        $otherRoom = Room::factory()->create([
            'hotel_id' => $otherHotel->id,
            'room_type_id' => $otherRoomType->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->patch(route('hotel.rooms.update-status', $otherRoom), [
            'status' => 'occupied',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }
}

