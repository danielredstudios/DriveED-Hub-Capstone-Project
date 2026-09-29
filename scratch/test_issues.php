<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$students = App\Models\Student::select('id', 'name', 'email', 'role')->get();
foreach ($students as $s) {
    echo "ID: {$s->id} | Name: {$s->name} | Email: {$s->email} | Role: {$s->role}\n";
}

$school = App\Models\School::first();
echo "\n--- Testing Student Enrollment Form Submission ---\n";
// Let's find a student with role 'student'
$student = App\Models\Student::where('role', 'student')->first();
$course = App\Models\Course::where('school_id', $school->id)->where('course_type', 'practical')->first();
echo "Student: {$student->name} ({$student->id}) enrolling in {$course->title} ({$course->id})\n";

// Let's invoke the HTTP request through Laravel's kernel
$request = Illuminate\Http\Request::create(
    "/{$school->slug}/student/enroll/{$course->id}",
    'POST',
    [
        'experience_level' => 'new_driver',
        'requested_dl_code' => 'B',
        'notes' => 'Test enrollment via HTTP kernel',
    ]
);

// Authenticate student
Auth::guard('student')->setUser($student);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);
echo "HTTP Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() === 500) {
    echo "500 ERROR CONTENT: " . substr($response->getContent(), 0, 500) . "\n";
} elseif ($response->isRedirection()) {
    echo "Redirected to: " . $response->getTargetUrl() . "\n";
    $session = $request->session();
    if ($session) {
        echo "Session errors: " . json_encode($session->get('errors')?->all()) . "\n";
        echo "Session flash error: " . $session->get('error') . "\n";
        echo "Session flash success: " . $session->get('success') . "\n";
    }
}
