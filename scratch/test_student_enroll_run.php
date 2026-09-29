<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use App\Models\Student;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\EnrollmentRequest;
use App\Http\Requests\StoreEnrollmentRequestRequest;
use App\Http\Controllers\StudentController;

$school = School::where('slug', 'drived-hub')->first();
$course = Course::where('school_id', $school->id)->where('course_type', 'theoretical')->first();
$package = $course->packages()->first();

// Create a clean test student with role 'student'
$testStudent = Student::create([
    'school_id' => $school->id,
    'branch_id' => 1,
    'name' => 'Test Enroll Student',
    'email' => 'test_enroll_' . time() . '@driveedhub.test',
    'password' => bcrypt('password'),
    'role' => 'student',
    'status' => 'active',
    'experience_level' => 'new_driver',
    'has_passed_theoretical' => false,
]);

echo "Created test student ID: {$testStudent->id}" . PHP_EOL;

auth()->guard('student')->setUser($testStudent);

$request = StoreEnrollmentRequestRequest::create(
    "/{$school->slug}/student/enroll/{$course->id}",
    'POST',
    [
        'experience_level' => 'new_driver',
        'package_id' => $package->id,
        'branch_id' => 1,
        'requested_dl_code' => 'A',
        'notes' => 'Enrollment test',
    ]
);
$request->setRouteResolver(function() use ($school, $course) {
    $route = new \Illuminate\Routing\Route('POST', '{school}/student/enroll/{course}', []);
    $route->parameters = ['school' => $school, 'course' => $course];
    return $route;
});

$controller = app()->make(StudentController::class);

try {
    $response = $controller->enroll($request, $school, $course);
    echo "Enroll Response Type: " . get_class($response) . PHP_EOL;
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "Target URL: " . $response->getTargetUrl() . PHP_EOL;
        echo "Session errors: " . json_encode(session('errors') ? session('errors')->all() : []) . PHP_EOL;
        echo "Session success: " . session('success') . PHP_EOL;
        echo "Session error: " . session('error') . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo "CAUGHT EXCEPTION: " . $e->getMessage() . PHP_EOL;
    echo "At: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
