<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panne_interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panne_id')->constrained('pannes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 50)->nullable(); // started, note, resolved
            $table->text('notes')->nullable();
            $table->dateTime('created_at');
        });

        Schema::table('panne_interventions', function (Blueprint $table) {
            $table->index(['panne_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panne_interventions');
    }
};
