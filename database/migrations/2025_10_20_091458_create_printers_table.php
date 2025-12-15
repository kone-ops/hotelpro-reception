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
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de l'imprimante
            $table->string('ip_address'); // Adresse IP de l'imprimante
            $table->enum('type', ['ticket', 'a4']); // Type d'imprimante
            $table->string('module')->nullable(); // Module associé (caisse, reception, etc.)
            $table->boolean('is_active')->default(true); // Statut actif/inactif
            $table->text('description')->nullable(); // Description optionnelle
            $table->timestamps();
            
            $table->index(['module', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
