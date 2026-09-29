<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->string('batch_group')->nullable()->after('notes');
            $table->unsignedInteger('batch_day_number')->nullable()->after('batch_group');
            $table->index(['batch_group', 'batch_day_number']);
        });
    }

    public function down(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropIndex(['batch_group', 'batch_day_number']);
            $table->dropColumn(['batch_group', 'batch_day_number']);
        });
    }
};