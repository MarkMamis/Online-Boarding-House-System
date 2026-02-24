<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('room_number');
            $table->unsignedInteger('capacity')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('status', ['available','occupied','maintenance'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};