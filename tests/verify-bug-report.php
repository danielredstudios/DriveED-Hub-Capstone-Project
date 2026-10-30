<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{Admin, Booking, EnrollmentRequest, Instructor, School, SessionCompletion};
use App\Http\Controllers\{InstructorTimeSlotController, SessionCompletionController};
use App\Services\SessionLoggingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

$checks = 0;
$check = function ($condition, $message) use (&$checks) {
    if (!$condition) throw new RuntimeException($message);
    echo 'PASS: '.$message.PHP_EOL;
    $checks++;
};
$school = School::where('slug', 'drived-hub')->firstOrFail();
$source = Booking::where('school_id', $school->id)->whereNotNull('instructor_id')->whereNotNull('enrollment_request_id')->firstOrFail();
$instructor = Instructor::findOrFail($source->instructor_id);
$admin = Admin::where('school_id', $school->id)->where('role', 'school_admin')->firstOrFail();
DB::beginTransaction();
try {
    $booking = $source->replicate();
    $booking->fill(['time_slot_id' => null, 'scheduled_at' => '2001-01-03 05:00:00', 'booking_date' => '2001-01-03', 'status' => 'scheduled', 'session_status' => null, 'session_grade' => null])->save();
    Auth::forgetGuards();
    Auth::guard('instructor')->setUser($instructor);
    $request = Request::create('/lesson', 'POST', ['session_status' => 'done', 'attendance_status' => 'late', 'session_grade' => 0, 'instructor_feedback' => 'Regression test notes']);
    $response = app(InstructorTimeSlotController::class)->updateLessonDetails($school, $booking, $request);
    $check($response->getStatusCode() === 200, 'Schedule session log saves attendance, zero grade, and notes.');
    $session = SessionCompletion::where('booking_id', $booking->id)->firstOrFail();
    $check((float) $session->session_grade === 0.0 && $session->attendance_status === 'late' && $session->notes === 'Regression test notes', 'Shared session record preserves all fields.');
    app(SessionLoggingService::class)->syncBooking($booking->fresh());
    $check(SessionCompletion::where('booking_id', $booking->id)->count() === 1, 'Repeated schedule saves do not duplicate hours.');
    $booking->update(['status' => 'completed']);
    app(SessionLoggingService::class)->syncBooking($booking->fresh());
    $check(SessionCompletion::where('booking_id', $booking->id)->count() === 1, 'Admin verification reuses the same session record.');
    $check($session->fresh()->logged_by === $instructor->id, 'Logged-by key remains an instructor key.');
    $booking->update(['status' => 'cancelled']);
    app(SessionLoggingService::class)->syncBooking($booking);
    $check($session->fresh()->status === 'cancelled', 'Admin cancellation also cancels the linked training log.');

    $other = Instructor::where('school_id', $school->id)->where('id', '!=', $instructor->id)->firstOrFail();
    Auth::guard('instructor')->setUser($other);
    try {
        app(InstructorTimeSlotController::class)->updateLessonDetails($school, $booking, $request);
        $check(false, 'Another instructor must be denied.');
    } catch (Symfony\Component\HttpKernel\Exception\HttpException $error) {
        $check($error->getStatusCode() === 403, 'Another instructor cannot log this booking.');
    }
    Auth::guard('instructor')->setUser($instructor);
    $eligible = app(SessionCompletionController::class)->create($school, Request::create('/sessions/create'))->getData()['enrollments'];
    $check($eligible->every(fn ($enrollment) => $enrollment->learner->bookings()->where('instructor_id', $instructor->id)->exists() || $enrollment->learner->enrollmentRequests()->whereHas('sessionCompletions', fn ($session) => $session->where('instructor_id', $instructor->id))->exists()), 'Manual enrollment list contains only connected students.');

    $enrollment = EnrollmentRequest::findOrFail($source->enrollment_request_id);
    $enrollment->update(['status' => 'approved']);
    $manual = App\Http\Requests\StoreSessionCompletionRequest::create('/sessions', 'POST', [
        'enrollment_id' => $enrollment->id, 'session_type' => $enrollment->course->course_type === 'practical' ? 'practical' : 'theoretical',
        'hours_completed' => 1.5, 'session_date' => '2001-01-04', 'session_time' => '05:00', 'notes' => 'Manual regression session', 'session_grade' => 82, 'attendance_status' => 'attended',
    ]);
    $manual->setContainer($app)->setRedirector(app('redirect'));
    $manual->setLaravelSession(app('session')->driver());
    $manual->validateResolved();
    app(SessionCompletionController::class)->store($school, $manual);
    $manualLog = SessionCompletion::where('notes', 'Manual regression session')->firstOrFail();
    $manualBooking = Booking::findOrFail($manualLog->booking_id);
    $check($manualBooking->status === 'done' && $manualBooking->enrollment_request_id === $enrollment->id, 'Manual log creates an admin-visible booking with the same enrollment key.');
    $check((float) $manualBooking->session_grade === 82.0, 'Manual log grade is available to admin verification.');
    app(SessionLoggingService::class)->syncBooking($manualBooking);
    $check(SessionCompletion::where('booking_id', $manualBooking->id)->count() === 1 && (float) $manualLog->fresh()->hours_completed === 1.5, 'Manual and schedule logging share one record and preserve manual hours.');
    $manual->setMethod('PATCH');
    $manual->merge(['notes' => 'Edited manual feedback', 'session_grade' => 88]);
    app(SessionCompletionController::class)->update($school, $manual, $manualLog->id);
    $check($manualBooking->fresh()->instructor_feedback === 'Edited manual feedback' && (float) $manualBooking->fresh()->session_grade === 88.0, 'Manual edits synchronize notes and grade back to the same booking.');
    $check(SessionCompletion::where('booking_id', $manualBooking->id)->count() === 1, 'Manual edits preserve the unique session-to-booking link.');
    $manualLog->refresh()->update(['booking_id' => null, 'session_grade' => null, 'notes' => 'Older manual note']);
    $manualBooking->update(['session_grade' => 91, 'instructor_feedback' => 'Existing booking feedback']);
    app(SessionLoggingService::class)->linkManual($manualLog->fresh(), preserveBookingFeedback: true);
    $check((float) $manualLog->fresh()->session_grade === 91.0 && $manualLog->fresh()->notes === 'Existing booking feedback' && (float) $manualBooking->fresh()->session_grade === 91.0, 'Legacy linking preserves previously recorded booking grades and feedback.');

    $settings = $school->schoolSetting;
    $settings->update(['login_show_school_name' => false, 'login_logo_image' => null, 'login_school_name_text' => 'DriveED Hub QA', 'login_header_bg_color' => '#123456']);
    $school->unsetRelation('schoolSetting');
    view()->share('errors', new Illuminate\Support\ViewErrorBag);
    $html = view('layouts.app', ['school' => $school])->render();
    $header = explode('<div class="topbar-right">', explode('<nav class="topbar"', $html)[1])[0];
    $check(!str_contains($header, '<img') && !str_contains($header, 'DriveED Hub QA'), 'Hidden school name and removed logo disappear from the authenticated header.');
    $check(str_contains($html, '#123456'), 'Configured header color reaches the authenticated layout.');
    $authHeader = view('partials.school-auth-header', ['school' => $school])->render();
    $check(!str_contains($authHeader, '<img') && !str_contains($authHeader, 'header-school-name">'), 'Login header honors hidden name and removed logo.');
    $settings->update(['login_header_layout' => 'logo-only']);
    $school->unsetRelation('schoolSetting');
    $authHeader = view('partials.school-auth-header', ['school' => $school])->render();
    $check(!str_contains($authHeader, '<img') && !str_contains($authHeader, 'header-school-name">'), 'Logo-only layout does not resurrect a hidden school name.');
    Illuminate\Support\Facades\Mail::fake();
    Auth::forgetGuards();
    Auth::guard('admin')->setUser($admin);
    $invitation = App\Models\Invitation::create(['school_id' => $school->id, 'email' => 'regression@example.invalid', 'role' => 'instructor', 'token' => Illuminate\Support\Str::random(64), 'payload' => ['name' => 'Regression fixture'], 'expires_at' => now()->subDay()]);
    app(App\Http\Controllers\AdminController::class)->resendInvitation($school, $invitation);
    Illuminate\Support\Facades\Mail::assertSent(App\Mail\SystemInvitationMail::class, fn ($mail) => $mail->hasTo('regression@example.invalid'));
    $check($invitation->fresh()->expires_at->isFuture(), 'Resend renews the pending email and uses a fake mail transport.');
    app(App\Http\Controllers\AdminController::class)->cancelInvitation($school, $invitation);
    $check(!$invitation->fresh(), 'Remove deletes the pending setup without creating a user.');
    $settings->update(['login_show_school_name' => true]);
    $school->unsetRelation('schoolSetting');
    $html = view('layouts.app', ['school' => $school])->render();
    $header = explode('</nav>', explode('<nav class="topbar"', $html)[1])[0];
    $check(substr_count($header, 'DriveED Hub QA') === 1 && !str_contains($header, 'SMART Driving School'), 'Configured school name appears exactly once.');
    echo "PASS: {$checks} bug report regression checks. All test data rolled back.\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: '.$error->getMessage().PHP_EOL);
    $exitCode = 1;
} finally {
    DB::rollBack();
}
exit($exitCode ?? 0);
