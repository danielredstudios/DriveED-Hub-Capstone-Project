<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Course;
use App\Models\CoursePackage;
use Database\Seeders\Traits\HandlesSchoolSeeding;

class LySpeedSeeder extends Seeder
{
    use HandlesSchoolSeeding;

    public function run(): void
    {
        $demoPassword = (string) env('DEMO_SEED_PASSWORD', 'DriveDemo123');
        $hashedPassword = Hash::make($demoPassword);

        $this->command->info('');
        $this->command->info('🏫 Creating LySpeed Driving School (10 branches)...');

        $school = School::updateOrCreate(
            ['slug' => 'lyspeed-driving'],
            [
                'name' => 'LySpeed Driving School',
                'timezone' => 'Asia/Manila',
                'branding' => json_encode([
                    'logo' => null,
                    'colors' => ['primary' => '#8B0000', 'secondary' => '#ffffff', 'accent' => '#B22222'],
                ]),
                'settings' => json_encode([
                    'contact_number' => '+63 918 234 5678',
                    'email' => 'info@lyspeed.com',
                    'address' => '456 Jose Abad Santos Avenue, San Fernando, Pampanga',
                    'allow_self_registration' => true,
                ]),
                'instructor_removal_notice_days' => 7,
            ]
        );

        SchoolSetting::updateOrCreate(
            ['school_id' => $school->id],
            [
                'primary_color' => '#8B0000',
                'secondary_color' => '#ffffff',
                'accent_color' => '#B22222',
                'button_primary_bg' => '#8B0000',
                'button_style' => 'solid',
                'use_gradient_header' => false,
                'background_type' => 'color',
                'background_color' => '#f8fafc',
                'role_student_bg' => '#fee2e2',
                'role_student_text' => '#991b1b',
                'role_instructor_bg' => '#fef2f2',
                'role_instructor_text' => '#7f1d1d',
                'badge_pending_bg' => '#f59e0b',
                'badge_approved_bg' => '#10b981',
                'badge_cancelled_bg' => '#dc2626',

                'header_text_color' => '#ffffff',
                'sidebar_bg_color' => '#ffffff',
                'sidebar_text_color' => '#8B0000',
                'instructor_selection_mode' => 'student_choice',
                'enable_booking_queue' => true,
                'booking_queue_days' => 2,
                'enable_branches' => true,
                'booking_cutoff_hours' => 12,
                'alert_threshold_pending' => 50,
            ]
        );

        // ── 1. INFRASTRUCTURE ──
        $branchList = [
            ['name' => 'Main Branch - San Fernando', 'address' => '456 Jose Abad Santos Ave, San Fernando, Pampanga', 'contact_number' => '+63-918-234-5601', 'email' => 'sanfernando@lyspeed.com'],
            ['name' => 'Guagua Branch', 'address' => '321 San Nicolas, Guagua, Pampanga', 'contact_number' => '+63-918-234-5602', 'email' => 'guagua@lyspeed.com'],
            ['name' => 'Angeles City Branch', 'address' => '77 Miranda St, Angeles City, Pampanga', 'contact_number' => '+63-918-234-5603', 'email' => 'angeles@lyspeed.com'],
            ['name' => 'Mabalacat Branch', 'address' => '89 MacArthur Highway, Mabalacat, Pampanga', 'contact_number' => '+63-918-234-5604', 'email' => 'mabalacat@lyspeed.com'],
            ['name' => 'Mexico Branch', 'address' => '15 Mexico Town Center, Mexico, Pampanga', 'contact_number' => '+63-918-234-5605', 'email' => 'mexico@lyspeed.com'],
            ['name' => 'Apalit Branch', 'address' => '44 Apalit Bypass Rd, Apalit, Pampanga', 'contact_number' => '+63-918-234-5606', 'email' => 'apalit@lyspeed.com'],
            ['name' => 'Porac Branch', 'address' => '29 National Highway, Porac, Pampanga', 'contact_number' => '+63-918-234-5607', 'email' => 'porac@lyspeed.com'],
            ['name' => 'Bacolor Branch', 'address' => '10 Bacolor Town Proper, Bacolor, Pampanga', 'contact_number' => '+63-918-234-5608', 'email' => 'bacolor@lyspeed.com'],
            ['name' => 'Lubao Branch', 'address' => '66 Lubao Bypass Rd, Lubao, Pampanga', 'contact_number' => '+63-918-234-5609', 'email' => 'lubao@lyspeed.com'],
            ['name' => 'Magalang Branch', 'address' => '38 Magalang-Concepcion Rd, Magalang, Pampanga', 'contact_number' => '+63-918-234-5610', 'email' => 'magalang@lyspeed.com'],
        ];
        $branches = $this->createBranches($school, $branchList);
        $this->command->info('   ✓ Branches created');

        // ── 2. VEHICLES ──
        $this->createVehicles($school, $branches);
        $this->command->info('   ✓ Fleet created');

        // ── 2. IDENTITY (All users first) ──

        // Admins
        foreach ([
            ['name' => 'Carlos Miguel Villanueva', 'email' => 'carlos.villanueva@lyspeed.com'],
            ['name' => 'Elena Rose Gonzales', 'email' => 'elena.gonzales@lyspeed.com'],
        ] as $a) {
            Admin::updateOrCreate(['email' => $a['email']], [
                'school_id' => $school->id,
                'name' => $a['name'],
                'password' => $hashedPassword,
                'role' => 'school_admin',
                'is_active' => true,
            ]);
        }
        Admin::updateOrCreate(['email' => 'lyspeed.admin@gmail.com'], [
            'school_id' => $school->id,
            'name' => 'LySpeed Demo Admin',
            'password' => $hashedPassword,
            'role' => 'school_admin',
            'is_active' => true,
        ]);

        // Branch Managers
        $lsManagerNames = [
            'Angelina Reyes',
            'Benito Aquino',
            'Cristina Dela Cruz',
            'Dominador Ocampo',
            'Evelyn Pangilinan',
            'Florante Manansala',
            'Gilda Cunanan',
            'Honesto David',
            'Imelda Lugtu',
            'Josefino Pineda',
        ];
        foreach ($lsManagerNames as $i => $name) {
            Admin::updateOrCreate(['email' => $this->makeEmail($name, 'lyspeed.com')], [
                'school_id' => $school->id,
                'branch_id' => $branches[$i]->id,
                'name' => $name,
                'password' => $hashedPassword,
                'role' => 'branch_secretary',
                'is_active' => true,
            ]);
        }

        // Instructors
        $instructors = [];
        $instOffset = 200;
        for ($b = 0; $b < count($branches); $b++) {
            for ($j = 0; $j < 3; $j++) {
                $name = $this->nameAt($instOffset);
                $instructors[] = Instructor::updateOrCreate(
                    ['email' => $this->makeEmail("ls.inst.{$instOffset}", 'lyspeed.com')],
                    [
                        'school_id' => $school->id,
                        'branch_id' => $branches[$b]->id,
                        'name' => $name,
                        'contact' => '+63-918-666-' . str_pad($instOffset - 199, 4, '0', STR_PAD_LEFT),
                        'password' => $hashedPassword,
                        'license_number' => 'LIC-LS-2024-' . str_pad($instOffset - 199, 3, '0', STR_PAD_LEFT),
                        'bio' => 'Professional driving instructor at LySpeed Driving School.',
                        'status' => 'active',
                        'availability' => 'available',
                    ]
                );
                $instOffset++;
            }
        }
        $instructors[] = Instructor::updateOrCreate(
            ['email' => 'lyspeed.instructor@gmail.com'],
            [
                'school_id' => $school->id,
                'branch_id' => $branches[0]->id,
                'name' => 'LySpeed Demo Instructor',
                'contact' => '+63-918-666-0000',
                'password' => $hashedPassword,
                'license_number' => 'LIC-LS-TEST-001',
                'bio' => 'Test instructor account for demo purposes.',
                'status' => 'active',
                'availability' => 'available',
            ]
        );

        // Students
        $students = [];
        $stuOffset = 1000;
        for ($b = 0; $b < count($branches); $b++) {
            for ($j = 0; $j < 8; $j++) {
                $name = $this->nameAt($stuOffset);
                $student = Student::updateOrCreate(
                    ['school_id' => $school->id, 'email' => $this->makeEmail("ls.stu.{$stuOffset}", 'lyspeed.test')],
                    [
                        'name' => $name,
                        'branch_id' => $branches[$b]->id,
                        'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                        'password' => $hashedPassword,
                        'status' => 'active',
                        'enrollment_date' => now()->subDays(rand(7, 60)),
                    ]
                );
                $student->role = 'student';
                $student->email_verified_at = $student->email_verified_at ?? now();
                $student->verification_code = null;
                $student->verification_code_expires_at = null;
                $student->verification_attempts = 0;
                $student->last_verification_attempt_at = null;
                $student->save();
                $students[] = $student;
                $stuOffset++;
            }
        }
        $demoStudent = Student::updateOrCreate(
            ['school_id' => $school->id, 'email' => 'lyspeed.student@gmail.com'],
            [
                'name' => 'LySpeed Demo Student',
                'branch_id' => $branches[0]->id,
                'contact' => '+63-918-999-0001',
                'password' => $hashedPassword,
                'status' => 'active',
                'enrollment_date' => now()->subDays(30),
            ]
        );
        $demoStudent->role = 'student';
        $demoStudent->email_verified_at = $demoStudent->email_verified_at ?? now();
        $demoStudent->verification_code = null;
        $demoStudent->verification_code_expires_at = null;
        $demoStudent->verification_attempts = 0;
        $demoStudent->last_verification_attempt_at = null;
        $demoStudent->save();
        $students[] = $demoStudent;

        // Guests
        $admins = Admin::where('school_id', '=', $school->id)->where('role', '=', 'school_admin')->get()->all();
        $guests = $this->createGuestsAndEnrollmentRequests($school, [], $admins, $demoPassword);

        $this->command->info('   ✓ All user identities created (Admins, Managers, Instructors, Students, Guests)');

        // ── 3. PRODUCTS (Courses) ──
        $courses = $this->createLySpeedCourses($school);
        $this->command->info('   ✓ Courses with packages created');

        // ── 4. INTERACTIONS (Link everything) ──

        // Time Slots, Bookings, Payments
        $this->createTimeSlotsAndAssignments($school, $instructors, $courses, $branches);
        $this->createBookingsAndPayments($school, $students, $instructors, $courses, $branches, 18);

        // Link guests to courses
        $this->createGuestsAndEnrollmentRequests($school, $courses, $admins, $demoPassword);

        // Notifications
        $this->createSampleNotifications($school, $students, $instructors, $admins, $guests);

        $this->command->info('   ✓ Interactions created (Slots, Bookings, Enrollment Requests)');
    }

    private function createLySpeedCourses(School $school): array
    {
        $courses = [];

        $c1 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Basic Driving Course'],
            ['description' => 'Affordable driving lessons for beginners.', 'type' => 'Practical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true, 'features' => ['Basic Vehicle Control', 'Traffic Navigation', 'Parking Skills', 'Road Safety']]
        );
        $courses[] = $c1;
        CoursePackage::updateOrCreate(['course_id' => $c1->id, 'name' => '8-Hour Starter'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 8, 'price' => 4000.00, 'description' => 'Beginner automatic driving course.']);
        CoursePackage::updateOrCreate(['course_id' => $c1->id, 'name' => '12-Hour Complete'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 12, 'price' => 5500.00, 'description' => 'Complete automatic driving course.', 'is_popular' => true]);

        $c2 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Motorcycle Riding Course'],
            ['description' => 'Learn to ride motorcycles safely.', 'type' => 'Practical', 'vehicle_type' => 'Motorcycle', 'status' => 'active', 'features' => ['Balance Training', 'Gear Shifting', 'Defensive Riding', 'License Preparation']]
        );
        $courses[] = $c2;
        CoursePackage::updateOrCreate(['course_id' => $c2->id, 'name' => '6-Hour Package'], ['transmission_type' => 'manual', 'vehicle_type' => 'Motorcycle', 'training_hours' => 6, 'price' => 3000.00, 'description' => 'Motorcycle riding fundamentals.']);

        $c3 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Theoretical Driving Course (TDC)'],
            ['description' => 'LTO-accredited theoretical driving course.', 'type' => 'Theoretical', 'vehicle_type' => 'Car', 'status' => 'active', 'features' => ['Traffic Rules', 'Road Signs', 'LTO Accredited', 'Certificate']]
        );
        $courses[] = $c3;
        CoursePackage::updateOrCreate(['course_id' => $c3->id, 'name' => 'TDC 15-Hour'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 15, 'price' => 1200.00, 'description' => 'Complete TDC for LTO written exam.']);

        return $courses;
    }
}
