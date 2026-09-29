<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$school = App\Models\School::first();
$admin = App\Models\Admin::first();

$req = App\Models\EnrollmentRequest::where('status', 'pending')->first();
if (!$req) {
    echo "No pending requests found.\n";
    exit;
}

echo "Found request #{$req->id}\n";

Illuminate\Support\Facades\DB::beginTransaction();
try {
    $req->update([
        'status' => 'approved',
        'approved_by' => $admin->id,
        'approved_at' => now(),
        'enrolled_at' => now(),
    ]);

    $req->student->update(['role' => 'student']);
    $req->student->update(['is_course_locked' => true]);

    try {
        Illuminate\Support\Facades\Mail::to($req->learner->email)
            ->send(new App\Mail\EnrollmentApproved($req, $school));
    }
    catch (\Exception $e) {
        echo "Mail failed: " . $e->getMessage() . "\n";
    }

    App\Models\Notification::send(
        $req->student,
        'enrollment_approved',
        'Enrollment Approved!',
        "Your enrollment for {$req->course->title} has been approved. Welcome aboard!",
        'success',
        "/{$school->slug}/student"
    );

    Illuminate\Support\Facades\DB::commit();
    echo "Approval Success!\n";
}
catch (\Exception $e) {
    Illuminate\Support\Facades\DB::rollBack();
    echo "FAILED: \n";
    echo $e->getMessage() . "\n";
}
