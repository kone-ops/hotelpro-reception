<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Lignes de quantité par type de linge pour une collecte.
     */
    public function up(): void
    {
        Schema::create('laundry_collection_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laundry_collection_id')->constrained('laundry_collections')->cascadeOnDelete();
            $table->foreignId('laundry_item_type_id')->constrained('laundry_item_types')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();

            $table->unique(['laundry_collection_id', 'laundry_item_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_collection_lines');
    }
};
