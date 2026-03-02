<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->text('out_of_service_reason')->nullable()->after('technical_state');
            $table->date('out_of_service_from')->nullable()->after('out_of_service_reason');
            $table->date('out_of_service_until')->nullable()->after('out_of_service_from');
            $table->foreignId('out_of_service_by')->nullable()->after('out_of_service_until')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['out_of_service_by']);
            $table->dropColumn([
                'out_of_service_reason',
                'out_of_service_from',
                'out_of_service_until',
                'out_of_service_by',
            ]);
        });
    }
};
