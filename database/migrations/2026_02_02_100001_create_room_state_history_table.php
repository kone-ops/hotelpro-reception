<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Historique unifié des changements d'état des chambres (occupation, cleaning, technical).
     */
    public function up(): void
    {
        Schema::create('room_state_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->string('state_type', 20); // occupation, cleaning, technical
            $table->string('previous_value', 50)->nullable();
            $table->string('new_value', 50);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('service', 50)->nullable(); // reception, housekeeping, technical
            $table->text('notes')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index(['room_id', 'changed_at']);
            $table->index('state_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_state_history');
    }
};
