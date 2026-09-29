<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\School;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\ModuleLesson;

/**
 * Content & Progress Seeder
 *
 * Run AFTER UnifiedSeeder. Adds:
 *  - course_type / license_type / hours_required on existing courses
 *  - Course modules & lessons (content pipeline)
 *  - New students (enrolled, with active enrollment requests)
 *  - New instructors
 *  - Session completions (hour tracking)
 *  - Phase progression requests (theoretical -> practical workflow)
 *  - Progress records
 *
 * Run: php artisan db:seed --class=ContentProgressSeeder
 * All non-system seeded passwords: DEMO_SEED_PASSWORD (default: DriveDemo123)
 */
class ContentProgressSeeder extends Seeder
{
    private string $hashedPassword;

    private array $maleFirst = [
        'Alejandro', 'Bernardo', 'Claudio', 'Delfin', 'Emilio',
        'Fabian', 'Gaspar', 'Horacio', 'Ignacio', 'Jacinto',
        'Leandro', 'Marcelo', 'Narciso', 'Octavio', 'Prospero',
        'Quirino', 'Renato', 'Silvestre', 'Timoteo', 'Urbano',
        'Venancio', 'Wenceslao', 'Zaragoza', 'Amadeo', 'Basilio',
    ];

    private array $femaleFirst = [
        'Adoracion', 'Benilda', 'Ceferina', 'Dolores', 'Encarnacion',
        'Filomena', 'Generosa', 'Herminia', 'Iluminada', 'Juliana',
        'Leticia', 'Magdalena', 'Natividad', 'Olimpia', 'Perfecta',
        'Remedios', 'Soledad', 'Tranquilina', 'Visitacion', 'Wilhelmina',
        'Asuncion', 'Bienvenida', 'Catalina', 'Dominica', 'Estrella',
    ];

    private array $lastNames = [
        'Agoncillo', 'Bonifacio', 'Cojuangco', 'Dimaculangan', 'Escueta',
        'Fuentebella', 'Gatmaitan', 'Hontiveros', 'Ilustre', 'Jalandoni',
        'Karingal', 'Lacson', 'Mabini', 'Natividad', 'Osmena',
        'Palma', 'Quezon', 'Rizal', 'Sumulong', 'Tandang',
        'Urdaneta', 'Villamor', 'Zabarte', 'Araneta', 'Buencamino',
    ];

    public function run(): void
    {
        $demoPassword = (string) env('DEMO_SEED_PASSWORD', 'DriveDemo123');
        $this->hashedPassword = Hash::make($demoPassword);

        $this->command->info('');
        $this->command->info(str_repeat('=', 64));
        $this->command->info('  CONTENT & PROGRESS SEEDER - Modules, Sessions, Phases');
        $this->command->info(str_repeat('=', 64));
        $this->command->info('');

        // Load existing schools
        $lySpeed      = School::where('slug', 'lyspeed-driving')->first();
        $driveEdHub   = School::where('slug', 'drived-hub')->first();

        if (!$lySpeed || !$driveEdHub) {
            $this->command->error('Schools not found! Run UnifiedSeeder first.');
            return;
        }

        // 1. Fix course metadata
        $this->fixCourseMetadata();

        // 2. Seed course modules & lessons for all schools
        $this->seedCourseContent($lySpeed);
        $this->seedCourseContent($driveEdHub);

        // 3. Create new students + instructors, enrollments, sessions, phases per school
        $this->seedSchoolProgress($lySpeed, studentsPerBranch: 4, instructorsPerBranch: 1);
        $this->seedSchoolProgress($driveEdHub, studentsPerBranch: 3, instructorsPerBranch: 1);

        $this->command->info('');
        $this->command->info(str_repeat('=', 64));
        $this->command->info('  CONTENT & PROGRESS SEEDER COMPLETED!');
        $this->command->info(str_repeat('=', 64));
        $this->command->info('');
    }

    // ================================================================
    //  1. FIX COURSE METADATA
    // ================================================================

    private function fixCourseMetadata(): void
    {
        $this->command->info('Fixing course metadata (course_type, license_type, hours_required)...');

        $courses = Course::all();
        foreach ($courses as $course) {
            $updates = [];

            if (empty($course->course_type)) {
                $updates['course_type'] = strtolower($course->type) === 'theoretical' ? 'theoretical' : 'practical';
            }

            if (empty($course->license_type)) {
                $updates['license_type'] = 'non_professional';
            }

            if (empty($course->hours_required) || $course->hours_required == 0) {
                $maxHours = $course->packages()->max('training_hours');
                $courseType = $updates['course_type'] ?? $course->course_type;
                $defaultHours = ($courseType === 'theoretical') ? 15 : 10;
                $updates['hours_required'] = $maxHours ?: $defaultHours;
            }

            if (!empty($updates)) {
                $course->update($updates);
            }
        }

        $this->command->info('   -> ' . $courses->count() . ' courses updated');
    }

    // ================================================================
    //  2. COURSE MODULES & LESSONS
    // ================================================================

    private function seedCourseContent(School $school): void
    {
        $this->command->info('');
        $this->command->info("Seeding course content for {$school->name}...");

        $courses = Course::where('school_id', $school->id)->get();

        foreach ($courses as $course) {
            $isTheoretical = in_array(strtolower($course->type), ['theoretical']) ||
                             ($course->course_type === 'theoretical');

            $modules = $isTheoretical
                ? $this->getTheoreticalModules()
                : $this->getPracticalModules($course);

            foreach ($modules as $sortOrder => $moduleData) {
                $module = CourseModule::updateOrCreate(
                    ['school_id' => $school->id, 'course_id' => $course->id, 'title' => $moduleData['title']],
                    ['description' => $moduleData['description'], 'module_type' => $moduleData['module_type'], 'sort_order' => $sortOrder + 1]
                );

                foreach ($moduleData['lessons'] as $lSort => $lesson) {
                    ModuleLesson::updateOrCreate(
                        ['school_id' => $school->id, 'module_id' => $module->id, 'title' => $lesson['title']],
                        [
                            'content' => $lesson['content'],
                            'video_url' => $lesson['video_url'] ?? null,
                            'attachments' => $lesson['attachments'] ?? null,
                            'sort_order' => $lSort + 1,
                        ]
                    );
                }
            }
        }

        $moduleCount = CourseModule::where('school_id', $school->id)->count();
        $lessonCount = ModuleLesson::where('school_id', $school->id)->count();
        $this->command->info("   -> {$moduleCount} modules, {$lessonCount} lessons created");
    }

    // ================================================================
    //  3. NEW STUDENTS, INSTRUCTORS, ENROLLMENTS, SESSIONS, PHASES
    //     Uses DB::table() for all inserts to avoid Eloquent overhead
    // ================================================================

    private function seedSchoolProgress(School $school, int $studentsPerBranch, int $instructorsPerBranch): void
    {
        $this->command->info('');
        $this->command->info("Seeding progress data for {$school->name}...");

        $branches = DB::table('branches')->where('school_id', $school->id)->where('is_active', true)->get();
        $courses  = DB::table('courses')->where('school_id', $school->id)->get();
        $admins   = DB::table('admins')->where('school_id', $school->id)
                        ->whereIn('role', ['school_admin', 'branch_secretary'])->pluck('id')->toArray();

        if ($branches->isEmpty() || $courses->isEmpty() || empty($admins)) {
            $this->command->warn("   Skipping {$school->name} - no branches, courses, or admins");
            return;
        }

        $theoCourses  = $courses->filter(fn($c) => $c->course_type === 'theoretical' || strtolower($c->type) === 'theoretical')->values();
        $pracCourses  = $courses->filter(fn($c) => $c->course_type === 'practical' || strtolower($c->type) === 'practical')->values();

        $slug = $school->slug;
        $nameIdx = abs(crc32($slug)) % 25;
        $now = now()->format('Y-m-d H:i:s');

        // Collect all existing instructor IDs
        $existingInstructorIds = DB::table('instructors')->where('school_id', $school->id)->pluck('id')->toArray();

        // -- Create new instructors --
        $newInstructorCount = 0;
        foreach ($branches as $bIdx => $branch) {
            for ($j = 0; $j < $instructorsPerBranch; $j++) {
                $idx = ($nameIdx + $bIdx * $instructorsPerBranch + $j) % 25;
                $name = $this->maleFirst[$idx] . ' ' . $this->lastNames[$idx];
                $email = "cp.inst.{$bIdx}.{$j}@{$slug}.test";

                $exists = DB::table('instructors')->where('email', $email)->exists();
                if (!$exists) {
                    $id = DB::table('instructors')->insertGetId([
                        'school_id' => $school->id,
                        'branch_id' => $branch->id,
                        'name' => $name,
                        'email' => $email,
                        'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                        'password' => $this->hashedPassword,
                        'license_number' => 'LIC-CP-' . strtoupper(substr($slug, 0, 2)) . '-' . str_pad($bIdx * $instructorsPerBranch + $j + 1, 3, '0', STR_PAD_LEFT),
                        'bio' => "Instructor at {$school->name}, {$branch->name}.",
                        'status' => 'active',
                        'availability' => 'available',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $existingInstructorIds[] = $id;
                    $newInstructorCount++;
                }
            }
        }
        $this->command->info("   -> {$newInstructorCount} new instructors created");

        // -- Create new students --
        $studentEntries = [];
        foreach ($branches as $bIdx => $branch) {
            for ($j = 0; $j < $studentsPerBranch; $j++) {
                $idx = ($nameIdx + $bIdx * $studentsPerBranch + $j) % 25;
                $name = $this->femaleFirst[$idx] . ' ' . $this->lastNames[($idx + 5) % 25];
                $email = "cp.stu.{$bIdx}.{$j}@{$slug}.test";

                $existing = DB::table('students')->where('school_id', $school->id)->where('email', $email)->first();
                if ($existing) {
                    $studentEntries[] = ['id' => $existing->id, 'branch_id' => $branch->id];
                } else {
                    $daysAgo = rand(14, 90);
                    $id = DB::table('students')->insertGetId([
                        'school_id' => $school->id,
                        'branch_id' => $branch->id,
                        'name' => $name,
                        'email' => $email,
                        'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                        'password' => $this->hashedPassword,
                        'status' => 'active',
                        'role' => 'student',
                        'enrollment_date' => now()->subDays($daysAgo)->format('Y-m-d'),
                        'experience_level' => $j % 3 === 0 ? 'experienced' : 'new_driver',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $studentEntries[] = ['id' => $id, 'branch_id' => $branch->id];
                }
            }
        }
        $this->command->info("   -> " . count($studentEntries) . " new students created");

        // -- Distribute students into enrollment states --
        $enrollmentCount = 0;
        $sessionCount    = 0;
        $phaseCount      = 0;

        foreach ($studentEntries as $sIdx => $entry) {
            $studentId = $entry['id'];
            $branchId  = $entry['branch_id'];
            $state     = $sIdx % 4;
            $adminId   = $admins[array_rand($admins)];
            $instrId   = $existingInstructorIds[array_rand($existingInstructorIds)];
            $expLevel  = $sIdx % 3 === 0 ? 'experienced' : 'new_driver';

            $enrollDaysAgo = rand(14, 45);
            $enrolledAt = now()->subDays($enrollDaysAgo)->format('Y-m-d H:i:s');

            try {
                switch ($state) {
                    case 0: // COMPLETED THEORETICAL
                        $course = $theoCourses->isNotEmpty() ? $theoCourses->random() : $courses->random();
                        $hoursReq = (float) ($course->hours_required ?: 15);
                        $completedAt = now()->subDays(rand(3, 15))->format('Y-m-d H:i:s');
                        $passedAt = now()->subDays(rand(3, 15))->format('Y-m-d H:i:s');

                        $enrollId = DB::table('enrollment_requests')->insertGetId([
                            'school_id' => $school->id, 'branch_id' => $branchId,
                            'learner_id' => $studentId, 'course_id' => $course->id,
                            'status' => 'completed', 'payment_status' => 'paid',
                            'payment_confirmed_by' => $adminId, 'payment_confirmed_at' => $enrolledAt,
                            'experience_level' => $expLevel,
                            'requested_license_type' => $course->license_type ?? 'non_professional',
                            'approved_by' => $adminId, 'approved_at' => $enrolledAt,
                            'enrolled_at' => $enrolledAt, 'completed_at' => $completedAt,
                            'theoretical_passed' => true, 'theoretical_passed_at' => $passedAt,
                            'theoretical_passed_by' => $adminId,
                            'theoretical_pass_notes' => 'Passed theoretical assessment successfully.',
                            'created_at' => $now, 'updated_at' => $now,
                        ]);

                        DB::table('students')->where('id', $studentId)->update([
                            'has_passed_theoretical' => true,
                            'theoretical_passed_at' => $passedAt,
                            'student_license_status' => 'verified',
                            'student_license_verified_at' => now()->subDays(rand(3, 10))->format('Y-m-d H:i:s'),
                            'updated_at' => $now,
                        ]);

                        $sessionCount += $this->insertSessions($school->id, $enrollId, $instrId, 'theoretical', $hoursReq, $enrollDaysAgo);
                        $enrollmentCount++;

                        // Phase progression (theoretical -> practical)
                        $phaseStatus = ['pending', 'approved', 'approved'][$sIdx % 3];
                        DB::table('phase_progression_requests')->insert([
                            'enrollment_id' => $enrollId, 'school_id' => $school->id,
                            'from_phase' => 'theoretical', 'to_phase' => 'practical',
                            'requested_at' => now()->subDays(rand(1, 5))->format('Y-m-d H:i:s'),
                            'status' => $phaseStatus,
                            'reviewed_at' => $phaseStatus !== 'pending' ? now()->subDays(rand(0, 2))->format('Y-m-d H:i:s') : null,
                            'reviewed_by' => $phaseStatus !== 'pending' ? $adminId : null,
                            'admin_notes' => $phaseStatus !== 'pending' ? 'Approved. Student completed all theoretical hours.' : null,
                            'created_at' => $now, 'updated_at' => $now,
                        ]);
                        $phaseCount++;

                        DB::table('progresses')->updateOrInsert(
                            ['student_id' => $studentId, 'course_id' => $course->id],
                            ['school_id' => $school->id, 'completion_percent' => 100, 'last_updated' => $now, 'notes' => 'Theoretical course completed.', 'created_at' => $now, 'updated_at' => $now]
                        );
                        break;

                    case 1: // IN-PROGRESS THEORETICAL
                        $course = $theoCourses->isNotEmpty() ? $theoCourses->random() : $courses->random();
                        $hoursReq = (float) ($course->hours_required ?: 15);
                        $hoursDone = round($hoursReq * (rand(30, 70) / 100), 2);

                        $enrollId = DB::table('enrollment_requests')->insertGetId([
                            'school_id' => $school->id, 'branch_id' => $branchId,
                            'learner_id' => $studentId, 'course_id' => $course->id,
                            'status' => 'approved', 'payment_status' => 'paid',
                            'payment_confirmed_by' => $adminId, 'payment_confirmed_at' => $enrolledAt,
                            'experience_level' => $expLevel,
                            'requested_license_type' => $course->license_type ?? 'non_professional',
                            'approved_by' => $adminId, 'approved_at' => $enrolledAt,
                            'enrolled_at' => $enrolledAt,
                            'created_at' => $now, 'updated_at' => $now,
                        ]);

                        $licenseStatus = ['pending', 'verified', 'none'][rand(0, 2)];
                        $studentUpdate = [
                            'active_enrollment_id' => $enrollId,
                            'is_course_locked' => true,
                            'student_license_status' => $licenseStatus,
                            'updated_at' => $now,
                        ];
                        if ($licenseStatus === 'verified') {
                            $studentUpdate['student_license_verified_at'] = now()->subDays(rand(5, 20))->format('Y-m-d H:i:s');
                        }
                        DB::table('students')->where('id', $studentId)->update($studentUpdate);

                        $sessionCount += $this->insertSessions($school->id, $enrollId, $instrId, 'theoretical', $hoursDone, $enrollDaysAgo);
                        $enrollmentCount++;

                        $pct = round(($hoursDone / $hoursReq) * 100);
                        DB::table('progresses')->updateOrInsert(
                            ['student_id' => $studentId, 'course_id' => $course->id],
                            ['school_id' => $school->id, 'completion_percent' => min(95, $pct), 'last_updated' => $now, 'notes' => "In progress - {$hoursDone}/{$hoursReq} hours completed.", 'created_at' => $now, 'updated_at' => $now]
                        );
                        break;

                    case 2: // IN-PROGRESS PRACTICAL
                        $course = $pracCourses->isNotEmpty() ? $pracCourses->random() : $courses->random();
                        $hoursReq = (float) ($course->hours_required ?: 10);
                        $hoursDone = round($hoursReq * (rand(20, 60) / 100), 2);

                        $enrollId = DB::table('enrollment_requests')->insertGetId([
                            'school_id' => $school->id, 'branch_id' => $branchId,
                            'learner_id' => $studentId, 'course_id' => $course->id,
                            'status' => 'approved', 'payment_status' => 'paid',
                            'payment_confirmed_by' => $adminId, 'payment_confirmed_at' => $enrolledAt,
                            'experience_level' => $expLevel,
                            'requested_license_type' => $course->license_type ?? 'non_professional',
                            'approved_by' => $adminId, 'approved_at' => $enrolledAt,
                            'enrolled_at' => $enrolledAt,
                            'created_at' => $now, 'updated_at' => $now,
                        ]);

                        DB::table('students')->where('id', $studentId)->update([
                            'active_enrollment_id' => $enrollId,
                            'is_course_locked' => true,
                            'has_passed_theoretical' => true,
                            'theoretical_passed_at' => now()->subDays(rand(20, 60))->format('Y-m-d H:i:s'),
                            'student_license_status' => 'verified',
                            'student_license_verified_at' => now()->subDays(rand(15, 40))->format('Y-m-d H:i:s'),
                            'updated_at' => $now,
                        ]);

                        $sessionCount += $this->insertSessions($school->id, $enrollId, $instrId, 'practical', $hoursDone, $enrollDaysAgo);
                        $enrollmentCount++;

                        $pct = round(($hoursDone / $hoursReq) * 100);
                        DB::table('progresses')->updateOrInsert(
                            ['student_id' => $studentId, 'course_id' => $course->id],
                            ['school_id' => $school->id, 'completion_percent' => min(90, $pct), 'last_updated' => $now, 'notes' => "Practical training in progress - {$hoursDone}/{$hoursReq} hours.", 'created_at' => $now, 'updated_at' => $now]
                        );
                        break;

                    case 3: // COMPLETED PRACTICAL
                        $course = $pracCourses->isNotEmpty() ? $pracCourses->random() : $courses->random();
                        $hoursReq = (float) ($course->hours_required ?: 10);
                        $completedAt = now()->subDays(rand(1, 10))->format('Y-m-d H:i:s');

                        $enrollId = DB::table('enrollment_requests')->insertGetId([
                            'school_id' => $school->id, 'branch_id' => $branchId,
                            'learner_id' => $studentId, 'course_id' => $course->id,
                            'status' => 'completed', 'payment_status' => 'paid',
                            'payment_confirmed_by' => $adminId, 'payment_confirmed_at' => $enrolledAt,
                            'experience_level' => $expLevel,
                            'requested_license_type' => $course->license_type ?? 'non_professional',
                            'approved_by' => $adminId, 'approved_at' => $enrolledAt,
                            'enrolled_at' => $enrolledAt, 'completed_at' => $completedAt,
                            'created_at' => $now, 'updated_at' => $now,
                        ]);

                        DB::table('students')->where('id', $studentId)->update([
                            'has_passed_theoretical' => true,
                            'theoretical_passed_at' => now()->subDays(rand(30, 60))->format('Y-m-d H:i:s'),
                            'student_license_status' => 'verified',
                            'student_license_verified_at' => now()->subDays(rand(25, 50))->format('Y-m-d H:i:s'),
                            'updated_at' => $now,
                        ]);

                        $sessionCount += $this->insertSessions($school->id, $enrollId, $instrId, 'practical', $hoursReq, $enrollDaysAgo);
                        $enrollmentCount++;

                        DB::table('phase_progression_requests')->insert([
                            'enrollment_id' => $enrollId, 'school_id' => $school->id,
                            'from_phase' => 'practical', 'to_phase' => 'completed',
                            'requested_at' => now()->subDays(rand(2, 8))->format('Y-m-d H:i:s'),
                            'status' => 'approved',
                            'reviewed_at' => now()->subDays(rand(0, 2))->format('Y-m-d H:i:s'),
                            'reviewed_by' => $adminId,
                            'admin_notes' => 'Student completed all required practical driving hours.',
                            'created_at' => $now, 'updated_at' => $now,
                        ]);
                        $phaseCount++;

                        DB::table('progresses')->updateOrInsert(
                            ['student_id' => $studentId, 'course_id' => $course->id],
                            ['school_id' => $school->id, 'completion_percent' => 100, 'last_updated' => $now, 'notes' => 'Practical course completed. Ready for LTO examination.', 'created_at' => $now, 'updated_at' => $now]
                        );
                        break;
                }
            } catch (\Throwable $e) {
                $this->command->error("   Error on student #{$sIdx} (state {$state}): " . $e->getMessage());
                file_put_contents(storage_path('logs/seeder_error.log'), date('Y-m-d H:i:s') . " Student #{$sIdx}: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n", FILE_APPEND);
            }
        }

        $this->command->info("   -> {$enrollmentCount} enrollment requests created");
        $this->command->info("   -> {$sessionCount} session completion hours logged");
        $this->command->info("   -> {$phaseCount} phase progression requests created");
    }

    // ----------------------------------------------------------------
    //  HELPER: Insert session completion records using raw DB
    // ----------------------------------------------------------------

    private function insertSessions(int $schoolId, int $enrollmentId, int $instructorId, string $sessionType, float $totalHours, int $startDaysAgo): int
    {
        $remaining = $totalHours;
        $dayOffset = $startDaysAgo;
        $sessionNum = 0;
        $now = now()->format('Y-m-d H:i:s');

        $rows = [];
        while ($remaining > 0.01 && $sessionNum < 30) {
            $hours = min($remaining, rand(1, 2) + (rand(0, 1) * 0.5));
            $hours = round($hours, 2);

            $startHour = rand(8, 15);
            $startTime = sprintf('%02d:00:00', $startHour);
            $endHour = $startHour + (int) ceil($hours);
            $endTime = sprintf('%02d:00:00', min($endHour, 17));
            $sessionDate = now()->subDays($dayOffset)->format('Y-m-d');

            $rows[] = [
                'school_id' => $schoolId,
                'enrollment_id' => $enrollmentId,
                'instructor_id' => $instructorId,
                'session_type' => $sessionType,
                'hours_completed' => $hours,
                'session_date' => $sessionDate,
                'session_time' => $startTime,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'completed',
                'notes' => $this->getSessionNote($sessionType, $sessionNum),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $remaining -= $hours;
            $dayOffset -= rand(1, 3);
            if ($dayOffset < 0) $dayOffset = 0;
            $sessionNum++;
        }

        if (!empty($rows)) {
            foreach (array_chunk($rows, 10) as $chunk) {
                DB::table('session_completions')->insert($chunk);
            }
        }

        return $sessionNum;
    }

    // ----------------------------------------------------------------
    //  Session notes
    // ----------------------------------------------------------------

    private function getSessionNote(string $type, int $num): string
    {
        $theoreticalNotes = [
            'Covered traffic signs and signals. Student is attentive.',
            'Road rules discussion. Good participation in Q&A.',
            'Reviewed defensive driving principles.',
            'Practice quiz on traffic violations and penalties.',
            'Anti-distracted driving act discussion. Student takes detailed notes.',
            'LTO exam preparation. Student shows strong understanding.',
            'Final review session. Ready for assessment.',
        ];

        $practicalNotes = [
            'Vehicle familiarization and mirror adjustment. Student comfortable with controls.',
            'Practiced straight driving and basic turns. Good progress.',
            'Parking practice - perpendicular and parallel. Needs more work on parallel.',
            'City driving practice. Student handles intersections well.',
            'Hill start and incline parking. Improved clutch control.',
            'Highway driving introduction. Student maintains safe following distance.',
            'Night driving practice. Student adapts well to reduced visibility.',
            'Comprehensive practice route. Student is nearly test-ready.',
        ];

        $notes = $type === 'theoretical' ? $theoreticalNotes : $practicalNotes;
        return $notes[$num % count($notes)];
    }

    // ================================================================
    //  MODULE & LESSON CONTENT DEFINITIONS
    // ================================================================

    private function getTheoreticalModules(): array
    {
        return [
            [
                'title' => 'Introduction to Philippine Traffic Laws',
                'description' => 'Overview of Republic Act 4136, RA 10913, and other relevant traffic legislation.',
                'module_type' => 'theoretical',
                'lessons' => [
                    ['title' => 'History of Philippine Traffic Laws', 'content' => "This lesson covers the evolution of traffic legislation in the Philippines.\n\n## Key Laws\n- **RA 4136** - Land Transportation and Traffic Code\n- **RA 10913** - Anti-Distracted Driving Act\n- **RA 10586** - Anti-Drunk and Drugged Driving Act\n- **RA 11229** - Child Safety in Motor Vehicles Act\n\n## Learning Objectives\n1. Identify the major traffic laws\n2. Understand the penalties for common violations\n3. Know the rights and responsibilities of drivers"],
                    ['title' => 'LTO Rules and Regulations', 'content' => "The Land Transportation Office (LTO) is the primary government agency responsible for driver licensing.\n\n## License Categories\n- **Student Permit** - Valid for 1 year\n- **Non-Professional License** - For private vehicle use\n- **Professional License** - For public utility and commercial vehicles\n\n## Requirements\n1. Valid student permit\n2. TDC Certificate\n3. PDC Certificate\n4. Medical certificate\n5. Drug test clearance"],
                    ['title' => 'Penalties and Fines Schedule', 'content' => "Understanding penalties helps drivers maintain proper road behavior.\n\n## Common Violations and Fines\n| Violation | First Offense | Second Offense |\n|-----------|--------------|----------------|\n| No license | P3,000 | P5,000 |\n| Beating red light | P1,000 | P2,000 |\n| Over-speeding | P1,000-P2,000 | P2,000-P5,000 |", 'video_url' => 'https://www.youtube.com/watch?v=example_traffic_fines'],
                ],
            ],
            [
                'title' => 'Road Signs, Signals, and Markings',
                'description' => 'Complete guide to Philippine road signs, traffic signals, and pavement markings.',
                'module_type' => 'theoretical',
                'lessons' => [
                    ['title' => 'Regulatory Signs', 'content' => "Regulatory signs inform road users of traffic laws.\n\n## Categories\n### Prohibitory Signs\n- No Entry\n- No Left/Right Turn\n- No U-Turn\n- Speed Limit\n\n### Mandatory Signs\n- Keep Right/Left\n- Roundabout\n\nRegulatory signs MUST be obeyed."],
                    ['title' => 'Warning Signs', 'content' => "Warning signs alert drivers to potential hazards. They are typically diamond-shaped with a yellow background.\n\n## Common Warning Signs\n- Curve Ahead\n- Steep Grade\n- Slippery When Wet\n- Pedestrian Crossing\n- School Zone\n\nWhen you see a warning sign, reduce speed and be prepared."],
                    ['title' => 'Pavement Markings and Traffic Signals', 'content' => "## Pavement Markings\n- **Solid Yellow Line** - No overtaking zone\n- **Broken White Line** - Lane division, overtaking permitted\n- **Solid White Line** - Edge of road\n- **Crosswalk** - Pedestrian crossing area\n\n## Traffic Signals\n- **Green** - Proceed with caution\n- **Yellow** - Prepare to stop\n- **Red** - Full stop\n- **Flashing Red** - Treat as STOP sign", 'video_url' => 'https://www.youtube.com/watch?v=example_road_signs'],
                ],
            ],
            [
                'title' => 'Defensive Driving and Road Safety',
                'description' => 'Principles of defensive driving, hazard perception, and accident prevention.',
                'module_type' => 'theoretical',
                'lessons' => [
                    ['title' => 'Principles of Defensive Driving', 'content' => "Defensive driving means preventing accidents regardless of what other drivers do.\n\n## SIPDE Process\n1. **S**can - Check surroundings\n2. **I**dentify - Recognize hazards\n3. **P**redict - Anticipate behavior\n4. **D**ecide - Choose best response\n5. **E**xecute - Act on your decision\n\n## Smith System\n1. Aim high in steering\n2. Get the big picture\n3. Keep your eyes moving\n4. Leave yourself an out\n5. Make sure they see you"],
                    ['title' => 'Common Driving Hazards in the Philippines', 'content' => "Philippine roads present unique challenges.\n\n## Common Hazards\n- **Flooding** - Avoid driving through deep flood waters\n- **Jaywalkers** - Always watch for pedestrians\n- **Motorcycles weaving** - Check mirrors frequently\n- **Construction zones** - Reduce speed\n\n## Emergency Procedures\n1. Tire blowout: Grip steering, ease off gas\n2. Brake failure: Pump brakes, use engine braking\n3. Engine fire: Pull over, turn off engine, evacuate"],
                ],
            ],
        ];
    }

    private function getPracticalModules(object $course): array
    {
        $isMoto = stripos($course->title, 'motorcycle') !== false || ($course->vehicle_type ?? '') === 'Motorcycle';
        $isManual = stripos($course->title, 'manual') !== false;

        if ($isMoto) {
            return [
                [
                    'title' => 'Motorcycle Fundamentals',
                    'description' => 'Basic motorcycle components, safety gear, and pre-ride inspection.',
                    'module_type' => 'practical_prep',
                    'lessons' => [
                        ['title' => 'Motorcycle Components and Controls', 'content' => "Before riding, you must know every control.\n\n## Primary Controls\n- **Throttle** (right handlebar) - Engine speed\n- **Front Brake** (right hand lever) - 70% braking\n- **Rear Brake** (right foot pedal) - 30% braking\n- **Clutch** (left hand lever) - Transmission engagement\n- **Gear Shifter** (left foot) - 1 down, N, 2-5 up"],
                        ['title' => 'Safety Gear and Pre-Ride Inspection', 'content' => "ATGATT = All The Gear, All The Time.\n\n## Required Safety Gear\n1. DOT-approved helmet\n2. Riding jacket with armor\n3. Riding gloves\n4. Riding boots\n5. Riding pants\n\n## T-CLOCS Pre-Ride Check\n- **T** - Tires and wheels\n- **C** - Controls\n- **L** - Lights\n- **O** - Oil and fluids\n- **C** - Chassis\n- **S** - Stands"],
                    ],
                ],
                [
                    'title' => 'Basic Riding Skills',
                    'description' => 'Balance, slow-speed maneuvers, and basic riding techniques.',
                    'module_type' => 'practical_prep',
                    'lessons' => [
                        ['title' => 'Balance and Low-Speed Control', 'content' => "Mastering slow-speed balance is the foundation of confident riding.\n\n## Exercises\n1. Walking the bike\n2. Duck walking\n3. Friction zone practice\n4. Slow straight ride\n5. Figure-8s\n\n## Tips\n- Look where you want to go\n- Keep some throttle applied\n- Use rear brake for slow speed control"],
                        ['title' => 'Cornering and Lane Changes', 'content' => "## Cornering Process\n1. **Slow** - Reduce speed BEFORE the turn\n2. **Look** - Turn head through the turn\n3. **Press** - Counter-steer\n4. **Roll** - Roll on throttle through turn\n\n## Lane Changing\n1. Check mirrors\n2. Signal intention\n3. Head check blind spot\n4. Change lanes smoothly\n5. Cancel signal"],
                    ],
                ],
            ];
        }

        return [
            [
                'title' => 'Vehicle Familiarization',
                'description' => 'Understanding vehicle controls, dashboard indicators, and pre-drive checks.',
                'module_type' => 'practical_prep',
                'lessons' => [
                    ['title' => 'Vehicle Controls and Dashboard', 'content' => "Familiarize yourself with all vehicle controls before driving.\n\n## Primary Controls\n- **Steering Wheel** - Turn to steer\n- **Accelerator** (right pedal) - Controls speed\n- **Brake** (middle pedal) - Slows and stops\n" . ($isManual ? "- **Clutch** (left pedal) - Transmission engagement\n" : "- **Gear Selector** - P, R, N, D\n") . "\n## Dashboard Indicators\n- Red = Stop immediately\n- Yellow = Caution\n- Green/Blue = Information\n\n## Pre-Drive Routine\n1. Adjust seat and mirrors\n2. Fasten seatbelt\n3. Check mirrors\n4. Start engine"],
                    ['title' => 'Seat Position, Mirrors, and Safety', 'content' => "## Seat Adjustment\n- Feet reach all pedals comfortably\n- Knees slightly bent\n- Arms slightly bent at 9-and-3\n\n## Mirror Setup\n- Rearview: frame rear window\n- Left mirror: lean left, barely see car\n- Right mirror: lean right, barely see car\n\nAlways wear your seatbelt (RA 8750)."],
                ],
            ],
            [
                'title' => ($isManual ? 'Clutch Control and Gear Shifting' : 'Basic Driving Techniques'),
                'description' => $isManual
                    ? 'Master the friction zone, smooth gear changes, and hill starts.'
                    : 'Steering, braking, accelerating, and basic maneuvering.',
                'module_type' => 'practical_prep',
                'lessons' => $isManual ? [
                    ['title' => 'Understanding the Friction Zone', 'content' => "The friction zone is where the clutch begins to engage.\n\n## Finding It\n1. Press clutch fully\n2. Shift into 1st gear\n3. Slowly release clutch until car starts to pull\n4. That point = friction zone\n\n## Common Mistakes\n- Releasing clutch too fast = stall\n- Too much gas + slow clutch = excessive wear\n- Riding the clutch = premature wear"],
                    ['title' => 'Gear Shifting Patterns', 'content' => "## Upshifting\n1. Release accelerator\n2. Press clutch fully\n3. Move gear lever to next gear\n4. Release clutch smoothly with gas\n\n## When to Shift\n| Gear | Speed Range |\n|------|------------|\n| 1st | 0-15 km/h |\n| 2nd | 15-30 km/h |\n| 3rd | 30-45 km/h |\n| 4th | 45-60 km/h |\n| 5th | 60+ km/h |"],
                    ['title' => 'Hill Start Technique', 'content' => "## Handbrake Method\n1. Stop on hill with handbrake engaged\n2. Press clutch, shift to 1st\n3. Find friction zone\n4. Release handbrake while adding gas\n5. Fully release clutch once moving\n\n## Tips\n- Start on gentle inclines first\n- Use handbrake to prevent rolling"],
                ] : [
                    ['title' => 'Smooth Acceleration and Braking', 'content' => "## Acceleration\n- Press accelerator gradually\n- Maintain steady pressure for constant speed\n- Ease off before curves\n\n## Braking\n- Progressive braking: start light, increase, ease off before stop\n- Keep both hands on wheel\n- 3-second following distance rule"],
                    ['title' => 'Steering Techniques', 'content' => "## Hand Position\n- 9-and-3 position (recommended for airbag safety)\n- Keep both hands on wheel\n\n## Methods\n- Push-pull: Push up one hand, pull down other\n- Hand-over-hand: For tight turns\n\n## Avoid\n- One-hand driving\n- Palming the wheel\n- Over-correcting"],
                ],
            ],
            [
                'title' => 'On-Road Driving Skills',
                'description' => 'City driving, highway driving, parking, and real-world practice.',
                'module_type' => 'practical_prep',
                'lessons' => [
                    ['title' => 'City and Urban Driving', 'content' => "City driving requires constant awareness.\n\n## Key Skills\n- Intersection management (check left-right-left)\n- Stay centered in lane\n- 3+ seconds following distance\n- Check blind spots before lane changes\n\n## Common Hazards\n- Pedestrians crossing unexpectedly\n- Motorcycles splitting lanes\n- Buses stopping suddenly\n- Doors opening from parked cars"],
                    ['title' => 'Parking Techniques', 'content' => "## Perpendicular (90 degrees) Parking\n1. Signal and slow down\n2. Position 1m from parked cars\n3. When shoulder aligns with space, turn fully\n4. Straighten when centered\n\n## Parallel Parking\n1. Pull alongside car in front\n2. Reverse and turn toward curb\n3. Straighten briefly at 45 degrees\n4. Turn away from curb\n5. Straighten and center\n\n## Hill Parking\n- Uphill with curb: wheels LEFT\n- Downhill with curb: wheels RIGHT\n- Always engage parking brake", 'video_url' => 'https://www.youtube.com/watch?v=example_parking'],
                ],
            ],
        ];
    }
}
