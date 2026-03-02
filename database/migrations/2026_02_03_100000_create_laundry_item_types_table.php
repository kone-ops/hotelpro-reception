<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Types de linge définis par l'hôtel (draps, serviettes, etc.).
     */
    public function up(): void
    {
        Schema::create('laundry_item_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('name', 100);           // ex: Draps housse, Serviettes bain
            $table->string('code', 50)->nullable(); // ex: DRAP, SERV_BATH
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['hotel_id']);
            $table->unique(['hotel_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_item_types');
    }
};
