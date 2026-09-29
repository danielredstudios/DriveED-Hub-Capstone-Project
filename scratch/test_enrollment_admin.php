<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$school = App\Models\School::first();
$admin = App\Models\Admin::where('school_id', $school->id)->first();
Auth::guard('admin')->setUser($admin);

$controller = new App\Http\Controllers\EnrollmentRequestController();

// 1. Test index
try {
    $request = Illuminate\Http\Request::create('/' . $school->slug . '/admin/enrollments', 'GET');
    $res = $controller->index($request, $school);
    echo "EnrollmentRequestController::index OK, class: " . get_class($res) . "\n";
    if ($res instanceof Illuminate\View\View) {
        $res->render();
        echo "Enrollment index rendered OK!\n";
    }
} catch (\Throwable $e) {
    echo "Enrollment index ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// 2. Check pending enrollment requests
$pending = App\Models\EnrollmentRequest::where('school_id', $school->id)->where('status', 'pending')->first();
if ($pending) {
    echo "Found pending enrollment request ID: {$pending->id}\n";
    try {
        $req = Illuminate\Http\Request::create('/' . $school->slug . '/admin/enrollments/' . $pending->id, 'GET');
        $res = $controller->show($school, $pending);
        echo "show OK!\n";
    } catch (\Throwable $e) {
        echo "show ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
} else {
    echo "No pending enrollment request found.\n";
}
