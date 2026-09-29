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
        Schema::create('payment_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->unsignedBigInteger('actor_id')->nullable()->index(); // Can be Admin or Student
            $table->string('action_type'); // 'submit', 'approve', 'reject', 'cancel', 'refund'
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('reason_code')->nullable();
            $table->text('reason_note')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['payment_id', 'to_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_status_logs');
    }
};
