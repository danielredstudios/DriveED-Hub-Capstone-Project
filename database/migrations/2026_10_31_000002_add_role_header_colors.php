<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            foreach (['admin', 'instructor', 'student'] as $role) {
                $table->string($role.'_header_bg', 7)->nullable();
                $table->string($role.'_header_text', 7)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            foreach (['admin', 'instructor', 'student'] as $role) {
                $table->dropColumn([$role.'_header_bg', $role.'_header_text']);
            }
        });
    }
};
