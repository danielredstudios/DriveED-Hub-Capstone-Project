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
        Schema::table('students', function (Blueprint $table) {
            $table->integer('verification_attempts')->default(0)->after('verification_code');
            $table->timestamp('last_verification_attempt_at')->nullable()->after('verification_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['verification_attempts', 'last_verification_attempt_at']);
        });
    }
};
