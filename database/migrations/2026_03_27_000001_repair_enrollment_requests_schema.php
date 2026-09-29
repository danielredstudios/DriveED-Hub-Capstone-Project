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
            // Add price for historical snapshotting
            if (!Schema::hasColumn('enrollment_requests', 'price')) {
                $table->decimal('price', 10, 2)->after('course_id')->default(0.00);
            }
            
            // Add payment tracking direct to request for legacy/simple tracking
            if (!Schema::hasColumn('enrollment_requests', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('enrollment_requests', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_method');
            }
            
            if (!Schema::hasColumn('enrollment_requests', 'payment_proof_path')) {
                $table->string('payment_proof_path')->nullable()->after('payment_reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_requests', function (Blueprint $table) {
            $table->dropColumn(['price', 'payment_method', 'payment_reference', 'payment_proof_path']);
        });
    }
};
