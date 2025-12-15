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
        Schema::table('pre_reservations', function (Blueprint $table) {
            // Ajouter les colonnes pour les chambres et dates
            if (!Schema::hasColumn('pre_reservations', 'room_type_id')) {
                $table->foreignId('room_type_id')->nullable()->after('hotel_id')->constrained('room_types')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('pre_reservations', 'room_id')) {
                $table->foreignId('room_id')->nullable()->after('room_type_id')->constrained('rooms')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('pre_reservations', 'check_in_date')) {
                $table->date('check_in_date')->nullable()->after('room_id');
            }
            
            if (!Schema::hasColumn('pre_reservations', 'check_out_date')) {
                $table->date('check_out_date')->nullable()->after('check_in_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('pre_reservations', 'check_out_date')) {
                $table->dropColumn('check_out_date');
            }
            
            if (Schema::hasColumn('pre_reservations', 'check_in_date')) {
                $table->dropColumn('check_in_date');
            }
            
            if (Schema::hasColumn('pre_reservations', 'room_id')) {
                $table->dropForeign(['room_id']);
                $table->dropColumn('room_id');
            }
            
            if (Schema::hasColumn('pre_reservations', 'room_type_id')) {
                $table->dropForeign(['room_type_id']);
                $table->dropColumn('room_type_id');
            }
        });
    }
};
