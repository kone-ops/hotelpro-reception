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
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'new_reservation', 'check_in', 'check_out', 'reservation_validated', etc.
            $table->string('title');
            $table->text('message');
            $table->string('icon')->default('info'); // success, error, warning, info
            $table->string('color')->nullable(); // couleur personnalisée
            $table->morphs('notifiable'); // notifiable_type, notifiable_id (pour lier à reservation, etc.)
            $table->json('data')->nullable(); // données supplémentaires
            $table->string('action_url')->nullable(); // URL pour accéder à la ressource
            $table->string('action_text')->nullable(); // Texte du bouton d'action
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'read']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
