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
        Schema::table('identity_documents', function (Blueprint $table) {
            $table->string('lieu_delivrance')->nullable()->after('type');
            $table->date('date_delivrance')->nullable()->after('lieu_delivrance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identity_documents', function (Blueprint $table) {
            $table->dropColumn(['lieu_delivrance', 'date_delivrance']);
        });
    }
};
