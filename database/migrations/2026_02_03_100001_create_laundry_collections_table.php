<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Collecte de linge (une par chambre / fin de nettoyage).
     */
    public function up(): void
    {
        Schema::create('laundry_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('housekeeping_task_id')->nullable()->constrained('housekeeping_tasks')->nullOnDelete();
            $table->timestamp('collected_at');
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50)->default('pending'); // pending, in_wash, done
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'status']);
            $table->index(['hotel_id', 'collected_at']);
            $table->index(['room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_collections');
    }
};
