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
        Schema::create('group_accompaniments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accompaniment_id')->constrained()->onDelete('cascade');
            $table->integer('position'); // Position dans le groupe (1, 2, 3...)
            $table->string('status')->default('pending'); // pending, completed, expired
            $table->json('data')->nullable(); // Données du formulaire
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['accompaniment_id', 'position']);
            $table->index(['accompaniment_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_accompaniments');
    }
};
