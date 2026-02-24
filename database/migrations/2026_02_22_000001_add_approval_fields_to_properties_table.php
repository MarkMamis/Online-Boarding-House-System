<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'approval_status')) {
                $approvalStatus = $table->string('approval_status')->default('pending');

                if (Schema::hasColumn('properties', 'image_path')) {
                    $approvalStatus->after('image_path');
                }
            }

            if (! Schema::hasColumn('properties', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approval_status');
            }

            if (! Schema::hasColumn('properties', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('properties', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_by');
            }

            if (! Schema::hasColumn('properties', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('properties', 'rejection_reason')) {
                $table->string('rejection_reason', 500)->nullable()->after('rejected_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }
            if (Schema::hasColumn('properties', 'rejected_by')) {
                $table->dropConstrainedForeignId('rejected_by');
            }

            $drop = [];
            foreach (['approval_status', 'approved_at', 'rejected_at', 'rejection_reason'] as $col) {
                if (Schema::hasColumn('properties', $col)) {
                    $drop[] = $col;
                }
            }
            if (! empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
