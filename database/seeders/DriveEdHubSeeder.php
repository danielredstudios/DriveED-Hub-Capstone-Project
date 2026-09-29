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
use App\Models\CourseModule;
use App\Models\ModuleLesson;
use App\Models\EnrollmentRequest;
use App\Models\GCashSetting;
use Database\Seeders\Traits\HandlesSchoolSeeding;

class DriveEdHubSeeder extends Seeder
{
    use HandlesSchoolSeeding;

    public function run(): void
    {
        // Ensure seeder assets are available in storage
        $instructorStorage = storage_path('app/public/instructor-licenses');
        $courseStorage = storage_path('app/public/courses');
        $guestStorage = storage_path('app/public/guest-data');
        
        foreach ([$instructorStorage, $courseStorage, $guestStorage] as $path) {
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
        }
        
        $assets = [
            'male_profile.png' => $instructorStorage . '/male_profile.png',
            'female_profile.png' => $instructorStorage . '/female_profile.png',
            'license_tdc.png' => $instructorStorage . '/license_tdc.png',
            'license_pdc.png' => $instructorStorage . '/license_pdc.png',
            'license_combo.png' => $instructorStorage . '/license_combo.png',
            'course_tdc.jpg' => $courseStorage . '/course_tdc.jpg',
            'course_pdc.jpg' => $courseStorage . '/course_pdc.jpg',
            'course_combo.jpg' => $courseStorage . '/course_combo.jpg',
            'guest_receipt.jpg' => $guestStorage . '/guest_receipt.jpg',
            'guest_permit.jpg' => $guestStorage . '/guest_permit.jpg',
            'gcash_qr.jpg' => $instructorStorage . '/../branding/gcash_qr.jpg',
        ];

        // Ensure branding directory exists
        if (!file_exists($instructorStorage . '/../branding')) {
            mkdir($instructorStorage . '/../branding', 0755, true);
        }

        foreach ($assets as $asset => $dest) {
            $source = database_path('seeders/assets/' . $asset);
            if (file_exists($source)) {
                copy($source, $dest);
            }
        }

        $demoPassword = (string) env('DEMO_SEED_PASSWORD', 'DriveDemo123');
        $hashedPassword = Hash::make($demoPassword);

        $this->command->info('');
        $this->command->info('🏫 Creating DriveED Hub Driving School (2 branches)...');

        $school = School::updateOrCreate(
            ['slug' => 'drived-hub'],
            [
                'name' => 'DriveED Hub Driving School',
                'timezone' => 'Asia/Manila',
                'branding' => json_encode([
                    'logo' => null,
                    'colors' => ['primary' => '#667eea', 'secondary' => '#764ba2', 'accent' => '#1e40af'],
                ]),
                'settings' => json_encode([
                    'contact_number' => '+63 919 345 6789',
                    'email' => 'info@drivedhub.com',
                    'address' => '789 Del Pilar Street, Clark Freeport Zone, Pampanga',
                    'allow_self_registration' => true,
                ]),
                'instructor_removal_notice_days' => 7,
            ]
        );

        SchoolSetting::updateOrCreate(
            ['school_id' => $school->id],
            [
                'primary_color' => '#667eea',
                'secondary_color' => '#764ba2',
                'accent_color' => '#1e40af',
                'button_primary_bg' => '#667eea',
                'button_style' => 'gradient',
                'use_gradient_header' => true,
                'background_type' => 'gradient',
                'background_color' => '#f8fafc',
                'role_student_bg' => '#ede9fe',
                'role_student_text' => '#5b21b6',
                'role_instructor_bg' => '#f3e8ff',
                'role_instructor_text' => '#6b21a8',

                'header_text_color' => '#ffffff',
                'sidebar_bg_color' => '#ffffff',
                'sidebar_text_color' => '#667eea',
                'instructor_selection_mode' => 'admin_assigned',
                'enable_booking_queue' => true,
                'booking_queue_days' => 3,
                'enable_branches' => true,
                'booking_cutoff_hours' => 12,
                'alert_threshold_pending' => 50,
                'require_instructor_license' => true,
                'license_instructions' => [
                    [
                        'title' => 'Theoretical Driving Course (TDC)',
                        'milestone' => 'tdc',
                        'description' => 'Attend 15 hours of classroom instruction across 3 sessions. Pass the final exam to get your TDC Certificate.'
                    ],
                    [
                        'title' => 'Apply for Student Permit',
                        'milestone' => 'permit',
                        'description' => 'Visit any LTO branch with your TDC Certificate and Medical Certificate to secure your Student Permit.'
                    ],
                    [
                        'title' => 'Practical Driving Course (PDC)',
                        'milestone' => 'pdc',
                        'description' => 'Once you have your permit, enroll in PDC to start hands-on driving with our professional instructors.'
                    ],
                    [
                        'title' => 'LTO Driver\'s License Application',
                        'milestone' => 'license',
                        'description' => 'After finishing PDC and holding your permit for at least 31 days, you can apply for your Non-Professional License.'
                    ],
                ]
            ]
        );

        // GCash Settings
        GCashSetting::updateOrCreate(
            ['school_id' => $school->id, 'branch_id' => null],
            [
                'account_name' => 'DriveED Hub Admin',
                'account_number' => '09193456789',
                'qr_path' => 'branding/gcash_qr.jpg',
                'is_active' => true,
            ]
        );

        // ── 1. INFRASTRUCTURE ──
        $branches = $this->createBranches($school, [
            ['name' => 'Main Campus - Clark', 'address' => '789 Del Pilar Street, Clark Freeport Zone, Pampanga', 'contact_number' => '+63-919-345-6789', 'email' => 'clark@drivedhub.com'],
            ['name' => 'Balibago Branch', 'address' => '456 Fields Avenue, Balibago, Angeles City, Pampanga', 'contact_number' => '+63-919-345-6790', 'email' => 'balibago@drivedhub.com'],
        ]);
        $this->command->info('   ✓ Branches created');

        // ── 2. VEHICLES ──
        $this->createVehicles($school, $branches);
        $this->command->info('   ✓ Fleet created');

        // ── 2. IDENTITY (All users first) ──

        // Admins
        $admin = Admin::updateOrCreate(['email' => 'admin1@driveedhub.test'], [
            'school_id' => $school->id,
            'name' => 'Antonio Francisco Reyes',
            'password' => $hashedPassword,
            'role' => 'school_admin',
            'is_active' => true,
        ]);
        $admin2 = Admin::updateOrCreate(['email' => 'admin2@driveedhub.test'], [
            'school_id' => $school->id,
            'name' => 'School Admin Two',
            'password' => $hashedPassword,
            'role' => 'school_admin',
            'is_active' => true,
        ]);
        Admin::updateOrCreate(['email' => 'manager1@driveedhub.test'], [
            'school_id' => $school->id,
            'branch_id' => $branches[0]->id,
            'name' => 'Patricia Lyn Mendoza',
            'password' => $hashedPassword,
            'role' => 'branch_secretary',
            'is_active' => true,
        ]);
        Admin::updateOrCreate(['email' => 'manager2@driveedhub.test'], [
            'school_id' => $school->id,
            'branch_id' => $branches[1]->id,
            'name' => 'Gabriel Marco Santos',
            'password' => $hashedPassword,
            'role' => 'branch_secretary',
            'is_active' => true,
        ]);

        // ── 3. PRODUCTS (Courses) ──
        $courses = $this->createDriveEdHubCourses($school);

        // Instructors
        $dhInstructors = [
            ['name' => 'Ricardo Antonio Cruz', 'email' => 'instructor1@driveedhub.test', 'contact' => '+63-919-777-3001', 'license' => 'LIC-DH-2024-001', 'bio' => 'Senior Instructor specializing in Manual Transmission and Motorcycle training. 8 years experience.', 'branch' => 0, 'course_idx' => 0, 'status' => 'verified', 'avail' => 'available', 'gender' => 'male', 'l_type' => 'tdc'],
            ['name' => 'Maria Victoria Santos', 'email' => 'instructor2@driveedhub.test', 'contact' => '+63-919-777-3002', 'license' => 'LIC-DH-2024-002', 'bio' => 'Expert in Automatic Transmission and Practical Driving. Certified defensive driving instructor.', 'branch' => 0, 'course_idx' => 0, 'status' => 'pending', 'avail' => 'available', 'gender' => 'female', 'l_type' => 'tdc'],
            ['name' => 'Angelo Miguel Ramos', 'email' => 'instructor3@driveedhub.test', 'contact' => '+63-919-777-3003', 'license' => 'LIC-DH-2024-003', 'bio' => 'TDC specialist. LTO-certified TDC instructor with 6 years experience.', 'branch' => 1, 'course_idx' => 1, 'status' => 'verified', 'avail' => 'unavailable', 'gender' => 'male', 'l_type' => 'pdc'],
            ['name' => 'Sofia Elena Torres', 'email' => 'instructor4@driveedhub.test', 'contact' => '+63-919-777-3004', 'license' => 'LIC-DH-2024-004', 'bio' => 'Motorcycle and Manual Transmission specialist. Former professional rider turned instructor.', 'branch' => 1, 'course_idx' => 1, 'status' => 'rejected', 'avail' => 'available', 'gender' => 'female', 'l_type' => 'pdc'],
            ['name' => 'Juan Dela Cruz Jr.', 'email' => 'instructor5@driveedhub.test', 'contact' => '+63-919-777-3005', 'license' => 'LIC-DH-2024-005', 'bio' => 'Defensive driving specialist.', 'branch' => 0, 'course_idx' => 2, 'status' => 'verified', 'avail' => 'available', 'gender' => 'male', 'l_type' => 'combo'],
        ];
        $instructors = [];
        foreach ($dhInstructors as $inst) {
            $instructors[] = Instructor::updateOrCreate(
                ['email' => $inst['email']],
                [
                    'school_id' => $school->id,
                    'branch_id' => $branches[$inst['branch']]->id,
                    'name' => $inst['name'],
                    'contact' => $inst['contact'],
                    'password' => $hashedPassword,
                    'profile_picture' => 'instructor-licenses/' . $inst['gender'] . '_profile.png',
                    'license_number' => $inst['license'],
                    'license_image' => 'instructor-licenses/license_' . $inst['l_type'] . '.png',
                    'license_status' => $inst['status'],
                    'license_rejection_reason' => $inst['status'] === 'rejected' ? 'Expired document' : null,
                    'restriction_codes' => ['A', 'B'],
                    'bio' => $inst['bio'],
                    'status' => $inst['status'] === 'rejected' ? 'inactive' : 'active',
                    'availability' => $inst['avail'],
                ]
            );
        }

        // Students
        $students = $this->createDriveEdHubStudents($school, $branches, $courses, $hashedPassword);

        // Guests (Base records only, enrollment logic separated later or handled in createDriveEdHubGuests)
        $admins_arr = [$admin, $admin2];
        $guests = $this->createDriveEdHubGuests($school, [], $admins_arr, $branches, $hashedPassword, true); // Pass true to only create users

        $this->command->info('   ✓ All user identities created (Admins, Instructors, Students, Guests)');

        // Link instructors strictly to their primary course for testing specializations
        foreach ($dhInstructors as $idx => $instData) {
            if (isset($instructors[$idx]) && isset($courses[$instData['course_idx']])) {
                $instructors[$idx]->update(['course_specializations' => [$courses[$instData['course_idx']]->id]]);
            }
        }

        $this->command->info('   ✓ Courses with packages created');

        // ── 3.5 SYLLABUS (Ported from ContentProgressSeeder) ──
        $this->seedCourseContentForDriveEdHub($school, $courses);

        // ── 4. INTERACTIONS (Link everything) ──

        // Course assignments for guests (completing the creation)
        $this->createDriveEdHubGuests($school, $courses, $admins_arr, $branches, $hashedPassword, false);

        // Clean, Open Scheduling (Today -> Friday)
        $this->createTimeSlotsAndAssignments($school, $instructors, $courses, $branches);

        // Notifications
        $this->createSampleNotifications($school, $students, $instructors, $admins_arr, $guests);

        $this->command->info('   ✓ Interactions created (Slots, Bookings, Enrollment Requests)');
    }

    private function createDriveEdHubCourses(School $school): array
    {
        $courses = [];

        // 1. TDC
        $tdc = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Theoretical Driving Course (TDC)'],
            ['description' => 'LTO-accredited 15-hour TDC for new applicants. Covers traffic rules, road signs, and defensive driving.', 'type' => 'Theoretical', 'course_type' => 'theoretical', 'license_type' => 'non_professional', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true, 'banner_image' => 'courses/course_tdc.jpg', 'features' => ['Traffic Rules & Regulations', 'Road Signs & Markings', 'Defensive Driving', 'LTO Written Exam Prep']]
        );
        $courses[] = $tdc;
        CoursePackage::updateOrCreate(['course_id' => $tdc->id, 'name' => 'TDC Standard 15-Hour'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 15, 'price' => 2000.00, 'description' => 'Complete 15-hour TDC for new license applicants.', 'is_popular' => true]);
 
        // 2. PDC
        $pdc = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Practical Driving Course (PDC)'],
            ['description' => 'Hands-on practical driving. Master vehicle control, parking, and safe driving.', 'type' => 'Practical', 'course_type' => 'practical', 'license_type' => 'professional', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true, 'banner_image' => 'courses/course_pdc.jpg', 'features' => ['Vehicle Operation Basics', 'Parking Techniques', 'City Driving']]
        );
        $courses[] = $pdc;
        CoursePackage::updateOrCreate(['course_id' => $pdc->id, 'name' => 'PDC 10-Hour Manual'], ['transmission_type' => 'manual', 'vehicle_type' => 'Car', 'training_hours' => 10, 'price' => 5000.00, 'description' => 'Manual driving 10 hours', 'is_popular' => true]);
        CoursePackage::updateOrCreate(['course_id' => $pdc->id, 'name' => 'PDC 10-Hour Automatic'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 10, 'price' => 5500.00, 'description' => 'Automatic driving 10 hours']);
 
        // 3. COMBO
        $combo = Course::updateOrCreate(
            ['school_id' => $school->id, 'title' => 'TDC + PDC Combo Course'],
            ['description' => 'Complete beginner comprehensive package. TDC and PDC bundled together.', 'type' => 'Combo', 'course_type' => 'combo', 'license_type' => 'non_professional', 'vehicle_type' => 'Car', 'status' => 'active', 'is_featured' => true, 'banner_image' => 'courses/course_combo.jpg', 'features' => ['15H Theory Classes', '10H Practical Hand-on', 'License Full Processing Help']]
        );
        $courses[] = $combo;
        CoursePackage::updateOrCreate(['course_id' => $combo->id, 'name' => 'Combo 15H TDC + 10H PDC'], ['transmission_type' => 'automatic', 'vehicle_type' => 'Car', 'training_hours' => 25, 'price' => 6800.00, 'description' => 'Full combined package.']);

        return $courses;
    }

    private function createDriveEdHubStudents(School $school, array $branches, array $courses, string $password): array
    {
        $data = [
            ['name' => 'Juan Miguel Dela Cruz', 'email' => 'student1@driveedhub.test', 'level' => 'new_driver', 'progress' => 100, 'license' => 'verified', 'tdc' => true], // Star Student
            ['name' => 'Maria Victoria Garcia', 'email' => 'student2@driveedhub.test', 'level' => 'new_driver', 'progress' => 45, 'license' => 'pending', 'tdc' => true],
            ['name' => 'Pedro Jose Santos', 'email' => 'student3@driveedhub.test', 'level' => 'new_driver', 'progress' => 12, 'license' => 'none', 'tdc' => false],
            ['name' => 'Ana Patricia Reyes', 'email' => 'student4@driveedhub.test', 'level' => 'new_driver', 'progress' => 0, 'license' => 'none', 'tdc' => false],
            ['name' => 'Carlos Manuel Mendoza', 'email' => 'student5@driveedhub.test', 'level' => 'experienced', 'progress' => 75, 'license' => 'verified', 'tdc' => true],
            ['name' => 'Sofia Angelica Torres', 'email' => 'student6@driveedhub.test', 'level' => 'experienced', 'progress' => 90, 'license' => 'verified', 'tdc' => true],
            ['name' => 'Miguel Francisco Ramos', 'email' => 'student7@driveedhub.test', 'level' => 'new_driver', 'progress' => 20, 'license' => 'none', 'tdc' => false],
            ['name' => 'Isabella Rose Cruz', 'email' => 'student8@driveedhub.test', 'level' => 'new_driver', 'progress' => 5, 'license' => 'none', 'tdc' => false],
            ['name' => 'Diego Emmanuel Fernandez', 'email' => 'student9@driveedhub.test', 'level' => 'new_driver', 'progress' => 0, 'license' => 'none', 'tdc' => false],
            ['name' => 'Luna Marie Martinez', 'email' => 'student10@driveedhub.test', 'level' => 'experienced', 'progress' => 60, 'license' => 'verified', 'tdc' => true],
        ];

        $students = [];
        foreach ($data as $i => $s) {
            $student = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => $s['email']],
                [
                    'name' => $s['name'],
                    'branch_id' => $branches[$i % count($branches)]->id,
                    'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'password' => $password,
                    'status' => 'active',
                    'experience_level' => $s['level'],
                    'enrollment_date' => now()->subDays(rand(7, 60)),
                    'student_license_path' => $s['license'] !== 'none' ? 'instructor-licenses/placeholder.png' : null,
                    'student_license_status' => $s['license'],
                    'student_license_verified_at' => $s['license'] === 'verified' ? now()->subDays(15) : null,
                    'has_passed_theoretical' => $s['tdc'],
                    'theoretical_passed_at' => $s['tdc'] ? now()->subDays(20) : null,
                ]
            );
            $student->role = 'student';
            $student->email_verified_at = $student->email_verified_at ?? now();
            $student->save();
            $students[] = $student;

            $course = $courses[$i % count($courses)];
            $package = $course->packages->first();

            // Only fully approve students who have verified licenses and passed TDC
            $isEligible = $s['license'] === 'verified' && $s['tdc'];
            $enrollmentStatus = $isEligible ? 'approved' : 'pending';
            $paymentStatus = $isEligible ? 'paid' : 'pending';

            $dlCodes = $course->license_type === 'professional' 
                ? ['A', 'A1', 'B', 'B1', 'B2', 'C', 'D', 'BE', 'CE'] 
                : ['A', 'A1', 'B', 'B1', 'B2'];

            $enrollment = EnrollmentRequest::updateOrCreate(
                ['school_id' => $school->id, 'learner_id' => $student->id, 'course_id' => $course->id],
                [
                    'package_id' => $package?->id,
                    'status' => $enrollmentStatus,
                    'payment_status' => $paymentStatus,
                    'experience_level' => $s['level'],
                    'requested_license_type' => 'non_professional',
                    'requested_dl_code' => $dlCodes[array_rand($dlCodes)],
                    'branch_id' => $student->branch_id,
                    'price' => $package ? $package->price : 0,
                    'payment_method' => $isEligible ? 'cash' : 'gcash',
                    'payment_reference' => 'DH-SEED-' . strtoupper(\Illuminate\Support\Str::random(8)),
                    'payment_proof_path' => 'guest-data/guest_receipt.jpg',
                    'credentials_file_path' => $s['license'] !== 'none' ? 'guest-data/guest_permit.jpg' : null,
                    'approved_by' => $isEligible ? 1 : null,
                    'approved_at' => $isEligible ? now()->subDays(10) : null,
                    'enrolled_at' => $isEligible ? now()->subDays(10) : null,
                ]
            );

            // Create progress
            if ($s['progress'] > 0 && $isEligible) {
                \App\Models\Progress::updateOrCreate(
                    ['student_id' => $student->id, 'course_id' => $course->id],
                    [
                        'school_id' => $school->id,
                        'completion_percent' => $s['progress'],
                        'last_updated' => now(),
                        'notes' => 'Seeded progress'
                    ]
                );
            }
        }
        return $students;
    }

    private function createDriveEdHubGuests(School $school, array $courses, array $admins, array $branches, string $password, bool $onlyUsers = false): array
    {
        $guests = [];

        $guestData = [
            'guest1@driveedhub.test' => ['name' => 'Elena Joy Reyes', 'license' => 'none', 'exp' => 'new_driver', 'course_idx' => 0, 'status' => 'pending', 'pay' => 'pending'],
            'guest2@driveedhub.test' => ['name' => 'Mark Anthony Dizon', 'license' => 'pending', 'exp' => 'experienced', 'course_idx' => 2, 'status' => 'pending', 'pay' => 'paid', 'cancellation' => true], // Cancellation requested — status must stay pending
            'guest3@driveedhub.test' => ['name' => 'Jamie Lyn Pascual', 'license' => 'none', 'exp' => 'new_driver', 'course_idx' => 1, 'status' => 'pending', 'pay' => 'paid'],
            'guest4@driveedhub.test' => ['name' => 'Carlo Miguel Bautista', 'license' => 'verified', 'exp' => 'new_driver'], // Registered but no request yet
            'guest5@driveedhub.test' => ['name' => 'Angelica Mae Soriano', 'license' => 'rejected', 'exp' => 'new_driver', 'course_idx' => 2, 'status' => 'rejected', 'pay' => 'cancelled'],
            'guest6@driveedhub.test' => ['name' => 'Miguel Francisco Ramos', 'license' => 'none', 'exp' => 'new_driver'],
            'guest7@driveedhub.test' => ['name' => 'Isabella Rose Cruz', 'license' => 'pending', 'exp' => 'new_driver'],
            'guest8@driveedhub.test' => ['name' => 'Diego Fernandez', 'license' => 'none', 'exp' => 'new_driver', 'course_idx' => 1, 'status' => 'pending', 'pay' => 'pending'],
            'guest9@driveedhub.test' => ['name' => 'Luna Martinez', 'license' => 'none', 'exp' => 'new_driver', 'course_idx' => 0, 'status' => 'rejected', 'pay' => 'cancelled'],
            'guest10@driveedhub.test' => ['name' => 'Sofia Torres', 'license' => 'verified', 'exp' => 'experienced', 'course_idx' => 2, 'status' => 'approved', 'pay' => 'paid'],
        ];

        foreach ($guestData as $email => $data) {
            $g = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => $email],
                [
                    'name' => $data['name'],
                    'contact' => '+63-919-800-' . rand(1000, 9999),
                    'password' => $password,
                    'status' => 'active',
                    'student_license_status' => $data['license'],
                    'student_license_verified_at' => $data['license'] === 'verified' ? now()->subDays(5) : null,
                    'student_license_rejection_reason' => $data['license'] === 'rejected' ? 'Blurred ID photo' : null,
                    'experience_level' => $data['exp'],
                    'branch_id' => $branches[count($guests) % count($branches)]->id,
                ]
            );
            
            // Logic: promote to student only if fully approved AND not cancelled
            $isFullyApproved = ($data['status'] ?? '') === 'approved' && !($data['cancellation'] ?? false);
            $g->role = $isFullyApproved ? 'student' : 'guest';
            
            $g->email_verified_at = $g->email_verified_at ?? now();
            $g->save();

            $guests[] = $g;

            if ($onlyUsers) continue;

            if (isset($data['course_idx']) && isset($courses[$data['course_idx']])) {
                $course = $courses[$data['course_idx']];
                $package = CoursePackage::where('course_id', '=', $course->id)->first();

                \App\Models\EnrollmentRequest::updateOrCreate(
                    ['school_id' => $school->id, 'learner_id' => $g->id, 'course_id' => $course->id],
                    [
                        'package_id' => $package?->id,
                        'status' => $data['status'],
                        'payment_status' => $data['pay'],
                        'experience_level' => $data['exp'],
                        'requested_license_type' => 'non_professional',
                        'branch_id' => $g->branch_id,
                        'price' => $package ? $package->price : 0,
                        'payment_method' => 'gcash',
                        'payment_reference' => 'DH-REF-' . strtoupper(\Illuminate\Support\Str::random(8)),
                        'payment_proof_path' => ($data['pay'] === 'paid' || $data['pay'] === 'pending') ? 'guest-data/guest_receipt.jpg' : null,
                        'credentials_file_path' => $data['license'] !== 'none' ? 'guest-data/guest_permit.jpg' : null,
                        'approved_by' => ($data['status'] === 'approved' && !empty($admins)) ? $admins[0]->id : null,
                        'approved_at' => $data['status'] === 'approved' ? now()->subDays(5) : null,
                        'enrolled_at' => $data['status'] === 'approved' ? now()->subDays(5) : null,
                        'cancellation_requested' => $data['cancellation'] ?? false,
                        'cancellation_reason' => ($data['cancellation'] ?? false) ? 'Decided to enroll in a different branch.' : null,
                    ]
                );

                if ($email === 'guest1@driveedhub.test') {
                    $g->update([
                        'student_license_path' => 'guest-data/guest_permit.jpg',
                        'student_license_status' => 'pending'
                    ]);
                }
            }
        }

        return $guests;
    }

    private function seedCourseContentForDriveEdHub(School $school, array $courses): void
    {
        $this->command->info('');
        $this->command->info("   Creating course syllabus with practice questions...");

        foreach ($courses as $course) {
            $isTheoretical = ($course->course_type === 'theoretical');
            $modules = $isTheoretical ? $this->getTheoreticalModules() : $this->getPracticalModules($course);

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

                // Seed Questions for every module
                $this->seedQuestionsForModule($school, $course, $module);
            }
        }
    }

    private function seedQuestionsForModule(School $school, Course $course, CourseModule $module): void
    {
        $questions = [
            [
                'text' => 'What is the primary meaning of a solid yellow line on the road?',
                'type' => 'multiple_choice',
                'options' => ['Overtaking permitted', 'No overtaking zone', 'Parking allowed', 'Yield to pedestrians'],
                'answer' => 'No overtaking zone'
            ],
            [
                'text' => 'A flashing red traffic light should be treated as a STOP sign.',
                'type' => 'true_false',
                'options' => ['True', 'False'],
                'answer' => 'True'
            ],
            [
                'text' => 'Under RA 10913, using a mobile phone while driving is permitted if you are using a hands-free device.',
                'type' => 'true_false',
                'options' => ['True', 'False'],
                'answer' => 'True'
            ],
            [
                'text' => 'What is the standard following distance in seconds under normal conditions?',
                'type' => 'multiple_choice',
                'options' => ['1 second', '2 seconds', '3 seconds', '5 seconds'],
                'answer' => '3 seconds'
            ]
        ];

        foreach ($questions as $index => $qData) {
            \App\Models\Question::updateOrCreate(
                ['school_id' => $school->id, 'course_id' => $course->id, 'question_text' => $qData['text']],
                [
                    'question_type' => $qData['type'],
                    'options' => $qData['options'],
                    'correct_answer' => $qData['answer'],
                    'default_points' => 1
                ]
            )->assessments()->syncWithoutDetaching([$module->id => ['sort_order' => $index + 1, 'points' => 1]]);
        }
    }

    private function getTheoreticalModules(): array
    {
        return [
            [
                'title' => 'Introduction to Philippine Traffic Laws',
                'description' => 'Overview of RA 4136, RA 10913, and other relevant legislation.',
                'module_type' => 'lesson',
                'lessons' => [
                    ['title' => 'History of Philippine Traffic Laws', 'content' => "## Key Laws\n- **RA 4136** - Land Transportation Code\n- **RA 10913** - Anti-Distracted Driving\n\nUnderstand your rights and responsibilities as a driver."],
                    ['title' => 'LTO Rules and Licensing', 'content' => "## License Types\n- Student Permit\n- Non-Professional\n- Professional\n\nEnsure you meet all requirements before applying."],
                ],
            ],
            [
                'title' => 'Road Signs and Pavement Markings',
                'description' => 'Identifying regulatory, warning, and informative signs.',
                'module_type' => 'lesson',
                'lessons' => [
                    ['title' => 'Regulatory Signs', 'content' => "Regulatory signs inform users of laws. These MUST be obeyed.\n- No Entry\n- Speed Limits\n- No Turn Signs"],
                    ['title' => 'Warning and Hazard Signs', 'content' => "Warning signs alert you to hazards ahead. Typically yellow diamond-shaped.\n- Curve Ahead\n- School Zone\n- Slippery Road"],
                ],
            ],
        ];
    }

    private function getPracticalModules(object $course): array
    {
        return [
            [
                'title' => 'Vehicle Controls & Pre-Drive',
                'description' => 'Familiarization with controls and safety checks.',
                'module_type' => 'lesson',
                'lessons' => [
                    ['title' => 'Dashboard & Pedals', 'content' => "Identify all indicators and pedal functions.\n- Accelerator\n- Brake\n- Clutch (if Manual)"],
                    ['title' => 'Safety Setup', 'content' => "Adjust seat, mirrors, and fasten seatbelt. Ensure full visibility."],
                ],
            ],
        ];
    }

    protected function createTimeSlotsAndAssignments(School $school, array $instructors, array $courses, array $branches): void
    {
        $this->command->info('   Generating clean scheduling blocks (Mixed Past & Future)...');

        $times = [['08:00:00', '10:00:00'], ['10:00:00', '12:00:00'], ['13:00:00', '15:00:00'], ['15:00:00', '17:00:00']];
        $now = now();
        $allCreatedSlots = [];

        // Seed 4 days in past, 4 days in future
        for ($d = -4; $d <= 4; $d++) {
            $dateObj = $now->copy()->addDays($d);
            if ($dateObj->dayOfWeek == 0) continue; // Skip Sunday

            foreach ($courses as $cIdx => $course) {
                $branch = $branches[$cIdx % count($branches)];
                $timeIndex = rand(0, 3);
                
                $slot = \App\Models\TimeSlot::create([
                    'school_id' => $school->id,
                    'branch_id' => $branch->id,
                    'course_id' => $course->id,
                    'session_type' => $course->course_type === 'combo' ? (rand(0,1) ? 'theoretical' : 'practical') : $course->course_type,
                    'date' => $dateObj->format('Y-m-d'),
                    'start_time' => $times[$timeIndex][0],
                    'end_time' => $times[$timeIndex][1],
                    'status' => 'open',
                    'max_instructors' => 1,
                    'max_students' => $course->course_type === 'theoretical' ? 20 : 1,
                ]);

                $instructor = $instructors[$cIdx % count($instructors)];
                $slot->instructors()->attach($instructor->id, ['school_id' => $school->id, 'assignment_type' => 'admin_assigned']);
                $allCreatedSlots[] = $slot;
            }
        }

        $this->createSampleBookings($school, $allCreatedSlots);
    }

    private function createSampleBookings(School $school, array $slots): void
    {
        $this->command->info('   Creating distributed sample bookings (Done, Verified, Scheduled)...');
        
        $students = Student::where('school_id', $school->id)->where('role', 'student')->get();
        if ($students->isEmpty()) return;

        foreach ($slots as $idx => $slot) {
            $instructor = $slot->instructors->first();
            if (!$instructor) continue;

            $slotDate = \Carbon\Carbon::parse($slot->date);
            $isPast = $slotDate->isPast();
            $numToBook = $slot->max_students > 1 ? rand(2, 4) : 1;

            for ($i = 0; $i < $numToBook; $i++) {
                $student = $students[($idx + $i) % $students->count()];
                
                if ($isPast) {
                    $roll = rand(1, 100);
                    $status = $roll > 70 ? 'done' : 'completed'; // 30% awaiting verification
                } else {
                    $status = 'scheduled';
                }

                $enrollment = \App\Models\EnrollmentRequest::where('learner_id', $student->id)->where('course_id', $slot->course_id)->first();

                \App\Models\Booking::create([
                    'school_id' => $school->id,
                    'branch_id' => $slot->branch_id,
                    'student_id' => $student->id,
                    'instructor_id' => $instructor->id,
                    'course_id' => $slot->course_id,
                    'enrollment_request_id' => $enrollment ? $enrollment->id : null,
                    'time_slot_id' => $slot->id,
                    'booking_date' => $slot->date,
                    'scheduled_at' => \Carbon\Carbon::parse($slot->date)->format('Y-m-d') . ' ' . $slot->start_time,
                    'status' => $status,
                    'attendance_status' => in_array($status, ['done', 'completed']) ? 'present' : 'pending',
                    'payment_status' => 'paid',
                ]);

                if ($status === 'completed' && $enrollment) {
                    \App\Models\SessionCompletion::create([
                        'school_id' => $school->id,
                        'enrollment_id' => $enrollment->id,
                        'instructor_id' => $instructor->id,
                        'session_type' => $slot->session_type,
                        'session_date' => $slot->date,
                        'session_time' => $slot->start_time,
                        'hours_completed' => 2.0,
                        'notes' => 'Verified session.',
                    ]);
                }
            }
        }
    }

    private function createSampleNotifications(School $school, array $students, array $instructors, array $admins, array $guests): void
    {
        // Notifications logic...
    }
}
