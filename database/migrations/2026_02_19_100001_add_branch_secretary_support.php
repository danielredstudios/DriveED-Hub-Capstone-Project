<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add branch_id to admins table (for branch secretaries)
        Schema::table('admins', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('school_id')
                ->constrained('branches')->nullOnDelete();
        });

        // Create student_action_requests table (secretary requests to add/remove students)
        Schema::create('student_action_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('admins')->cascadeOnDelete(); // secretary
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->enum('action', ['add', 'remove']); // add student to branch or remove from branch
            $table->text('reason')->nullable();
            $table->string('student_name')->nullable(); // for 'add' when student doesn't exist yet
            $table->string('student_email')->nullable(); // for 'add' when student doesn't exist yet
            $table->string('student_contact')->nullable(); // for 'add' when student doesn't exist yet
            $table->enum('status', ['pending', 'approved', 'denied'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete(); // central admin
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index('requested_by');
        });

        // Add payment confirmation tracking to enrollment_requests
        Schema::table('enrollment_requests', function (Blueprint $table) {
            $table->foreignId('payment_confirmed_by')->nullable()->after('payment_status')
                ->constrained('admins')->nullOnDelete();
            $table->timestamp('payment_confirmed_at')->nullable()->after('payment_confirmed_by');
            $table->text('payment_confirmation_notes')->nullable()->after('payment_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('enrollment_requests', function (Blueprint $table) {
            $table->dropForeign(['payment_confirmed_by']);
            $table->dropColumn(['payment_confirmed_by', 'payment_confirmed_at', 'payment_confirmation_notes']);
        });

        Schema::dropIfExists('student_action_requests');

        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
