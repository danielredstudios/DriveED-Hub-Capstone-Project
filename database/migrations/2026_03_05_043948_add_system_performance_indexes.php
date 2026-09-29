<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['school_id', 'scheduled_at'], 'idx_reports_bookings_date');
            $table->index(['instructor_id', 'status'], 'idx_reports_instructor_stats');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['booking_id', 'status'], 'idx_reports_payments_status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_reports_bookings_date');
            $table->dropIndex('idx_reports_instructor_stats');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_reports_payments_status');
        });
    }
};
