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
        Schema::table('enrollment_requests', function (Blueprint $table) {
            $table->boolean('cancellation_requested')->default(false)->after('status');
            $table->text('cancellation_reason')->nullable()->after('cancellation_requested');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_requests', function (Blueprint $table) {
            $table->dropColumn(['cancellation_requested', 'cancellation_reason']);
        });
    }
};
