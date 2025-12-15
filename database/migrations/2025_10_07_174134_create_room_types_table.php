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
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('name'); // Nom du type de chambre (Ex: Single, Double, Suite)
            $table->decimal('price', 10, 2); // Prix par nuit
            $table->text('description')->nullable(); // Description optionnelle
            $table->integer('capacity')->nullable(); // Capacité (nombre de personnes)
            $table->boolean('is_available')->default(true); // Disponibilité
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
