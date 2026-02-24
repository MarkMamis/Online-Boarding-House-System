<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('landlord_id')->constrained('users')->cascadeOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->date('leave_date');
            $table->text('reason')->nullable();
            $table->text('landlord_response')->nullable();
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['landlord_id', 'status']);
            $table->index(['booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
