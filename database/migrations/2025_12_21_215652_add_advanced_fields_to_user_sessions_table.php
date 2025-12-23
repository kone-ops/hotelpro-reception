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
        Schema::table('user_sessions', function (Blueprint $table) {
            $table->string('device_name')->nullable()->after('user_agent');
            $table->string('device_type')->nullable()->after('device_name'); // desktop, mobile, tablet
            $table->string('browser')->nullable()->after('device_type');
            $table->string('platform')->nullable()->after('browser'); // Windows, macOS, Linux, iOS, Android
            $table->string('country')->nullable()->after('platform');
            $table->string('city')->nullable()->after('country');
            $table->string('region')->nullable()->after('city');
            $table->decimal('latitude', 10, 8)->nullable()->after('region');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->boolean('is_trusted_device')->default(false)->after('longitude');
            $table->string('device_fingerprint')->nullable()->after('is_trusted_device');
            $table->boolean('is_suspicious')->default(false)->after('device_fingerprint');
            $table->text('suspicious_reasons')->nullable()->after('is_suspicious'); // JSON array
            $table->timestamp('first_seen_at')->nullable()->after('suspicious_reasons');
            $table->timestamp('last_seen_at')->nullable()->after('first_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'device_name',
                'device_type',
                'browser',
                'platform',
                'country',
                'city',
                'region',
                'latitude',
                'longitude',
                'is_trusted_device',
                'device_fingerprint',
                'is_suspicious',
                'suspicious_reasons',
                'first_seen_at',
                'last_seen_at',
            ]);
        });
    }
};
