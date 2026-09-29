<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoCleanAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $demoPassword = (string) env('DEMO_SEED_PASSWORD', 'DriveDemo123');
        $hashedPassword = Hash::make($demoPassword);

        $demoSchools = [
            'lyspeed-driving' => ['domain' => 'lyspeed.test', 'label' => 'LySpeed'],
            'drived-hub' => ['domain' => 'drivedhub.test', 'label' => 'DriveED Hub'],
        ];

        $schools = School::whereIn('slug', array_keys($demoSchools))->get()->keyBy('slug');

        if ($schools->isEmpty()) {
            $this->command->warn('No demo schools found. Skipping clean demo account seeding.');
            return;
        }

        foreach ($demoSchools as $slug => $meta) {
            $school = $schools->get($slug);

            if (!$school) {
                $this->command->warn("Demo school {$slug} not found. Skipping.");
                continue;
            }

            $branchId = DB::table('branches')
                ->where('school_id', '=', $school->id)
                ->orderBy('id')
                ->value('id');

            if (!$branchId) {
                $this->command->warn("No branch found for {$slug}. Skipping clean account creation.");
                continue;
            }

            for ($i = 1; $i <= 15; $i++) {
                $this->seedLearnerAccount(
                    schoolId: $school->id,
                    branchId: $branchId,
                    email: "guest{$i}@{$meta['domain']}",
                    name: "{$meta['label']} Guest {$i}",
                    role: 'guest',
                    password: $hashedPassword,
                    contactSuffix: $i
                );

                $this->seedLearnerAccount(
                    schoolId: $school->id,
                    branchId: $branchId,
                    email: "student{$i}@{$meta['domain']}",
                    name: "{$meta['label']} Student {$i}",
                    role: 'student',
                    password: $hashedPassword,
                    contactSuffix: $i + 100
                );

                $this->seedInstructorAccount(
                    schoolId: $school->id,
                    branchId: $branchId,
                    email: "instructor{$i}@{$meta['domain']}",
                    name: "{$meta['label']} Instructor {$i}",
                    password: $hashedPassword,
                    sequence: $i
                );
            }

            // Safety pass: ensure every guest in this demo school is clean, including legacy guest seeds.
            $resetGuests = $this->purgeAllGuestAccountsForSchool($school->id, $branchId);
            $this->command->info("   -> {$resetGuests} guest account(s) reset to no-enrollment state for {$slug}");

            $courseIds = $this->resolveDemoCourseIds($school->id);
            $timeSlots = $this->ensureDemoSchedules(
                schoolId: $school->id,
                branchId: $branchId,
                slug: $slug,
                tdcCourseId: $courseIds['tdc'],
                pdcCourseId: $courseIds['pdc']
            );

            $instructorIds = $this->getDemoInstructorIds($school->id, $meta['domain']);
            $this->assignInstructorsToSchedules($school->id, $instructorIds, $timeSlots);

            $studentIds = $this->getDemoStudentIds($school->id, $meta['domain']);
            [$enrolledCount, $scheduledCount] = $this->enrollDemoStudents(
                schoolId: $school->id,
                branchId: $branchId,
                studentIds: $studentIds,
                timeSlots: $timeSlots,
                tdcCourseId: $courseIds['tdc'],
                pdcCourseId: $courseIds['pdc']
            );
            $seededVerifiedSessions = $this->seedVerifiedSessionSample($school->id, $meta['domain']);

            $this->command->info("   -> {$enrolledCount} student account(s) enrolled to TDC/PDC for {$slug}");
            $this->command->info("   -> {$scheduledCount} student account(s) given schedules for {$slug}");
            $this->command->info("   -> {$seededVerifiedSessions} verified session sample(s) seeded for {$slug}");
            $this->command->info("   -> " . count($instructorIds) . " instructor account(s) assigned to schedules for {$slug}");
        }

        $this->command->info('   ✓ Clean demo accounts ready: guest1-15, student1-15, instructor1-15 per demo school');
    }

    private function seedLearnerAccount(
        int $schoolId,
        int $branchId,
        string $email,
        string $name,
        string $role,
        string $password,
        int $contactSuffix
    ): void {
        $account = Student::updateOrCreate(
            ['school_id' => $schoolId, 'email' => $email],
            [
                'branch_id' => $branchId,
                'name' => $name,
                'contact' => '+63-900-000-' . str_pad((string) $contactSuffix, 4, '0', STR_PAD_LEFT),
                'password' => $password,
                'status' => 'active',
                'student_license_status' => 'none',
                'student_license_path' => null,
                'student_license_data' => null,
                'student_license_mime_type' => null,
                'student_license_filename' => null,
                'student_license_verified_at' => null,
                'student_license_verified_by' => null,
                'student_license_rejection_reason' => null,
                'has_passed_theoretical' => false,
                'theoretical_passed_at' => null,
                'enrollment_date' => now(),
            ]
        );

        $account->role = $role;
        $account->email_verified_at = $account->email_verified_at ?? now();
        $account->verification_code = null;
        $account->verification_code_expires_at = null;
        $account->verification_attempts = 0;
        $account->last_verification_attempt_at = null;
        $account->save();

        $this->purgeLearnerData($account->id);
        DB::table('students')
            ->where('id', '=', $account->id)
            ->update([
                'active_enrollment_id' => null,
                'is_course_locked' => false,
            ]);
    }

    private function seedInstructorAccount(
        int $schoolId,
        int $branchId,
        string $email,
        string $name,
        string $password,
        int $sequence
    ): void {
        $instructor = Instructor::updateOrCreate(
            ['school_id' => $schoolId, 'email' => $email],
            [
                'branch_id' => $branchId,
                'name' => $name,
                'contact' => '+63-901-000-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                'password' => $password,
                'status' => 'active',
                'availability' => 'available',
                'license_number' => 'DEMO-INST-' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                'bio' => 'Clean demo instructor account with no seeded activities.',
            ]
        );

        $this->purgeInstructorData($instructor->id);
    }

    private function purgeLearnerData(int $studentId): void
    {
        DB::table('notifications')
            ->where('notifiable_type', '=', Student::class)
            ->where('notifiable_id', '=', $studentId)
            ->delete();

        DB::table('payments')->where('payer_user_id', '=', $studentId)->delete();
        DB::table('bookings')->where('student_id', '=', $studentId)->delete();
        DB::table('progresses')->where('student_id', '=', $studentId)->delete();
        DB::table('enrollments')->where('student_id', '=', $studentId)->delete();
        DB::table('enrollment_requests')->where('learner_id', '=', $studentId)->delete();
    }

    private function purgeInstructorData(int $instructorId): void
    {
        DB::table('notifications')
            ->where('notifiable_type', '=', Instructor::class)
            ->where('notifiable_id', '=', $instructorId)
            ->delete();

        DB::table('instructor_removal_requests')->where('instructor_id', '=', $instructorId)->delete();
        DB::table('schedule_instructors')->where('instructor_id', '=', $instructorId)->delete();
        DB::table('session_completions')->where('instructor_id', '=', $instructorId)->delete();
        DB::table('session_completions')->where('logged_by', '=', $instructorId)->delete();
        DB::table('bookings')->where('instructor_id', '=', $instructorId)->update(['instructor_id' => null]);
    }

    private function purgeAllGuestAccountsForSchool(int $schoolId, int $branchId): int
    {
        $guestIds = Student::query()
            ->where('school_id', '=', $schoolId)
            ->where('role', '=', 'guest')
            ->pluck('id');

        foreach ($guestIds as $guestId) {
            $this->purgeLearnerData((int) $guestId);
        }

        if ($guestIds->isNotEmpty()) {
            DB::table('students')
                ->whereIn('id', $guestIds->all())
                ->update([
                    'branch_id' => $branchId,
                    'active_enrollment_id' => null,
                    'is_course_locked' => false,
                    'student_license_status' => 'none',
                    'student_license_path' => null,
                    'student_license_data' => null,
                    'student_license_mime_type' => null,
                    'student_license_filename' => null,
                    'student_license_verified_at' => null,
                    'student_license_verified_by' => null,
                    'student_license_rejection_reason' => null,
                    'has_passed_theoretical' => false,
                    'theoretical_passed_at' => null,
                ]);
        }

        return $guestIds->count();
    }

    private function resolveDemoCourseIds(int $schoolId): array
    {
        $courses = DB::table('courses')
            ->where('school_id', '=', $schoolId)
            ->orderBy('id')
            ->get(['id', 'title', 'type', 'course_type']);

        if ($courses->isEmpty()) {
            return ['tdc' => null, 'pdc' => null];
        }

        $tdcCourse = $courses->first(function ($course) {
            $type = strtolower(trim((string) ($course->type ?? '')));
            $courseType = strtolower(trim((string) ($course->course_type ?? '')));
            $title = strtolower(trim((string) ($course->title ?? '')));

            return $type === 'theoretical'
                || $courseType === 'theoretical'
                || str_contains($title, 'tdc')
                || str_contains($title, 'theoretical');
        });

        $pdcCourse = $courses->first(function ($course) {
            $type = strtolower(trim((string) ($course->type ?? '')));
            $courseType = strtolower(trim((string) ($course->course_type ?? '')));
            $title = strtolower(trim((string) ($course->title ?? '')));

            return $type === 'practical'
                || $courseType === 'practical'
                || str_contains($title, 'pdc')
                || str_contains($title, 'practical');
        });

        if (!$pdcCourse && $tdcCourse) {
            $pdcCourse = $courses->first(fn ($course) => (int) $course->id !== (int) $tdcCourse->id);
        }

        if (!$tdcCourse && $pdcCourse) {
            $tdcCourse = $courses->first(fn ($course) => (int) $course->id !== (int) $pdcCourse->id) ?? $pdcCourse;
        }

        if (!$tdcCourse && !$pdcCourse) {
            $tdcCourse = $courses->first();
        }

        return [
            'tdc' => $tdcCourse?->id,
            'pdc' => $pdcCourse?->id,
        ];
    }

    private function ensureDemoSchedules(
        int $schoolId,
        int $branchId,
        string $slug,
        ?int $tdcCourseId,
        ?int $pdcCourseId
    ): array {
        $requiredSlots = 15;
        $marker = "demo-clean-slot:{$slug}";

        $existingSlotIds = DB::table('time_slots')
            ->where('school_id', '=', $schoolId)
            ->where('branch_id', '=', $branchId)
            ->where('notes', 'like', "{$marker}%")
            ->pluck('id');

        if ($existingSlotIds->isNotEmpty()) {
            // Remove old demo slots and related demo slot bookings so regenerated schedules stay consistent.
            DB::table('bookings')->whereIn('time_slot_id', $existingSlotIds->all())->delete();
            DB::table('schedule_instructors')->whereIn('time_slot_id', $existingSlotIds->all())->delete();
            DB::table('time_slots')->whereIn('id', $existingSlotIds->all())->delete();
        }

        $timeWindows = [
            ['08:00:00', '09:00:00'],
            ['09:00:00', '10:00:00'],
            ['10:00:00', '11:00:00'],
            ['13:00:00', '14:00:00'],
            ['14:00:00', '15:00:00'],
        ];

        $now = now();

        for ($i = 0; $i < $requiredSlots; $i++) {
            $courseId = $this->resolveDemoCourseId($i, $tdcCourseId, $pdcCourseId);
            if (!$courseId) {
                continue;
            }

            $window = $timeWindows[$i % count($timeWindows)];
            $date = now()->addDays(1 + intdiv($i, count($timeWindows)))->toDateString();

            DB::table('time_slots')->insert([
                'school_id' => $schoolId,
                'branch_id' => $branchId,
                'course_id' => $courseId,
                'date' => $date,
                'start_time' => $window[0],
                'end_time' => $window[1],
                'status' => 'open',
                'max_instructors' => 1,
                'notes' => "{$marker}:" . ($i + 1),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return DB::table('time_slots')
            ->where('school_id', '=', $schoolId)
            ->where('branch_id', '=', $branchId)
            ->where('notes', 'like', "{$marker}%")
            ->orderBy('date')
            ->orderBy('start_time')
            ->get(['id', 'course_id', 'date', 'start_time', 'end_time'])
            ->all();
    }

    private function resolveDemoCourseId(int $index, ?int $tdcCourseId, ?int $pdcCourseId): ?int
    {
        if ($tdcCourseId && $pdcCourseId) {
            return $index % 2 === 0 ? $tdcCourseId : $pdcCourseId;
        }

        return $tdcCourseId ?: $pdcCourseId;
    }

    private function getDemoInstructorIds(int $schoolId, string $domain): array
    {
        $ids = [];
        for ($i = 1; $i <= 15; $i++) {
            $id = Instructor::query()
                ->where('school_id', '=', $schoolId)
                ->where('email', '=', "instructor{$i}@{$domain}")
                ->value('id');

            if ($id) {
                $ids[] = (int) $id;
            }
        }

        return $ids;
    }

    private function getDemoStudentIds(int $schoolId, string $domain): array
    {
        $ids = [];
        for ($i = 1; $i <= 15; $i++) {
            $id = Student::query()
                ->where('school_id', '=', $schoolId)
                ->where('email', '=', "student{$i}@{$domain}")
                ->value('id');

            if ($id) {
                $ids[] = (int) $id;
            }
        }

        return $ids;
    }

    private function assignInstructorsToSchedules(int $schoolId, array $instructorIds, array $timeSlots): void
    {
        if (empty($instructorIds) || empty($timeSlots)) {
            return;
        }

        $now = now();
        foreach ($instructorIds as $idx => $instructorId) {
            $slot = $timeSlots[$idx % count($timeSlots)];

            DB::table('schedule_instructors')->updateOrInsert(
                [
                    'time_slot_id' => $slot->id,
                    'instructor_id' => $instructorId,
                ],
                [
                    'school_id' => $schoolId,
                    'assignment_type' => 'admin_assigned',
                    'has_pending_removal_request' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function enrollDemoStudents(
        int $schoolId,
        int $branchId,
        array $studentIds,
        array $timeSlots,
        ?int $tdcCourseId,
        ?int $pdcCourseId
    ): array {
        if (empty($studentIds)) {
            return [0, 0];
        }

        $now = now();
        $enrolledCount = 0;
        $scheduledCount = 0;
        $scheduledTarget = (int) ceil(count($studentIds) / 2);

        foreach ($studentIds as $idx => $studentId) {
            $courseId = $this->resolveDemoCourseId($idx, $tdcCourseId, $pdcCourseId);
            if (!$courseId) {
                continue;
            }

            $package = DB::table('course_packages')
                ->where('course_id', '=', $courseId)
                ->orderByDesc('is_popular')
                ->orderBy('id')
                ->first(['id', 'price']);

            $existingRequestId = DB::table('enrollment_requests')
                ->where('school_id', '=', $schoolId)
                ->where('learner_id', '=', $studentId)
                ->where('course_id', '=', $courseId)
                ->value('id');

            $payload = [
                'school_id' => $schoolId,
                'branch_id' => $branchId,
                'learner_id' => $studentId,
                'course_id' => $courseId,
                'status' => 'approved',
                'payment_status' => 'paid',
                'requested_license_type' => 'non_professional',
                'experience_level' => 'new_driver',
                'price' => $package?->price ?? 0,
                'approved_at' => $now,
                'enrolled_at' => $now,
                'remarks' => 'Auto-enrolled demo student.',
                'updated_at' => $now,
            ];

            if ($existingRequestId) {
                DB::table('enrollment_requests')->where('id', '=', $existingRequestId)->update($payload);
                $enrollmentRequestId = (int) $existingRequestId;
            } else {
                $payload['created_at'] = $now;
                $enrollmentRequestId = (int) DB::table('enrollment_requests')->insertGetId($payload);
            }

            DB::table('enrollments')->updateOrInsert(
                [
                    'school_id' => $schoolId,
                    'student_id' => $studentId,
                    'course_id' => $courseId,
                ],
                [
                    'enrollment_request_id' => $enrollmentRequestId,
                    'status' => 'active',
                    'hours_completed' => 0,
                    'enrolled_at' => $now,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            DB::table('students')
                ->where('id', '=', $studentId)
                ->update([
                    'active_enrollment_id' => $enrollmentRequestId,
                    'is_course_locked' => true,
                ]);

            $enrolledCount++;

            if ($idx < $scheduledTarget) {
                $slot = $this->pickSlotForCourse($timeSlots, $courseId, $idx);
                if ($slot && $this->createStudentScheduleBooking($schoolId, $branchId, $studentId, $courseId, $package?->id, (float) ($package?->price ?? 0), $slot, $enrollmentRequestId)) {
                    $scheduledCount++;
                }
            }
        }

        return [$enrolledCount, $scheduledCount];
    }

    private function pickSlotForCourse(array $timeSlots, int $courseId, int $fallbackIndex): ?object
    {
        foreach ($timeSlots as $slot) {
            if ((int) $slot->course_id === (int) $courseId) {
                return $slot;
            }
        }

        if (empty($timeSlots)) {
            return null;
        }

        return $timeSlots[$fallbackIndex % count($timeSlots)];
    }

    private function createStudentScheduleBooking(
        int $schoolId,
        int $branchId,
        int $studentId,
        int $courseId,
        ?int $packageId,
        float $amount,
        object $slot,
        int $enrollmentRequestId
    ): bool {
        $exists = DB::table('bookings')
            ->where('student_id', '=', $studentId)
            ->where('time_slot_id', '=', $slot->id)
            ->exists();

        if ($exists) {
            return false;
        }

        $instructorId = DB::table('schedule_instructors')
            ->where('time_slot_id', '=', $slot->id)
            ->orderBy('id')
            ->value('instructor_id');

        $scheduledAt = trim((string) $slot->date . ' ' . ($slot->start_time ?? '09:00:00'));
        $now = now();

        DB::table('bookings')->insert([
            'school_id' => $schoolId,
            'branch_id' => $branchId,
            'student_id' => $studentId,
            'instructor_id' => $instructorId,
            'course_id' => $courseId,
            'enrollment_request_id' => $enrollmentRequestId,
            'package_id' => $packageId,
            'time_slot_id' => $slot->id,
            'scheduled_at' => $scheduledAt,
            'booking_date' => $scheduledAt,
            'status' => 'scheduled',
            'payment_status' => 'paid',
            'total_amount' => $amount,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return true;
    }

    private function seedVerifiedSessionSample(int $schoolId, string $domain): int
    {
        $instructorId = Instructor::query()
            ->where('school_id', '=', $schoolId)
            ->where('email', '=', "instructor1@{$domain}")
            ->value('id');

        if (!$instructorId) {
            return 0;
        }

        $sample = DB::table('bookings as b')
            ->leftJoin('time_slots as ts', 'ts.id', '=', 'b.time_slot_id')
            ->leftJoin('courses as c', 'c.id', '=', 'b.course_id')
            ->where('b.school_id', '=', $schoolId)
            ->where('b.instructor_id', '=', $instructorId)
            ->whereNotNull('b.enrollment_request_id')
            ->orderBy('b.id')
            ->select([
                'b.id as booking_id',
                'b.course_id',
                'b.enrollment_request_id',
                'b.booking_date',
                'b.scheduled_at',
                'ts.date as slot_date',
                'ts.start_time as slot_start',
                'ts.end_time as slot_end',
                'ts.session_type as slot_session_type',
                'c.course_type',
            ])
            ->first();

        if (!$sample) {
            return 0;
        }

        $enrollmentId = DB::table('enrollments')
            ->where('school_id', '=', $schoolId)
            ->where('enrollment_request_id', '=', $sample->enrollment_request_id)
            ->value('id');

        if (!$enrollmentId) {
            return 0;
        }

        $alreadySeeded = DB::table('session_completions')
            ->where('school_id', '=', $schoolId)
            ->where('instructor_id', '=', $instructorId)
            ->where('enrollment_id', '=', $enrollmentId)
            ->exists();

        if ($alreadySeeded) {
            return 0;
        }

        $sessionType = in_array((string) $sample->slot_session_type, ['theoretical', 'practical'], true)
            ? (string) $sample->slot_session_type
            : ((string) $sample->course_type === 'practical' ? 'practical' : 'theoretical');

        $sessionDate = $sample->slot_date
            ? (string) $sample->slot_date
            : ($sample->booking_date ? date('Y-m-d', strtotime((string) $sample->booking_date)) : now()->toDateString());

        $startTime = $sample->slot_start
            ? (string) $sample->slot_start
            : ($sample->scheduled_at ? date('H:i:s', strtotime((string) $sample->scheduled_at)) : '09:00:00');

        $endTime = $sample->slot_end
            ? (string) $sample->slot_end
            : date('H:i:s', strtotime($startTime . ' +1 hour'));

        $hours = round(max(0.5, (strtotime($endTime) - strtotime($startTime)) / 3600), 2);
        $now = now();

        DB::table('session_completions')->insert([
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
            'notes' => 'Auto-seeded verified demo session for instructor portal validation.',
            'logged_by' => $instructorId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('bookings')
            ->where('id', '=', $sample->booking_id)
            ->update([
                'status' => 'completed',
                'session_status' => 'completed',
                'attendance_status' => 'attended',
                'attendance_marked_at' => $now,
                'updated_at' => $now,
            ]);

        return 1;
    }
}