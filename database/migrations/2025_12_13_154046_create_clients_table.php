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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            
            // Identifiants pour la recherche (indexés)
            $table->string('email')->nullable()->index();
            $table->string('telephone')->nullable()->index();
            $table->string('numero_piece_identite')->nullable()->index();
            $table->string('type_piece_identite')->nullable();
            
            // Informations personnelles
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->string('sexe')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('nationalite')->nullable();
            
            // Coordonnées
            $table->text('adresse')->nullable();
            $table->string('profession')->nullable();
            
            // Métadonnées
            $table->integer('reservations_count')->default(0);
            $table->timestamp('last_reservation_at')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            
            $table->timestamps();
            
            // Index composite pour recherche rapide
            $table->index(['hotel_id', 'email']);
            $table->index(['hotel_id', 'telephone']);
            $table->index(['hotel_id', 'numero_piece_identite']);
            
            // Contraintes d'unicité par hôtel (un email/téléphone/numéro unique par hôtel)
            $table->unique(['hotel_id', 'email'], 'clients_hotel_email_unique');
            $table->unique(['hotel_id', 'telephone'], 'clients_hotel_telephone_unique');
            $table->unique(['hotel_id', 'numero_piece_identite'], 'clients_hotel_numero_piece_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
