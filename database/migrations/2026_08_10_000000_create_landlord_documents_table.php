<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landlord_documents', function (Blueprint $table) {
            $table->id();

            // landlord_id references the landlord User (consistent with
            // properties.landlord_id and the landlords/{user_id}/... storage layout).
            $table->foreignId('landlord_id')->constrained('users')->cascadeOnDelete();

            $table->string('document_type', 50);
            $table->string('document_number', 100)->nullable();
            $table->string('file_path');
            $table->date('date_issued')->nullable();
            $table->date('expiration_date')->nullable();

            // Administrative review state only. Expiration is calculated dynamically.
            $table->string('verification_status', 20)->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();

            $table->index('landlord_id');
            $table->index('document_type');
            $table->index('verification_status');
            $table->index('expiration_date');
            $table->index(['landlord_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landlord_documents');
    }
};
