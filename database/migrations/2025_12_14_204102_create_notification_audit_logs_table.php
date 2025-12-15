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
        Schema::create('notification_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('viewed_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('notification_id')->nullable()->constrained('user_notifications')->onDelete('cascade');
            $table->string('action'); // 'view', 'list_view', etc.
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();
            
            $table->index(['viewer_id', 'viewed_user_id']);
            $table->index('viewed_at');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_audit_logs');
    }
};
