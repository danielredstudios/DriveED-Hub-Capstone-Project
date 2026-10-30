<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('session_completions', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->unique()->constrained('bookings')->nullOnDelete();
            $table->decimal('session_grade', 5, 2)->nullable();
            $table->string('attendance_status', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('session_completions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('booking_id');
            $table->dropColumn(['session_grade', 'attendance_status']);
        });
    }
};
