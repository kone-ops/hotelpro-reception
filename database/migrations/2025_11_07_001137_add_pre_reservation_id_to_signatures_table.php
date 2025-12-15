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
        Schema::table('signatures', function (Blueprint $table) {
            // Ajouter la colonne pre_reservation_id si elle n'existe pas
            if (!Schema::hasColumn('signatures', 'pre_reservation_id')) {
                $table->foreignId('pre_reservation_id')->after('id')->constrained('pre_reservations')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            if (Schema::hasColumn('signatures', 'pre_reservation_id')) {
                $table->dropForeign(['pre_reservation_id']);
                $table->dropColumn('pre_reservation_id');
            }
        });
    }
};
