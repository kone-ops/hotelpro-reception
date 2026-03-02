<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_areas', function (Blueprint $table) {
            $table->foreignId('panne_category_id')->nullable()->after('notes')->constrained('panne_categories')->nullOnDelete();
            $table->foreignId('panne_type_id')->nullable()->after('panne_category_id')->constrained('panne_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_areas', function (Blueprint $table) {
            $table->dropForeign(['panne_category_id']);
            $table->dropForeign(['panne_type_id']);
        });
    }
};
