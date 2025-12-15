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
            $table->boolean('is_default')->default(false)->after('is_active');
            $table->string('manufacturer')->nullable()->after('name');
            $table->string('model')->nullable()->after('manufacturer');
            $table->string('location')->nullable()->after('model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            $table->dropColumn(['is_default', 'manufacturer', 'model', 'location']);
        });
    }
};
