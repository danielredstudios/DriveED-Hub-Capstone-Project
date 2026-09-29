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

$er = EnrollmentRequest::where('school_id', $school->id)
    ->where('status', 'pending')
    ->whereHas('learner', function($q) { $q->where('role', 'guest'); })
    ->first();

if (!$er) {
    echo "No pending guest enrollment found!" . PHP_EOL;
    exit;
}

echo "Approving EnrollmentRequest ID: {$er->id}, Learner: {$er->learner->name} (Role: {$er->learner->role})" . PHP_EOL;
$er->update(['payment_status' => 'paid']);

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
