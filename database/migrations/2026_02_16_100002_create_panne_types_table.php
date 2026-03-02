<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panne_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('panne_category_id')->constrained('panne_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 80)->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::table('panne_types', function (Blueprint $table) {
            $table->index(['hotel_id', 'panne_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panne_types');
    }
};
