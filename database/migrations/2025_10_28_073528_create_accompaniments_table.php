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
        Schema::create('accompaniments', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique(); // Token unique pour le QR code
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->integer('accompaniment_number'); // 1, 2, 3... pour identifier l'accompagnant
            $table->string('status')->default('pending'); // pending, completed, expired
            $table->json('data')->nullable(); // Données du formulaire accompagnant
            $table->timestamp('expires_at'); // Expiration du QR code
            $table->timestamp('completed_at')->nullable(); // Date de soumission
            $table->timestamps();
            
            $table->index(['token', 'status']);
            $table->index(['reservation_id', 'accompaniment_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accompaniments');
    }
};
