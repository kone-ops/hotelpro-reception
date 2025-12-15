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
        Schema::table('clients', function (Blueprint $table) {
            // Champs pour les pièces d'identité
            $table->string('piece_identite_recto_path')->nullable()->after('profession');
            $table->string('piece_identite_verso_path')->nullable()->after('piece_identite_recto_path');
            $table->date('piece_identite_delivery_date')->nullable()->after('piece_identite_verso_path');
            $table->string('piece_identite_delivery_place')->nullable()->after('piece_identite_delivery_date');
            $table->json('piece_identite_ocr_data')->nullable()->after('piece_identite_delivery_place');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'piece_identite_recto_path',
                'piece_identite_verso_path',
                'piece_identite_delivery_date',
                'piece_identite_delivery_place',
                'piece_identite_ocr_data',
            ]);
        });
    }
};
