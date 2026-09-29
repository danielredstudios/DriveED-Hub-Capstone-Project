<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Admin;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\EnrollmentRequest;
use Illuminate\Support\Facades\Auth;

echo "=== DIAGNOSTIC TEST START ===" . PHP_EOL;

$school = School::where('slug', 'drived-hub')->first();
echo "School: {$school->name} (ID: {$school->id})" . PHP_EOL;

// 1. Test Instructor Student List
echo PHP_EOL . "--- TEST 1: Instructor Student List ---" . PHP_EOL;
$instructor = Instructor::where('school_id', $school->id)->first();
echo "Instructor: {$instructor->name} (ID: {$instructor->id})" . PHP_EOL;

try {
    // Check what route / controller handles instructor students
    $controller = app()->make(App\Http\Controllers\InstructorController::class);
    Auth::guard('instructor')->setUser($instructor);
    $request = Illuminate\Http\Request::create("/{$school->slug}/instructor/students", 'GET');
    $response = $controller->students($request, $school);
    echo "Instructor students() returned successfully! Type: " . get_class($response) . PHP_EOL;
    if ($response instanceof \Illuminate\View\View) {
        echo "View name: " . $response->getName() . PHP_EOL;
        // Try to render the view
        $html = $response->render();
        echo "View rendered successfully, length: " . strlen($html) . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo "ERROR in Instructor students: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

// 2. Test Admin Student List
echo PHP_EOL . "--- TEST 2: Admin Student List ---" . PHP_EOL;
$admin = Admin::where('school_id', $school->id)->first();
echo "Admin: {$admin->name} (ID: {$admin->id}, Role: {$admin->role})" . PHP_EOL;

try {
    $adminController = app()->make(App\Http\Controllers\AdminController::class);
    Auth::guard('admin')->setUser($admin);
    $request = Illuminate\Http\Request::create("/{$school->slug}/admin/students", 'GET');
    if (method_exists($adminController, 'students')) {
        $response = $adminController->students($request, $school);
        echo "AdminController::students() returned! Type: " . get_class($response) . PHP_EOL;
        if ($response instanceof \Illuminate\View\View) {
            $html = $response->render();
            echo "Admin view rendered successfully, length: " . strlen($html) . PHP_EOL;
        }
    } else {
        echo "AdminController::students does not exist! Checking routes..." . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo "ERROR in Admin students: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

// 3. Test Student Enrollment
echo PHP_EOL . "--- TEST 3: Student Enrollment ---" . PHP_EOL;
$student = Student::where('role', 'student')->where('school_id', $school->id)->first();
$course = Course::where('school_id', $school->id)->first();
$package = CoursePackage::where('course_id', $course->id)->first();

echo "Student: {$student->name} (ID: {$student->id})" . PHP_EOL;
echo "Course: {$course->title} (ID: {$course->id})" . PHP_EOL;

try {
    Auth::guard('student')->setUser($student);
    $studentController = app()->make(App\Http\Controllers\StudentController::class);
    
    // Check route model binding or direct call
    $enrollRequest = App\Http\Requests\StoreEnrollmentRequestRequest::create(
        "/{$school->slug}/student/enroll/{$course->id}",
        'POST',
        [
            'experience_level' => 'new_driver',
            'package_id' => $package ? $package->id : null,
            'branch_id' => $student->branch_id,
            'notes' => 'Test enrollment',
            'requested_dl_code' => 'A'
        ]
    );
    $enrollRequest->setRouteResolver(function() use ($school, $course) {
        $route = new \Illuminate\Routing\Route('POST', '{school}/student/enroll/{course}', []);
        $route->parameters = ['school' => $school, 'course' => $course];
        return $route;
    });
    
    $response = $studentController->enroll($enrollRequest, $school, $course);
    echo "StudentController::enroll returned! Type: " . get_class($response) . PHP_EOL;
} catch (\Throwable $e) {
    echo "ERROR in StudentController::enroll: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

// 4. Test Guest Enrollment
echo PHP_EOL . "--- TEST 4: Guest Enrollment ---" . PHP_EOL;
$guest = Student::where('role', 'guest')->where('school_id', $school->id)->first();
echo "Guest: {$guest->name} (ID: {$guest->id})" . PHP_EOL;

try {
    Auth::guard('student')->setUser($guest);
    $guestController = app()->make(App\Http\Controllers\GuestController::class);
    
    $enrollRequest = App\Http\Requests\StoreEnrollmentRequestRequest::create(
        "/{$school->slug}/guest/enroll/{$course->id}",
        'POST',
        [
            'experience_level' => 'new_driver',
            'package_id' => $package ? $package->id : null,
            'branch_id' => $guest->branch_id,
            'notes' => 'Test guest enrollment',
            'requested_dl_code' => 'A'
        ]
    );
    $enrollRequest->setRouteResolver(function() use ($school, $course) {
        $route = new \Illuminate\Routing\Route('POST', '{school}/guest/enroll/{course}', []);
        $route->parameters = ['school' => $school, 'course' => $course];
        return $route;
    });
    
    $response = $guestController->enroll($enrollRequest, $school, $course);
    echo "GuestController::enroll returned! Type: " . get_class($response) . PHP_EOL;
} catch (\Throwable $e) {
    echo "ERROR in GuestController::enroll: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

// 5. Test Admin Enrollment Approval / View
echo PHP_EOL . "--- TEST 5: Admin Enrollment View/Approve ---" . PHP_EOL;
try {
    Auth::guard('admin')->setUser($admin);
    $enrollAdminController = app()->make(App\Http\Controllers\EnrollmentRequestController::class);
    $request = Illuminate\Http\Request::create("/{$school->slug}/admin/enrollments", 'GET');
    $response = $enrollAdminController->index($request, $school);
    echo "EnrollmentRequestController::index returned! Type: " . get_class($response) . PHP_EOL;
    if ($response instanceof \Illuminate\View\View) {
        $html = $response->render();
        echo "Enrollment admin view rendered, length: " . strlen($html) . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo "ERROR in EnrollmentRequestController::index: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

echo "=== DIAGNOSTIC TEST END ===" . PHP_EOL;
