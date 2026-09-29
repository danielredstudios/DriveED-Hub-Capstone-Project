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
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE enrollment_requests MODIFY COLUMN payment_status ENUM('pending', 'on_hold', 'paid', 'partial', 'rejected', 'revision_required', 'cancelled') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE enrollment_requests MODIFY COLUMN payment_status ENUM('pending', 'on_hold', 'paid', 'partial', 'rejected', 'revision_required') NOT NULL DEFAULT 'pending'");
        }
    }
};
