<?php

namespace Database\Seeders\Traits;

use App\Models\School;
use App\Models\Branch;
use App\Models\CoursePackage;
use App\Models\TimeSlot;
use App\Models\ScheduleInstructor;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Progress;
use App\Models\EnrollmentRequest;
use App\Models\Notification;
use App\Models\Student;
use App\Models\Vehicle;
use App\Models\VehicleCategory;

trait HandlesSchoolSeeding
{
    private array $maleFirst = [
        'Juan Carlos', 'Pedro Miguel', 'Roberto Luis', 'Fernando Jose', 'Ricardo Antonio',
        'Angelo', 'Domingo', 'Francisco', 'Guillermo', 'Isidro',
        'Kenneth', 'Manuel', 'Oscar', 'Salvador', 'Ulysses',
        'Wilfredo', 'Abelardo', 'Benigno', 'Diosdado', 'Felix',
        'Hernando', 'Juanito', 'Leonardo', 'Nemesio', 'Primitivo',
        'Remigio', 'Teodoro', 'Enrique', 'Arturo', 'Cesar',
        'Danilo', 'Edgar', 'Florante', 'Gerardo', 'Hector',
        'Jaime', 'Lorenzo', 'Noel', 'Pablo', 'Renzo',
        'Tito', 'Victor', 'Xavier', 'Marco', 'Diego',
        'Rafael', 'Gabriel', 'Daniel', 'Luis', 'Carlos',
    ];

    private array $femaleFirst = [
        'Maria', 'Ana', 'Sofia', 'Isabella', 'Carmen',
        'Rosa', 'Elena', 'Patricia', 'Cristina', 'Angela',
        'Valentina', 'Lucia', 'Gabriela', 'Natalia', 'Teresa',
        'Gloria', 'Paulina', 'Cecilia', 'Bianca', 'Diana',
        'Mila', 'Olive', 'Hazel', 'Jasmine', 'Karla',
        'Nora', 'Lourdes', 'Rosalinda', 'Thelma', 'Victoria',
        'Yolanda', 'Zenaida', 'Imelda', 'Corazon', 'Esperanza',
        'Felicidad', 'Leonora', 'Milagros', 'Erlinda', 'Myrna',
        'Teresita', 'Gemma', 'Iris', 'Queenie', 'Samantha',
        'Ursula', 'Yvette', 'Florence', 'Josephine', 'Beatriz',
    ];

    private array $lastNames = [
        'Santos', 'Reyes', 'Cruz', 'Garcia', 'Torres',
        'Ramos', 'Flores', 'Lopez', 'Rivera', 'Hernandez',
        'Mendoza', 'Dizon', 'Navarro', 'Medina', 'Jimenez',
        'Alvarez', 'Ruiz', 'Sanchez', 'Ramirez', 'Castillo',
        'Villanueva', 'Bautista', 'Gonzales', 'Fernandez', 'Martinez',
        'Rodriguez', 'Espiritu', 'Manansala', 'Cunanan', 'David',
        'Pineda', 'Ocampo', 'Pangilinan', 'Aquino', 'Galang',
        'Ilagan', 'Lara', 'Manalo', 'Oliva', 'Salazar',
        'Tolentino', 'Umali', 'Valencia', 'Yap', 'Cordero',
        'Enriquez', 'Guevara', 'Hidalgo', 'Ignacio', 'Javier',
        'Laurel', 'Mercado', 'Nicolas', 'Panganiban', 'Quinto',
        'Roxas', 'Soriano', 'Tan', 'Velasco', 'Zamora',
    ];

    protected function nameAt(int $index): string
    {
        $allFirst = array_merge($this->maleFirst, $this->femaleFirst);
        $fnCount = count($allFirst);
        $lnCount = count($this->lastNames);

        $fnIdx = $index % $fnCount;
        $lnIdx = (int) floor($index / $fnCount) + $index % $lnCount;
        $lnIdx = $lnIdx % $lnCount;

        if ($index >= $fnCount) {
            $lnIdx = ($lnIdx + (int) floor($index / $fnCount)) % $lnCount;
        }

        return $allFirst[$fnIdx] . ' ' . $this->lastNames[$lnIdx];
    }

    protected function makeEmail(string $name, string $domain): string
    {
        $slug = strtolower(trim($name));
        $slug = str_replace(['ñ', ' '], ['n', '.'], $slug);
        return "{$slug}@{$domain}";
    }

    protected function createBranches(School $school, array $branchData): array
    {
        $branches = [];
        foreach ($branchData as $index => $data) {
            $branches[] = Branch::updateOrCreate(
                ['school_id' => $school->id, 'name' => $data['name']],
                [
                    'address' => $data['address'] ?? null,
                    'contact_number' => $data['contact_number'] ?? null,
                    'email' => $data['email'] ?? null,
                    'is_active' => true, 'sort_order' => $index + 1,
                ]
            );
        }
        return $branches;
    }

    protected function createTimeSlotsAndAssignments(School $school, array $instructors, array $courses, array $branches): void
    {
        $times = [
            ['08:00:00', '09:00:00'], ['09:00:00', '10:00:00'], ['10:00:00', '11:00:00'], ['11:00:00', '12:00:00'],
            ['13:00:00', '14:00:00'], ['14:00:00', '15:00:00'], ['15:00:00', '16:00:00'], ['16:00:00', '17:00:00'],
        ];
        $bi = 0;
        for ($day = 0; $day < 14; $day++) {
            $date = now()->addDays($day)->format('Y-m-d');
            if (now()->addDays($day)->dayOfWeek == 0) continue;
            foreach ($courses as $course) {
                $sc = rand(2, min(4, count($times))); // Fewer slots per course per day to avoid overlap overload
                $ds = array_rand($times, $sc);
                if (!is_array($ds)) $ds = [$ds];
                foreach ($ds as $si) {
                    $branch = $branches[$bi % count($branches)];
                    $bi++;
                    $ts = TimeSlot::create([
                        'school_id' => $school->id, 
                        'branch_id' => $branch->id, 
                        'course_id' => $course->id, 
                        'date' => $date, 
                        'start_time' => $times[$si][0], 
                        'end_time' => $times[$si][1], 
                        'status' => 'open', 
                        'max_instructors' => 1,
                        'max_students' => rand(1, 4), // Added max students capacity
                    ]);
                    if (!empty($instructors)) {
                        ScheduleInstructor::create([
                            'time_slot_id' => $ts->id, 
                            'instructor_id' => $instructors[array_rand($instructors)]->id, 
                            'school_id' => $school->id, 
                            'assignment_type' => 'admin_assigned'
                        ]);
                    }
                }
            }
        }
    }

    protected function createBookingsAndPayments(School $school, array $students, array $instructors, array $courses, array $branches, int $count = 10): void
    {
        if (empty($students) || empty($instructors) || empty($courses)) return;
        $statuses = ['confirmed', 'confirmed', 'confirmed', 'completed', 'completed', 'pending', 'cancelled'];
        for ($i = 0; $i < $count; $i++) {
            $student = $students[array_rand($students)];
            $instructor = $instructors[array_rand($instructors)];
            $course = $courses[array_rand($courses)];
            $package = CoursePackage::where('course_id', '=', $course->id)->inRandomOrder()->first();
            if (!$package) continue;
            $status = $statuses[array_rand($statuses)];
            $bookingDate = $status == 'completed' ? now()->subDays(rand(1, 30)) : now()->addDays(rand(1, 14));
            $branch = $branches[array_rand($branches)];
            $booking = Booking::create([
                'school_id' => $school->id, 'branch_id' => $branch->id,
                'student_id' => $student->id, 'instructor_id' => $instructor->id,
                'course_id' => $course->id, 'package_id' => $package->id,
                'scheduled_at' => $bookingDate, 'booking_date' => $bookingDate,
                'status' => $status, 'payment_status' => $status == 'completed' ? 'paid' : 'pending',
                'total_amount' => $package->price,
                'notes' => $status == 'cancelled' ? 'Student requested cancellation' : null,
                'cancelled_by' => $status == 'cancelled' ? 'student' : null,
                'cancellation_reason' => $status == 'cancelled' ? 'Personal emergency' : null,
                'cancelled_at' => $status == 'cancelled' ? now() : null,
                'attendance_status' => $status == 'completed' ? 'attended' : null,
                'session_status' => $status == 'completed' ? 'completed' : null,
            ]);
            if ($status == 'completed') {
                $method = rand(0, 1) === 0 ? 'on_site' : 'gcash';

                Payment::create([
                    'school_id' => $school->id,
                    'branch_id' => $booking->branch_id,
                    'booking_id' => $booking->id,
                    'payer_user_id' => $student->id,
                    'amount' => $package->price,
                    'paid_on' => $bookingDate,
                    'method' => $method,
                    'status' => 'approved',
                    'or_number' => $method === 'on_site' ? ('OR-SEED-' . strtoupper((string) \Illuminate\Support\Str::random(8))) : null,
                    'reference' => $method === 'gcash' ? ('GCASH-SEED-' . strtoupper((string) \Illuminate\Support\Str::random(8))) : null,
                ]);
                $prog = Progress::where('student_id', '=', $student->id)->where('course_id', '=', $course->id)->first();
                if ($prog) { $prog->update(['completion_percent' => min(100, $prog->completion_percent + rand(10, 25)), 'last_updated' => now(), 'notes' => 'Good progress.']); }
                else { Progress::create(['school_id' => $school->id, 'student_id' => $student->id, 'course_id' => $course->id, 'completion_percent' => rand(10, 40), 'last_updated' => now(), 'notes' => 'Good progress.']); }
            }
        }
    }

    protected function createVehicles(School $school, array $branches): void
    {
        $categories = $school->vehicleCategories;
        if ($categories->isEmpty()) {
            $defaults = ['Sedan', 'SUV', 'MPV', 'Hatchback', 'Motorcycle', 'Truck (Heavy)'];
            foreach ($defaults as $cat) {
                VehicleCategory::create([
                    'school_id' => $school->id,
                    'name' => $cat
                ]);
            }
            $categories = $school->vehicleCategories()->get();
        }

        $vehicleData = [
            ['model' => 'Toyota Vios 2024', 'plate' => 'NQA-1001', 'trans' => 'automatic', 'cat' => 'Sedan'],
            ['model' => 'Mitsubishi Mirage G4', 'plate' => 'NQA-1002', 'trans' => 'manual', 'cat' => 'Sedan'],
            ['model' => 'Toyota Fortuner', 'plate' => 'NQA-2001', 'trans' => 'automatic', 'cat' => 'SUV'],
            ['model' => 'Honda Click 125i', 'plate' => 'NQA-3001', 'trans' => 'automatic', 'cat' => 'Motorcycle'],
            ['model' => 'Suzuki Ertiga', 'plate' => 'NQA-4001', 'trans' => 'manual', 'cat' => 'MPV'],
            ['model' => 'Isuzu Forward', 'plate' => 'NQA-5001', 'trans' => 'manual', 'cat' => 'Truck (Heavy)'],
        ];

        foreach ($vehicleData as $index => $v) {
            $branch = $branches[$index % count($branches)];
            $category = $categories->where('name', $v['cat'])->first() ?? $categories->first();
            
            Vehicle::updateOrCreate(
                ['school_id' => $school->id, 'license_plate' => $v['plate']],
                [
                    'branch_id' => $branch->id,
                    'category_id' => $category->id,
                    'model' => $v['model'],
                    'transmission' => $v['trans'],
                    'status' => 'active',
                ]
            );
        }
    }

    protected function createGuestsAndEnrollmentRequests(School $school, array $courses, array $admins, string $password): array
    {
        $slug = $school->slug;
        $guestData = [
            ['name' => 'Elena Joy Reyes', 'email' => "guest1@{$slug}.test", 'license_status' => 'none', 'enrollment_status' => 'pending'],
            ['name' => 'Mark Anthony Dizon', 'email' => "guest2@{$slug}.test", 'license_status' => 'pending', 'enrollment_status' => 'pending', 'cancellation' => true],
            ['name' => 'Jamie Lyn Pascual', 'email' => "guest3@{$slug}.test", 'license_status' => 'verified', 'enrollment_status' => 'rejected'],
            ['name' => 'Carlo Miguel Bautista', 'email' => "guest4@{$slug}.test", 'license_status' => 'none', 'enrollment_status' => null],
        ];
        $guests = [];
        foreach ($guestData as $g) {
            $guest = Student::updateOrCreate(
                ['school_id' => $school->id, 'email' => $g['email']],
                [
                    'name' => $g['name'], 
                    'contact' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999), 
                    'password' => $password, 
                    'status' => 'active', 
                    'student_license_status' => $g['license_status'], 
                    'student_license_verified_at' => $g['license_status'] === 'verified' ? now()->subDays(5) : null
                ]
            );
            $guest->role = 'guest';
            if (in_array($school->slug, ['lyspeed-driving', 'drived-hub'], true)) {
                $guest->email_verified_at = $guest->email_verified_at ?? now();
                $guest->verification_code = null;
                $guest->verification_code_expires_at = null;
                $guest->verification_attempts = 0;
                $guest->last_verification_attempt_at = null;
            }
            $guest->save();
            $guests[] = $guest;

            if ($g['enrollment_status'] && !empty($courses)) {
                $course = $courses[array_rand($courses)];
                $package = CoursePackage::where('course_id', '=', $course->id)->first();
                
                $ed = [
                    'school_id' => $school->id, 
                    'learner_id' => $guest->id, 
                    'course_id' => $course->id, 
                    'status' => $g['enrollment_status'], 
                    'payment_status' => 'pending', 
                    'experience_level' => 'new_driver', 
                    'requested_license_type' => $course->license_type ?? 'non_professional',
                    'price' => $package ? $package->price : 0, // Added price support
                    'payment_method' => 'gcash', // Sample payment method
                    'payment_reference' => 'REF-' . strtoupper(\Illuminate\Support\Str::random(10)), // Sample reference
                ];

                if (isset($g['cancellation']) && $g['cancellation']) {
                    $ed['cancellation_requested'] = true;
                    $ed['cancellation_reason'] = 'The student decided to enroll in a different branch.';
                }

                if ($g['enrollment_status'] === 'rejected') $ed['remarks'] = 'Incomplete documentation. Please re-submit with valid credentials.';
                if ($g['enrollment_status'] === 'approved' && !empty($admins)) { 
                    $ed['approved_by'] = $admins[0]->id; 
                    $ed['approved_at'] = now()->subDays(2); 
                    $ed['enrolled_at'] = now()->subDays(2); 
                }
                EnrollmentRequest::updateOrCreate(['school_id' => $school->id, 'learner_id' => $guest->id, 'course_id' => $course->id], $ed);
            }
        }
        return $guests;
    }

    protected function createSampleNotifications(School $school, array $students, array $instructors, array $admins, array $guests): void
    {
        $slug = $school->slug;
        if (!empty($admins)) {
            Notification::send($admins[0], 'new_enrollment_request', 'New Enrollment Request', 'A new student has requested enrollment.', 'enrollment', "/{$slug}/admin/enrollments");
            Notification::send($admins[0], 'license_uploaded', 'License Pending Review', 'A student has uploaded a license for verification.', 'license', "/{$slug}/admin/enrollments");
        }
        if (!empty($students)) {
            Notification::send($students[0], 'enrollment_approved', 'Enrollment Approved!', 'Your enrollment has been approved. Welcome!', 'success', "/{$slug}/student");
            Notification::send($students[0], 'session_reminder', 'Upcoming Session', 'You have a driving session tomorrow.', 'session', "/{$slug}/student/schedule");
            if (count($students) > 1) Notification::send($students[1], 'session_reminder', 'Session Tomorrow', 'Reminder: practical session tomorrow morning.', 'session', "/{$slug}/student/schedule");
        }
        if (!empty($instructors)) Notification::send($instructors[0], 'session_reminder', 'Upcoming Session', 'You have a driving session tomorrow at 9:00 AM.', 'session', "/{$slug}/instructor/my-schedule");
        if (!empty($guests)) {
            Notification::send($guests[0], 'enrollment_received', 'Request Submitted', 'Your enrollment request is under review.', 'enrollment', "/{$slug}/guest/enrollment-requests");
            if (count($guests) > 2) Notification::send($guests[2], 'enrollment_rejected', 'Request Update', 'Your enrollment request was not approved.', 'warning', "/{$slug}/guest/enrollment-requests");
        }
    }
}
