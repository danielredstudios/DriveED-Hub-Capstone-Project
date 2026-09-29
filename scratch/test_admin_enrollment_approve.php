<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use App\Models\Admin;
use App\Models\EnrollmentRequest;
use App\Http\Controllers\EnrollmentRequestController;

$school = School::where('slug', 'drived-hub')->first();
$admin = Admin::where('school_id', $school->id)->where('role', 'school_admin')->first();
auth()->guard('admin')->setUser($admin);

$controller = app()->make(EnrollmentRequestController::class);

// Find a pending enrollment request
$er = EnrollmentRequest::where('school_id', $school->id)->where('status', 'pending')->first();
if (!$er) {
    echo "No pending enrollment request found!" . PHP_EOL;
    exit;
}

echo "Testing approve on EnrollmentRequest ID: {$er->id}, Learner ID: {$er->learner_id}" . PHP_EOL;

try {
    $res = $controller->approve($school, $er);
    echo "Approve result: " . get_class($res) . PHP_EOL;
    if ($res instanceof \Illuminate\Http\RedirectResponse) {
        echo "Target: " . $res->getTargetUrl() . PHP_EOL;
        echo "Success: " . session('success') . PHP_EOL;
        echo "Error: " . session('error') . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo "CAUGHT EXCEPTION in approve: " . $e->getMessage() . PHP_EOL;
    echo "At: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
