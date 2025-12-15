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
            $table->enum('connection_status', ['online', 'offline', 'checking'])->default('checking')->after('is_active');
            $table->timestamp('last_checked_at')->nullable()->after('connection_status');
            $table->integer('response_time_ms')->nullable()->after('last_checked_at')->comment('Temps de réponse en millisecondes');
            $table->integer('failed_checks_count')->default(0)->after('response_time_ms')->comment('Nombre de vérifications échouées consécutives');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            $table->dropColumn(['connection_status', 'last_checked_at', 'response_time_ms', 'failed_checks_count']);
        });
    }
};
