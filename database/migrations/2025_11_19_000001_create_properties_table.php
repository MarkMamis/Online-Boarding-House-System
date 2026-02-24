<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('address');
            $table->text('description')->nullable();
            $table->unsignedInteger('rooms_total')->default(0);
            $table->unsignedInteger('rooms_vacant')->default(0);
            $table->decimal('price_min', 10, 2)->nullable();
            $table->decimal('price_max', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};