<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landlord_documents', function (Blueprint $table) {
            $table->boolean('is_current')->default(true)->after('rejected_at');
            $table->timestamp('superseded_at')->nullable()->after('is_current');

            $table->index(['landlord_id', 'document_type', 'is_current'], 'landlord_documents_current_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('landlord_documents', function (Blueprint $table) {
            $table->dropIndex('landlord_documents_current_lookup');
            $table->dropColumn(['is_current', 'superseded_at']);
        });
    }
};
