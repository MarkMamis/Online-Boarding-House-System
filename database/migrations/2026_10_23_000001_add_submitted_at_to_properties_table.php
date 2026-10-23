<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('approval_status');
            }
        });

        // Safely backfill submitted_at for existing submitted/reviewed properties
        if (Schema::hasColumn('properties', 'submitted_at') && Schema::hasColumn('properties', 'created_at')) {
            DB::table('properties')
                ->whereIn('approval_status', ['approved', 'rejected', 'pending'])
                ->whereNull('submitted_at')
                ->update(['submitted_at' => DB::raw('created_at')]);
        }
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }
        });
    }
};
