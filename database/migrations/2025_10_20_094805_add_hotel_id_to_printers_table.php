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
        Schema::table('printers', function (Blueprint $table) {
            $table->foreignId('hotel_id')->nullable()->after('id')->constrained('hotels')->cascadeOnDelete();
            $table->index(['hotel_id', 'module', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->dropIndex(['hotel_id', 'module', 'is_active']);
            $table->dropColumn('hotel_id');
        });
    }
};
