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
use Illuminate\Http\UploadedFile;

$school = School::where('slug', 'drived-hub')->first();
$courses = Course::where('school_id', $school->id)->get();

foreach ($courses as $course) {
    echo "=== TESTING COURSE: {$course->title} (type: {$course->course_type}) ===" . PHP_EOL;
    $package = $course->packages()->first();

    $student = Student::create([
        'school_id' => $school->id,
        'branch_id' => 1,
        'name' => 'Test Student ' . $course->id,
        'email' => 'test_' . $course->id . '_' . time() . '@driveedhub.test',
        'password' => bcrypt('password'),
        'role' => 'student',
        'status' => 'active',
        'experience_level' => 'new_driver',
        'has_passed_theoretical' => true,
        'student_license_status' => 'verified',
    ]);

    auth()->guard('student')->setUser($student);

    // Test with dummy file
    $file = UploadedFile::fake()->create('license.jpg', 100, 'image/jpeg');

    $request = StoreEnrollmentRequestRequest::create(
        "/{$school->slug}/student/enroll/{$course->id}",
        'POST',
        [
            'experience_level' => 'new_driver',
            'package_id' => $package ? $package->id : null,
            'branch_id' => 1,
            'requested_dl_code' => 'A',
            'notes' => 'Enrollment test',
        ],
        [],
        [
            'student_license' => $file
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
        echo "Response: " . get_class($response) . " -> " . $response->getTargetUrl() . PHP_EOL;
        if (session('error')) echo "Flash error: " . session('error') . PHP_EOL;
    } catch (\Throwable $e) {
        echo "CAUGHT EXCEPTION: " . $e->getMessage() . PHP_EOL;
        echo "At: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    }
}
