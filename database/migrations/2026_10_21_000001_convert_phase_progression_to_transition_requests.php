<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase Progression is now submitted by the student, not generated in the
 * background by the hour counter.
 *
 * A student who holds a Bundle/Combo enrollment uploads their student
 * driver's license from the dashboard. That upload opens a transition
 * request here, which the school admin confirms from the Enrollment
 * Request page. Confirming it unlocks the practical phase.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phase_progression_requests', function (Blueprint $table) {
            // 'license' replaces the old automatic hour-count trigger.
            if (!Schema::hasColumn('phase_progression_requests', 'trigger')) {
                $table->string('trigger', 32)->default('license')->after('to_phase');
            }

            // Pop-up submission metadata captured from the student dashboard.
            if (!Schema::hasColumn('phase_progression_requests', 'submitted_by')) {
                $table->foreignId('submitted_by')->nullable()->after('requested_at')
                    ->constrained('students')->onDelete('set null');
            }

            if (!Schema::hasColumn('phase_progression_requests', 'license_path')) {
                $table->string('license_path')->nullable()->after('submitted_by');
            }

            if (!Schema::hasColumn('phase_progression_requests', 'student_notes')) {
                $table->text('student_notes')->nullable()->after('license_path');
            }
        });

        // The old enum only permitted theoretical/practical as a source phase.
        // Combo enrollments transition "combo" -> "practical".
        $this->widenPhaseColumns();
    }

    public function down(): void
    {
        Schema::table('phase_progression_requests', function (Blueprint $table) {
            foreach (['student_notes', 'license_path', 'trigger'] as $column) {
                if (Schema::hasColumn('phase_progression_requests', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('phase_progression_requests', 'submitted_by')) {
                $table->dropConstrainedForeignId('submitted_by');
            }
        });
    }

    /**
     * ENUMs are painful to alter across engines, so store the phase columns
     * as plain strings which accept theoretical / practical / combo / completed.
     */
    private function widenPhaseColumns(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE phase_progression_requests MODIFY from_phase VARCHAR(32) NOT NULL");
            DB::statement("ALTER TABLE phase_progression_requests MODIFY to_phase VARCHAR(32) NOT NULL");

            return;
        }

        // SQLite / Postgres: no native enum on the column, nothing to widen.
    }
};
