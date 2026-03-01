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
        Schema::create('room_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // student who submitted
            $table->unsignedTinyInteger('rating');  // 1-5 stars
            $table->text('comment');
            $table->string('display_name')->nullable(); // null = anonymous
            $table->timestamps();
            $table->unique(['room_id', 'user_id']); // one feedback per student per room
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_feedbacks');
    }
};
