<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollment_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollment_requests', 'requested_dl_code')) {
                $table->string('requested_dl_code')->nullable()->after('requested_license_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrollment_requests', function (Blueprint $table) {
            $table->dropColumn('requested_dl_code');
        });
    }
};
