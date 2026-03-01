<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create table if it somehow does not exist yet
        if (! Schema::hasTable('room_images')) {
            Schema::create('room_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
                $table->string('image_path');
                $table->string('label')->nullable();
                $table->unsignedInteger('sort_order')->nullable();
                $table->timestamps();
                $table->index(['room_id', 'sort_order']);
            });
        } else {
            // Table exists — just add the label column if missing
            if (! Schema::hasColumn('room_images', 'label')) {
                Schema::table('room_images', function (Blueprint $table) {
                    $table->string('label')->nullable()->after('image_path');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('room_images', 'label')) {
            Schema::table('room_images', function (Blueprint $table) {
                $table->dropColumn('label');
            });
        }
    }
};
