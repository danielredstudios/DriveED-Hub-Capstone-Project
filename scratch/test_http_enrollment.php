<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use App\Models\Student;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\Admin;
use App\Models\EnrollmentRequest;

$school = School::where('slug', 'drived-hub')->first();
$courses = Course::where('school_id', $school->id)->get();
$student = Student::where('role', 'student')->where('school_id', $school->id)->first();
$guest = Student::where('role', 'guest')->where('school_id', $school->id)->first();
$admin = Admin::where('school_id', $school->id)->first();

echo "--- 1. Testing Student Enroll Route via HTTP Kernel ---" . PHP_EOL;
foreach ($courses as $course) {
    $package = $course->packages()->first();
    echo "Testing Course: {$course->title} (ID: {$course->id}, Type: {$course->course_type})" . PHP_EOL;

    // Login student
    auth()->guard('student')->login($student);

    $uri = "/{$school->slug}/student/enroll/{$course->id}";
    $postData = [
        'experience_level' => 'new_driver',
        'package_id' => $package ? $package->id : null,
        'branch_id' => $student->branch_id,
        'notes' => 'Test notes',
        'requested_dl_code' => 'A'
    ];
    $server = [
        'HTTP_HOST' => 'localhost:8004',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
    ];
    $req = Illuminate\Http\Request::create($uri, 'POST', $postData, [], [], $server);
    // Add session
    $session = $app->make('session')->driver();
    $session->start();
    $req->setLaravelSession($session);

    try {
        $res = $kernel->handle($req);
        echo "Status: " . $res->getStatusCode() . PHP_EOL;
        if ($res->getStatusCode() == 500) {
            echo "500 ERROR CONTENT: " . substr($res->getContent(), 0, 500) . PHP_EOL;
        }
    } catch (\Throwable $e) {
        echo "EXCEPTION: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
        echo $e->getTraceAsString() . PHP_EOL;
    }
}

echo PHP_EOL . "--- 2. Testing Guest Enroll Route via HTTP Kernel ---" . PHP_EOL;
foreach ($courses as $course) {
    $package = $course->packages()->first();
    echo "Testing Guest Course: {$course->title} (ID: {$course->id})" . PHP_EOL;

    auth()->guard('student')->login($guest);

    $uri = "/{$school->slug}/guest/enroll/{$course->id}";
    $postData = [
        'experience_level' => 'new_driver',
        'package_id' => $package ? $package->id : null,
        'branch_id' => $guest->branch_id,
        'notes' => 'Test guest notes',
        'requested_dl_code' => 'A'
    ];
    $req = Illuminate\Http\Request::create($uri, 'POST', $postData, [], [], [
        'HTTP_HOST' => 'localhost:8004',
        'HTTP_ACCEPT' => 'text/html',
    ]);
    $session = $app->make('session')->driver();
    $session->start();
    $req->setLaravelSession($session);

    try {
        $res = $kernel->handle($req);
        echo "Status: " . $res->getStatusCode() . PHP_EOL;
        if ($res->getStatusCode() == 500) {
            echo "500 ERROR CONTENT: " . substr($res->getContent(), 0, 500) . PHP_EOL;
        }
    } catch (\Throwable $e) {
        echo "EXCEPTION: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    }
}
