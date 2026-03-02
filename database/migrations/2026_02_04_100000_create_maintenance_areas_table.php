<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('category', 50); // espaces_publics, espaces_techniques, espaces_exterieurs, loisirs, administration
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('technical_state', 20)->default('normal'); // normal, issue, maintenance, out_of_service
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('maintenance_areas', function (Blueprint $table) {
            $table->index(['hotel_id', 'category']);
            $table->index(['hotel_id', 'technical_state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_areas');
    }
};
