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
        Schema::create('tenant_onboardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'documents_uploaded', 'contract_signed', 'deposit_paid', 'completed'])->default('pending');
            $table->json('required_documents')->nullable(); // List of required documents
            $table->json('uploaded_documents')->nullable(); // Paths to uploaded documents
            $table->text('contract_content')->nullable();
            $table->boolean('contract_signed')->default(false);
            $table->timestamp('contract_signed_at')->nullable();
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->boolean('deposit_paid')->default(false);
            $table->timestamp('deposit_paid_at')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->string('digital_id')->nullable(); // Unique digital ID for tenant
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_onboardings');
    }
};
