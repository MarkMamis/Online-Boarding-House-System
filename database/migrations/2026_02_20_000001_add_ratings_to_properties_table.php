<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'average_rating')) {
                $table->decimal('average_rating', 2, 1)->default(0);
            }

            if (!Schema::hasColumn('properties', 'ratings_count')) {
                $table->unsignedInteger('ratings_count')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'average_rating')) {
                $table->dropColumn('average_rating');
            }

            if (Schema::hasColumn('properties', 'ratings_count')) {
                $table->dropColumn('ratings_count');
            }
        });
    }
};
