<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'next_payment_due_date')) {
                $table->date('next_payment_due_date')->nullable()->after('payment_date');
            }

            if (!Schema::hasColumn('bookings', 'last_overdue_notified_at')) {
                $table->timestamp('last_overdue_notified_at')->nullable()->after('next_payment_due_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'last_overdue_notified_at')) {
                $table->dropColumn('last_overdue_notified_at');
            }

            if (Schema::hasColumn('bookings', 'next_payment_due_date')) {
                $table->dropColumn('next_payment_due_date');
            }
        });
    }
};
