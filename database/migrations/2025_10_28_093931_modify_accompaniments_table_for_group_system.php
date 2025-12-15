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
        Schema::table('accompaniments', function (Blueprint $table) {
            // Supprimer les index qui dépendent des colonnes à supprimer
            $table->dropIndex(['reservation_id', 'accompaniment_number']);
        });
        
        Schema::table('accompaniments', function (Blueprint $table) {
            // Supprimer les colonnes qui ne sont plus nécessaires
            $table->dropColumn(['accompaniment_number', 'data', 'completed_at']);
            
            // Ajouter de nouvelles colonnes pour le système de groupe
            $table->string('group_token')->unique()->after('token'); // Token unique pour le groupe
            $table->json('accompaniments_data')->nullable()->after('group_token'); // Données de tous les accompagnants
            $table->integer('max_accompaniments')->default(0)->after('accompaniments_data'); // Nombre max d'accompagnants
            $table->integer('current_count')->default(0)->after('max_accompaniments'); // Nombre actuel d'accompagnants
            $table->boolean('is_active')->default(true)->after('current_count'); // Si le groupe est actif
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accompaniments', function (Blueprint $table) {
            // Supprimer les nouvelles colonnes
            $table->dropColumn([
                'group_token', 
                'accompaniments_data', 
                'max_accompaniments', 
                'current_count', 
                'is_active'
            ]);
            
            // Restaurer les colonnes supprimées
            $table->integer('accompaniment_number');
            $table->json('data')->nullable();
            $table->timestamp('completed_at')->nullable();
        });
        
        Schema::table('accompaniments', function (Blueprint $table) {
            // Restaurer l'index
            $table->index(['reservation_id', 'accompaniment_number']);
        });
    }
};