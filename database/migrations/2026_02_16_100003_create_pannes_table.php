<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pannes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('panne_type_id')->constrained('panne_types')->cascadeOnDelete();
            $table->foreignId('panne_category_id')->constrained('panne_categories')->cascadeOnDelete();
            $table->string('location_type', 20); // room, area
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('maintenance_area_id')->nullable()->constrained('maintenance_areas')->nullOnDelete();
            $table->text('description');
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('reported_at');
            $table->string('status', 30)->default('signalée'); // signalée, en_cours, résolue
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('resolved_at')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();
        });

        Schema::table('pannes', function (Blueprint $table) {
            $table->index(['hotel_id', 'status']);
            $table->index(['room_id']);
            $table->index(['maintenance_area_id']);
            $table->index('reported_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pannes');
    }
};
