<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_linen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('source', 20); // reception | room
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->unsignedBigInteger('housekeeping_task_id')->nullable();
            $table->timestamp('received_at');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending_pickup'); // pending_pickup | at_laundry | ready_for_pickup | picked_up | sent_to_laundry
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('client_name', 255)->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->foreignId('picked_up_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('client_linen', function (Blueprint $table) {
            $table->foreign('housekeeping_task_id')->references('id')->on('housekeeping_tasks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_linen');
    }
};
