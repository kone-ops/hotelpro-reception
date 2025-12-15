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
            $table->foreignId('room_type_id')->nullable()->after('hotel_id')->constrained('room_types')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->after('room_type_id')->constrained('rooms')->nullOnDelete();
            
            // Dates de séjour pour gérer la disponibilité
            $table->date('check_in_date')->nullable()->after('data');
            $table->date('check_out_date')->nullable()->after('check_in_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_reservations', function (Blueprint $table) {
            $table->dropForeign(['room_type_id']);
            $table->dropForeign(['room_id']);
            $table->dropColumn(['room_type_id', 'room_id', 'check_in_date', 'check_out_date']);
        });
    }
};
