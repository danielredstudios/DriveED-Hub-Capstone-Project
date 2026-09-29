<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_id')->nullable()->change();
            $table->foreignId('enrollment_request_id')->after('booking_id')->nullable()->constrained('enrollment_requests')->onDelete('set null');
            $table->foreignId('branch_id')->after('school_id')->nullable()->constrained('branches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
            $table->dropForeign(['enrollment_request_id']);
            $table->dropColumn('enrollment_request_id');
            $table->unsignedBigInteger('booking_id')->nullable(false)->change();
        });
    }
};
