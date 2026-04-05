<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'requires_advance_payment')) {
                $table->boolean('requires_advance_payment')->default(false)->after('inclusions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'requires_advance_payment')) {
                $table->dropColumn('requires_advance_payment');
            }
        });
    }
};
