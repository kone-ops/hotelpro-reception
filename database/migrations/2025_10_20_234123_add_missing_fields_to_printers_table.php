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
            // Ajouter les champs manquants
            if (!Schema::hasColumn('printers', 'manufacturer')) {
                $table->string('manufacturer')->nullable()->after('name');
            }
            if (!Schema::hasColumn('printers', 'model')) {
                $table->string('model')->nullable()->after('manufacturer');
            }
            if (!Schema::hasColumn('printers', 'location')) {
                $table->string('location')->nullable()->after('model');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            $columns = ['manufacturer', 'model', 'location'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('printers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
