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
        Schema::table('settings', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte unique sur 'key'
            $table->dropUnique(['key']);
            
            // Ajouter hotel_id
            $table->foreignId('hotel_id')->nullable()->after('id')->constrained('hotels')->cascadeOnDelete();
            
            // Créer une nouvelle contrainte unique sur la combinaison key + hotel_id
            $table->unique(['key', 'hotel_id']);
            $table->index(['hotel_id', 'group', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['key', 'hotel_id']);
            $table->dropIndex(['hotel_id', 'group', 'is_active']);
            $table->dropForeign(['hotel_id']);
            $table->dropColumn('hotel_id');
            $table->unique('key');
        });
    }
};
