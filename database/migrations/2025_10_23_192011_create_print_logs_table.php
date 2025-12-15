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
        Schema::create('print_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('printer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('hotel_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type_document'); // recu_client, ticket_caisse, test, etc.
            $table->string('reference')->nullable(); // Référence du document
            $table->text('contenu'); // Contenu imprimé
            $table->enum('statut', ['en_attente', 'en_cours', 'succes', 'echec', 'annule'])->default('en_attente');
            $table->text('erreur')->nullable(); // Message d'erreur si échec
            $table->json('metadata')->nullable(); // Métadonnées supplémentaires
            $table->timestamp('debut_impression')->nullable();
            $table->timestamp('fin_impression')->nullable();
            $table->integer('tentatives')->default(0); // Nombre de tentatives
            $table->timestamps();
            
            // Index pour les performances
            $table->index(['printer_id', 'created_at']);
            $table->index(['statut', 'created_at']);
            $table->index(['hotel_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_logs');
    }
};
