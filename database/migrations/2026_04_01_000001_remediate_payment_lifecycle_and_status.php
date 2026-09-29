<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Expand payment_status enum in enrollment_requests
        // Note: Using DB::statement for enum expansion is often more reliable across different DB engines
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE enrollment_requests MODIFY COLUMN payment_status ENUM('pending', 'on_hold', 'paid', 'partial', 'rejected', 'revision_required') NOT NULL DEFAULT 'pending'");
        } else {
            // For SQLite or others during testing
            Schema::table('enrollment_requests', function (Blueprint $table) {
                $table->string('payment_status')->change();
            });
        }

        // 2. Normalize Payment Status and Received At axis
        // Target: paid/completed -> approved
        DB::table('payments')
            ->whereIn('status', ['paid', 'completed'])
            ->update(['status' => 'approved']);

        // Backfill received_at using paid_on or created_at fallback
        DB::table('payments')
            ->where('status', 'approved')
            ->whereNull('received_at')
            ->update([
                'received_at' => DB::raw('COALESCE(paid_on, created_at)')
            ]);

        // 3. Normalize Booking No-Show status mapping
        // Target: no_show -> no-show
        DB::table('bookings')
            ->where('status', 'no_show')
            ->update(['status' => 'no-show']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse backfill for received_at is non-destructive (keeping the data), 
        // but we can't easily revert status normalization without risk.
        // Reverting the enum requires care.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE enrollment_requests MODIFY COLUMN payment_status ENUM('pending', 'on_hold', 'paid') NOT NULL DEFAULT 'pending'");
        }
    }
};
