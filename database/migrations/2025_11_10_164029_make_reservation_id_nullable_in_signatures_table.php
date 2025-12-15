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
            // Rendre reservation_id nullable si elle existe
            if (Schema::hasColumn('signatures', 'reservation_id')) {
                $table->unsignedBigInteger('reservation_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            // Remettre reservation_id NOT NULL
            if (Schema::hasColumn('signatures', 'reservation_id')) {
                $table->unsignedBigInteger('reservation_id')->nullable(false)->change();
            }
        });
    }
};
