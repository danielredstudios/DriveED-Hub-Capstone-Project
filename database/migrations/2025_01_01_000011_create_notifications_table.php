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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('notifiable_type'); // App\Models\Student, App\Models\Admin, App\Models\Instructor
            $table->unsignedBigInteger('notifiable_id');
            $table->string('type'); // enrollment_approved, enrollment_rejected, enrollment_received, session_reminder, etc.
            $table->string('title');
            $table->text('message');
            $table->string('icon')->default('info'); // info, success, warning, error
            $table->string('action_url')->nullable(); // URL to navigate to when clicked
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index(['school_id', 'notifiable_type', 'notifiable_id', 'read_at'], 'notif_school_user_read_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
