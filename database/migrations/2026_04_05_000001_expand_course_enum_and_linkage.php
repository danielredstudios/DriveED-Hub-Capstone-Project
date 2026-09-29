<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // Expand course_type enum for MySQL-backed deployments.
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE courses MODIFY course_type ENUM('theoretical', 'practical', 'combo') NOT NULL DEFAULT 'theoretical'");
        }

        Schema::table('time_slots', function (Blueprint $table) {
            if (!Schema::hasColumn('time_slots', 'session_type')) {
                $table->enum('session_type', ['theoretical', 'practical'])
                    ->nullable()
                    ->after('course_id');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'enrollment_request_id')) {
                $table->foreignId('enrollment_request_id')
                    ->nullable()
                    ->after('course_id')
                    ->constrained('enrollment_requests')
                    ->nullOnDelete();
            }
        });

        // Legacy backfill from joined course metadata.
        if (Schema::hasColumn('time_slots', 'session_type')) {
            if ($driver === 'mysql') {
                DB::statement("UPDATE time_slots ts LEFT JOIN courses c ON c.id = ts.course_id SET ts.session_type = CASE WHEN c.course_type = 'practical' THEN 'practical' ELSE 'theoretical' END WHERE ts.session_type IS NULL");
                DB::statement("UPDATE time_slots SET session_type = 'theoretical' WHERE session_type IS NULL");
                DB::statement("ALTER TABLE time_slots MODIFY session_type ENUM('theoretical', 'practical') NOT NULL DEFAULT 'theoretical'");
            } else {
                $rows = DB::table('time_slots as ts')
                    ->leftJoin('courses as c', 'c.id', '=', 'ts.course_id')
                    ->whereNull('ts.session_type')
                    ->select('ts.id', 'c.course_type')
                    ->get();

                foreach ($rows as $row) {
                    DB::table('time_slots')
                        ->where('id', $row->id)
                        ->update([
                            'session_type' => ($row->course_type === 'practical') ? 'practical' : 'theoretical',
                        ]);
                }

                DB::table('time_slots')
                    ->whereNull('session_type')
                    ->update(['session_type' => 'theoretical']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if (Schema::hasColumn('bookings', 'enrollment_request_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['enrollment_request_id']);
                $table->dropColumn('enrollment_request_id');
            });
        }

        if (Schema::hasColumn('time_slots', 'session_type')) {
            Schema::table('time_slots', function (Blueprint $table) {
                $table->dropColumn('session_type');
            });
        }

        if ($driver === 'mysql') {
            DB::statement("UPDATE courses SET course_type = 'theoretical' WHERE course_type = 'combo'");
            DB::statement("ALTER TABLE courses MODIFY course_type ENUM('theoretical', 'practical') NOT NULL DEFAULT 'theoretical'");
        }
    }
};
