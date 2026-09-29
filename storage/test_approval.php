<?php
$school = App\Models\School::first();
$admin = App\Models\Admin::first();

// Create a guest
$learner = App\Models\Student::factory()->create([
    'school_id' => $school->id,
    'role' => 'guest'
]);

// Create course
$course = App\Models\Course::first();

// Create enrollment request
$req = App\Models\EnrollmentRequest::create([
    'school_id' => $school->id,
    'learner_id' => $learner->id,
    'course_id' => $course->id,
    'status' => 'pending',
    'payment_status' => 'pending',
]);

echo "Created request #{$req->id}\n";

// Try to approve it like the controller
DB::beginTransaction();
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
        Mail::to($req->learner->email)
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

    DB::commit();
    echo "Approval Success!\n";
}
catch (\Exception $e) {
    DB::rollBack();
    echo "FAILED: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
