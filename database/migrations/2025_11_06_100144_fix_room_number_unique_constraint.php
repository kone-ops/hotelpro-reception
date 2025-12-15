<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Supprimer la contrainte unique sur room_number seul
            $table->dropUnique(['room_number']);
            
            // Ajouter une contrainte unique composite (hotel_id + room_number)
            // Un numéro de chambre doit être unique PAR HÔTEL, pas globalement
            $table->unique(['hotel_id', 'room_number'], 'rooms_hotel_room_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Supprimer la contrainte composite
            $table->dropUnique('rooms_hotel_room_unique');
            
            // Remettre la contrainte unique simple
            $table->unique('room_number');
        });
    }
};
