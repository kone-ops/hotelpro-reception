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
        Schema::table('form_fields', function (Blueprint $table) {
            $table->unsignedTinyInteger('section')->default(2)->after('position')->comment('Section du formulaire: 0=Recherche, 1=Type, 2=Personnelles, 3=Coordonnées, 4=Séjour, 5=Validation, 6=Signature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropColumn('section');
        });
    }
};
