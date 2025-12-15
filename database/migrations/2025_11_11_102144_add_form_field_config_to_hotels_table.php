<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajouter la configuration des champs du formulaire public
     */
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            // Configuration JSON pour les champs du formulaire
            $table->json('form_field_config')->nullable()->after('settings');
            
            /*
             * Structure attendue :
             * {
             *   "signature": {"visible": true, "required": true},
             *   "identity_document": {"visible": true, "required": false},
             *   "identity_document_front": {"visible": true, "required": false},
             *   "identity_document_back": {"visible": true, "required": false},
             *   "document_delivery_date": {"visible": true, "required": false},
             *   "document_delivery_place": {"visible": true, "required": false},
             *   "photo_identity": {"visible": true, "required": false},
             *   "profession": {"visible": true, "required": true},
             *   "address": {"visible": true, "required": false},
             *   "nationality": {"visible": true, "required": true}
             * }
             */
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('form_field_config');
        });
    }
};
