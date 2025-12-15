<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\PreReservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PublicFormTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $hotel;
    protected $roomType;
    protected $room;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un hôtel de test
        $this->hotel = Hotel::factory()->create([
            'name' => 'Test Hotel',
            'email' => 'test@hotel.com',
        ]);

        // Créer un type de chambre
        $this->roomType = RoomType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Simple',
            'price' => 50000,
            'capacity' => 2,
        ]);

        // Créer une chambre
        $this->room = Room::factory()->create([
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'room_number' => '101',
            'status' => 'available',
        ]);
    }

    /** @test */
    public function it_displays_public_form()
    {
        $response = $this->get(route('public.form', $this->hotel));

        $response->assertStatus(200);
        $response->assertSee($this->hotel->name);
        $response->assertSee('Formulaire de Pré-réservation');
    }

    /** @test */
    public function it_can_create_reservation_with_valid_data()
    {
        $data = [
            'type_reservation' => 'Individuelle',
            'type_piece_identite' => 'CNI',
            'numero_piece_identite' => '123456789',
            'nom' => 'Doe',
            'prenom' => 'John',
            'sexe' => 'M',
            'date_naissance' => '1990-01-01',
            'lieu_naissance' => 'Paris',
            'nationalite' => 'Française',
            'adresse' => '123 Rue Test',
            'ville' => 'Paris',
            'pays' => 'France',
            'telephone' => '+33612345678',
            'email' => 'john.doe@example.com',
            'profession' => 'Ingénieur',
            'venant_de' => 'Paris',
            'date_arrivee' => now()->addDays(7)->format('Y-m-d'),
            'date_depart' => now()->addDays(10)->format('Y-m-d'),
            'nombre_nuits' => 3,
            'nombre_adultes' => 1,
            'nombre_enfants' => 0,
            'room_type_id' => $this->roomType->id,
            'room_id' => $this->room->id,
            'confirmation_exactitude' => true,
            'acceptation_conditions' => true,
        ];

        $response = $this->post(route('public.form.store', $this->hotel), $data);

        $response->assertRedirect(route('public.form', $this->hotel));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pre_reservations', [
            'hotel_id' => $this->hotel->id,
            'email' => 'john.doe@example.com',
            'nom' => 'Doe',
            'prenom' => 'John',
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->post(route('public.form.store', $this->hotel), []);

        $response->assertSessionHasErrors([
            'nom',
            'prenom',
            'email',
            'telephone',
        ]);
    }

    /** @test */
    public function it_validates_email_format()
    {
        $data = [
            'email' => 'invalid-email',
            'nom' => 'Doe',
            'prenom' => 'John',
        ];

        $response = $this->post(route('public.form.store', $this->hotel), $data);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function it_respects_rate_limiting()
    {
        $data = [
            'type_reservation' => 'Individuelle',
            'nom' => 'Doe',
            'prenom' => 'John',
            'email' => 'john@example.com',
            'telephone' => '+33612345678',
            'date_arrivee' => now()->addDays(7)->format('Y-m-d'),
            'date_depart' => now()->addDays(10)->format('Y-m-d'),
            'nombre_adultes' => 1,
        ];

        // Faire 4 requêtes (limite est 3 par heure)
        for ($i = 0; $i < 4; $i++) {
            $response = $this->post(route('public.form.store', $this->hotel), $data);
        }

        // La 4ème requête devrait être bloquée
        $response->assertStatus(429); // Too Many Requests
    }
}

