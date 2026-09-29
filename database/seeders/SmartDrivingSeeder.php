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

class SmartDrivingSeeder extends Seeder
{
    use HandlesSchoolSeeding;

    public function run(): void
    {
        $hashedPassword = Hash::make('P@ssw0rd123');

        $this->command->info('');
        $this->command->info('🏫 Creating Smart Driving School (25 branches)...');

        $school = School::updateOrCreate(
            ['slug' => 'smart-driving'],
            [
                'name' => 'Smart Driving School',
                'timezone' => 'Asia/Manila',
                'branding' => json_encode([
                    'logo' => null,
                    'colors' => ['primary' => '#3b82f6', 'secondary' => '#fbbf24', 'accent' => '#1e40af'],
                ]),
                'settings' => json_encode([
                    'contact_number' => '+63 917 123 4567',
                    'email' => 'info@smartdriving.com',
                    'address' => '123 MacArthur Highway, Angeles City, Pampanga',
                    'allow_self_registration' => true,
                ]),
                'instructor_removal_notice_days' => 7,
            ]
        );

        SchoolSetting::updateOrCreate(
            ['school_id' => $school->id],
            [
                'primary_color' => '#3b82f6',
                'secondary_color' => '#fbbf24',
                'accent_color' => '#1e40af',
                'button_primary_bg' => '#3b82f6',
                'button_style' => 'solid',
                'role_student_bg' => '#dbeafe',
                'role_student_text' => '#1e40af',
                'role_instructor_bg' => '#e0f2fe',
                'role_instructor_text' => '#0369a1',
                'badge_pending_bg' => '#fbbf24',
                'badge_approved_bg' => '#10b981',
                'badge_cancelled_bg' => '#ef4444',

                'use_gradient_header' => false,
                'header_text_color' => '#ffffff',
                'background_type' => 'color',
                'background_color' => '#f8fafc',
                'sidebar_bg_color' => '#ffffff',
                'sidebar_text_color' => '#3b82f6',
                'instructor_selection_mode' => 'student_choice',
                'enable_booking_queue' => true,
                'booking_queue_days' => 3,
                'enable_branches' => true,
                'booking_cutoff_hours' => 12,
                'alert_threshold_pending' => 50,
            ]
        );

        // ── 1. INFRASTRUCTURE ──
        $branchList = [
            ['name' => 'Main Branch - Angeles City', 'address' => '123 MacArthur Highway, Angeles City, Pampanga', 'contact_number' => '+63-917-123-4501', 'email' => 'angeles@smartdriving.com'],
            ['name' => 'Clark Branch', 'address' => '45 M.A. Roxas Highway, Clark Freeport Zone, Pampanga', 'contact_number' => '+63-917-123-4502', 'email' => 'clark@smartdriving.com'],
            ['name' => 'Dau Branch', 'address' => '789 Jose Abad Santos Ave, Dau, Mabalacat, Pampanga', 'contact_number' => '+63-917-123-4503', 'email' => 'dau@smartdriving.com'],
            ['name' => 'San Fernando Branch', 'address' => '456 Olongapo-San Fernando Rd, San Fernando, Pampanga', 'contact_number' => '+63-917-123-4504', 'email' => 'sanfernando@smartdriving.com'],
            ['name' => 'Mabalacat Branch', 'address' => '321 MacArthur Highway, Mabalacat, Pampanga', 'contact_number' => '+63-917-123-4505', 'email' => 'mabalacat@smartdriving.com'],
            ['name' => 'Porac Branch', 'address' => '55 Porac-Gapan Rd, Porac, Pampanga', 'contact_number' => '+63-917-123-4506', 'email' => 'porac@smartdriving.com'],
            ['name' => 'Guagua Branch', 'address' => '88 San Nicolas, Guagua, Pampanga', 'contact_number' => '+63-917-123-4507', 'email' => 'guagua@smartdriving.com'],
            ['name' => 'Lubao Branch', 'address' => '12 Municipal Road, Lubao, Pampanga', 'contact_number' => '+63-917-123-4508', 'email' => 'lubao@smartdriving.com'],
            ['name' => 'Apalit Branch', 'address' => '67 Apalit Town Center, Apalit, Pampanga', 'contact_number' => '+63-917-123-4509', 'email' => 'apalit@smartdriving.com'],
            ['name' => 'Mexico Branch', 'address' => '233 Mexico Town Proper, Mexico, Pampanga', 'contact_number' => '+63-917-123-4510', 'email' => 'mexico@smartdriving.com'],
            ['name' => 'Bacolor Branch', 'address' => '100 Bacolor Town Proper, Bacolor, Pampanga', 'contact_number' => '+63-917-123-4511', 'email' => 'bacolor@smartdriving.com'],
            ['name' => 'Magalang Branch', 'address' => '78 Magalang Public Market, Magalang, Pampanga', 'contact_number' => '+63-917-123-4512', 'email' => 'magalang@smartdriving.com'],
            ['name' => 'Arayat Branch', 'address' => '55 Arayat Town Center, Arayat, Pampanga', 'contact_number' => '+63-917-123-4513', 'email' => 'arayat@smartdriving.com'],
            ['name' => 'Candaba Branch', 'address' => '31 Candaba Swamp Rd, Candaba, Pampanga', 'contact_number' => '+63-917-123-4514', 'email' => 'candaba@smartdriving.com'],
            ['name' => 'Floridablanca Branch', 'address' => '22 Floridablanca Town Proper, Floridablanca, Pampanga', 'contact_number' => '+63-917-123-4515', 'email' => 'floridablanca@smartdriving.com'],
            ['name' => 'Santa Ana Branch', 'address' => '14 Santa Ana Town Center, Santa Ana, Pampanga', 'contact_number' => '+63-917-123-4516', 'email' => 'santaana@smartdriving.com'],
            ['name' => 'Santa Rita Branch', 'address' => '99 Santa Rita Main Rd, Santa Rita, Pampanga', 'contact_number' => '+63-917-123-4517', 'email' => 'santarita@smartdriving.com'],
            ['name' => 'Santo Tomas Branch', 'address' => '45 Santo Tomas Rd, Santo Tomas, Pampanga', 'contact_number' => '+63-917-123-4518', 'email' => 'santotomas@smartdriving.com'],
            ['name' => 'Sasmuan Branch', 'address' => '76 Sasmuan Town Center, Sasmuan, Pampanga', 'contact_number' => '+63-917-123-4519', 'email' => 'sasmuan@smartdriving.com'],
            ['name' => 'Tarlac City Branch', 'address' => '200 F. Tañedo St, Tarlac City, Tarlac', 'contact_number' => '+63-917-123-4520', 'email' => 'tarlac@smartdriving.com'],
            ['name' => 'Olongapo Branch', 'address' => '321 Rizal Avenue, Olongapo City, Zambales', 'contact_number' => '+63-917-123-4521', 'email' => 'olongapo@smartdriving.com'],
            ['name' => 'Subic Branch', 'address' => '105 Subic Bay Freeport Zone, Subic, Zambales', 'contact_number' => '+63-917-123-4522', 'email' => 'subic@smartdriving.com'],
            ['name' => 'Cabanatuan Branch', 'address' => '88 Maharlika Highway, Cabanatuan City, Nueva Ecija', 'contact_number' => '+63-917-123-4523', 'email' => 'cabanatuan@smartdriving.com'],
            ['name' => 'Meycauayan Branch', 'address' => '55 MacArthur Highway, Meycauayan, Bulacan', 'contact_number' => '+63-917-123-4524', 'email' => 'meycauayan@smartdriving.com'],
            ['name' => 'Balanga Branch', 'address' => '33 Capitol Drive, Balanga City, Bataan', 'contact_number' => '+63-917-123-4525', 'email' => 'balanga@smartdriving.com'],
        ];
        $branches = $this->createBranches($school, $branchList);
        $this->command->info('   ✓ Branches created');

        // ── 2. IDENTITY (All users first) ──

        // Admins
        foreach ([
            ['name' => 'Maria Cristina Santos', 'email' => 'maria.santos@smartdriving.com'],
            ['name' => 'Jose Antonio Reyes', 'email' => 'jose.reyes@smartdriving.com'],
            ['name' => 'Carmen Rosa Villanueva', 'email' => 'carmen.villanueva@smartdriving.com'],
        ] as $a) {
            Admin::updateOrCreate(['email' => $a['email']], [
                'school_id' => $school->id,
                'name' => $a['name'],
                'password' => $hashedPassword,
                'role' => 'school_admin',
                'is_active' => true,
            ]);
        }
        Admin::updateOrCreate(['email' => 'schooladmin@gmail.com'], [
            'school_id' => $school->id,
            'name' => 'Demo School Admin',
            'password' => $hashedPassword,
            'role' => 'school_admin',
            'is_active' => true,
        ]);

        // Branch Managers
        Admin::updateOrCreate(['email' => 'secretary@gmail.com'], [
            'school_id' => $school->id,
            'branch_id' => $branches[0]->id,
            'name' => 'Demo Branch Manager',
            'password' => $hashedPassword,
            'role' => 'branch_secretary',
            'is_active' => true,
        ]);
        $managerNames = [
            'Rosa Marie Lim',
            'Fernando Bautista',
            'Lorna Aguilar',
            'Cecilia Tan',
            'Eduardo Gomez',
            'Myrna Torres',
            'Reynaldo Santos',
            'Gloria Pascual',
            'Nestor Cruz',
            'Erlinda Ramos',
            'Virgilio Lopez',
            'Teresita Mendoza',
            'Danilo Garcia',
            'Rosario Flores',
            'Arturo Rivera',
            'Corazon Hernandez',
            'Benjamin Dizon',
            'Felicidad Navarro',
            'Rodolfo Medina',
            'Leonora Jimenez',
            'Gregorio Alvarez',
            'Milagros Ruiz',
            'Alfredo Sanchez',
            'Esperanza Ramirez',
        ];
        foreach ($managerNames as $i => $name) {
            $branchIdx = $i + 1; // branches 1-24
            Admin::updateOrCreate(['email' => $this->makeEmail($name, 'smartdriving.com')], [
                'school_id' => $school->id,
                'branch_id' => $branches[$branchIdx]->id,
                'name' => $name,
                'password' => $hashedPassword,
                'role' => 'branch_secretary',
                'is_active' => true,
            ]);
        }

        // Instructors
        $instructors = [];
        $instOffset = 0;
        for ($b = 0; $b < count($branches); $b++) {
            for ($j = 0; $j < 3; $j++) {
                $name = $this->nameAt($instOffset);
                $instructors[] = Instructor::updateOrCreate(
                    ['email' => $this->makeEmail("sd.inst.{$instOffset}", 'smartdriving.com')],
                    [
                        'school_id' => $school->id,
                        'branch_id' => $branches[$b]->id,
                        'name' => $name,
                        'contact' => '+63-917-555-' . str_pad($instOffset + 1, 4, '0', STR_PAD_LEFT),
                        'password' => $hashedPassword,
                        'license_number' => 'LIC-SD-2024-' . str_pad($instOffset + 1, 3, '0', STR_PAD_LEFT),
                        'bio' => 'Experienced driving instructor at Smart Driving School.',
                        'status' => 'active',
                        'availability' => 'available',
                    ]
                );
                $instOffset++;
            }
        }
        $instructors[] = Instructor::updateOrCreate(
            ['email' => 'instructor@gmail.com'],
            [
                'school_id' => $school->id,
                'branch_id' => $branches[0]->id,
                'name' => 'Demo Instructor',
                'contact' => '+63-917-555-0000',
                'password' => $hashedPassword,
                'license_number' => 'LIC-SD-TEST-001',
                'bio' => 'Test instructor account for demo purposes.',
                'status' => 'active',
                'availability' => 'available',
            ]
        );

        // Students
        $students = [];
        $stuOffset = 500;
        for ($b = 0; $b < count($branches); $b++) {
            for ($j = 0; $j < 8; $j++) {
                $name = $this->nameAt($stuOffset);
                $student = Student::updateOrCreate(
                    ['school_id' => $school->id, 'email' => $this->makeEmail("sd.stu.{$stuOffset}", 'smartdriving.test')],
                    [
                        'name' => $name,
                        'branch_id' => $branches[$b]->id,
                        'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                        'password' => $hashedPassword,
                        'status' => 'active',
                        'enrollment_date' => now()->subDays(rand(7, 90)),
                    ]
                );
                $student->role = 'student';
                $student->save();
                $students[] = $student;
                $stuOffset++;
            }
        }
        $demoStudent = Student::updateOrCreate(
            ['school_id' => $school->id, 'email' => 'student@gmail.com'],
            [
                'name' => 'Demo Student',
                'branch_id' => $branches[0]->id,
                'contact' => '+63-900-000-0001',
                'password' => $hashedPassword,
                'status' => 'active',
                'enrollment_date' => now()->subDays(30),
            ]
        );
        $demoStudent->role = 'student';
        $demoStudent->save();
        $students[] = $demoStudent;

        // Guests
        $admins = Admin::where('school_id', '=', $school->id)->where('role', '=', 'school_admin')->get()->all();
        $guests = $this->createGuestsAndEnrollmentRequests($school, [], $admins, 'P@ssw0rd123');

        $this->command->info('   ✓ All user identities created (Admins, Managers, Instructors, Students, Guests)');

        // ── 3. PRODUCTS (Courses) ──
        $courses = $this->createSmartDrivingCourses($school);
        $this->command->info('   ✓ Courses with packages created');

        // ── 4. INTERACTIONS (Link everything) ──

        // Time Slots, Bookings, Payments
        $this->createTimeSlotsAndAssignments($school, $instructors, $courses, $branches);
        $this->createBookingsAndPayments($school, $students, $instructors, $courses, $branches, 30);

        // Link guests to courses
        $this->createGuestsAndEnrollmentRequests($school, $courses, $admins, 'P@ssw0rd123');

        // Notifications
        $this->createSampleNotifications($school, $students, $instructors, $admins, $guests);

        $this->command->info('   ✓ Interactions created (Slots, Bookings, Enrollment Requests)');
    }

    private function createSmartDrivingCourses(School $school): array
    {
        $courses = [];

        $c1 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course (Manual)'],
            ['description' => 'Master manual transmission driving with comprehensive hands-on training.', 'type' => 'Practical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true, 'features' => ['Manual Transmission', 'Clutch Control', 'Hill Start', 'Parking Techniques', 'Defensive Driving']]
        );
        $courses[] = $c1;
        CoursePackage::updateOrCreate(['course_id' => $c1->id, 'name' => '10-Hour Package'], ['transmission_type' => 'manual', 'vehicle_type' => 'Car', 'training_hours' => 10, 'price' => 5500.00, 'description' => 'Basic manual driving course for beginners.']);
        CoursePackage::updateOrCreate(['course_id' => $c1->id, 'name' => '15-Hour Package'], ['transmission_type' => 'manual', 'vehicle_type' => 'Car', 'training_hours' => 15, 'price' => 7500.00, 'description' => 'Complete manual driving course with advanced techniques.', 'is_popular' => true]);

        $c2 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course (Automatic)'],
            ['description' => 'Learn to drive automatic transmission vehicles with confidence.', 'type' => 'Practical', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true, 'features' => ['Automatic Transmission', 'City Driving', 'Parking Techniques', 'Defensive Driving']]
        );
        $courses[] = $c2;
        CoursePackage::updateOrCreate(['course_id' => $c2->id, 'name' => '8-Hour Package'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 8, 'price' => 4800.00, 'description' => 'Automatic driving course for beginners.', 'is_popular' => true]);

        $c3 = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Theoretical Driving Course (TDC)'],
            ['description' => 'Comprehensive road rules and traffic signs education. Required for LTO written exam.', 'type' => 'Theoretical', 'vehicle_type' => 'Car', 'status' => 'active', 'features' => ['Traffic Rules', 'Road Signs', 'LTO Written Exam Prep', 'Certificate Included']]
        );
        $courses[] = $c3;
        CoursePackage::updateOrCreate(['course_id' => $c3->id, 'name' => 'TDC 15-Hour Course'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 15, 'price' => 1500.00, 'description' => 'Complete TDC for LTO exam preparation.']);

        return $courses;
    }
}
