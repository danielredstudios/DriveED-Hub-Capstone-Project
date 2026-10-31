<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\{EnrollmentRequestController, InstructorTimeSlotController, TheoreticalCompletionController};
use App\Models\{Admin, Booking, Course, EnrollmentRequest, Instructor, School, SessionCompletion};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Symfony\Component\HttpKernel\Exception\HttpException;

$checks = 0;
$check = function (bool $condition, string $message) use (&$checks): void {
    if (!$condition) throw new RuntimeException($message);
    $checks++;
    echo "PASS: {$message}\n";
};
DB::beginTransaction();
$originalRequest = app('request');
try {
    $school = School::where('slug', 'drived-hub')->firstOrFail();
    $admin = Admin::where('school_id', $school->id)->where('role', 'school_admin')->firstOrFail();
    $instructor = Instructor::where('school_id', $school->id)->firstOrFail();
    $tdc = EnrollmentRequest::where('school_id', $school->id)->where('status', 'approved')
        ->whereHas('course', fn ($query) => $query->where('course_type', 'theoretical'))->firstOrFail();
    $comboCourse = Course::where('school_id', $school->id)->where('course_type', 'combo')->firstOrFail();
    $combo = EnrollmentRequest::create([
        'school_id' => $school->id, 'learner_id' => $tdc->learner_id,
        'branch_id' => $tdc->branch_id, 'course_id' => $comboCourse->id,
        'status' => 'approved', 'payment_status' => 'paid',
    ]);
    $tdc->learner->lockToCourse($combo);
    $trainingBefore = $tdc->sessionCompletions()->orderBy('id')->get()->toJson();
    $tdc->markTheoreticalPassed($admin->id, 'Regression check: preserve the newer course lock.');
    $student = $tdc->learner->fresh();
    $check($tdc->fresh()->status === 'completed', 'Old standalone TDC completes even when a newer Combo exists');
    $check($student->is_course_locked && $student->active_enrollment_id === $combo->id, 'Completing old TDC preserves the newer Combo lock');
    $check($tdc->fresh()->theoretical_pass_notes === 'Regression check: preserve the newer course lock.', 'TDC passing notes are persisted');
    $tdc->markTheoreticalPassed($admin->id);
    $check($tdc->fresh()->theoretical_pass_notes === 'Regression check: preserve the newer course lock.', 'Repeating a pass without notes preserves the original passing notes');
    $check($tdc->sessionCompletions()->orderBy('id')->get()->toJson() === $trainingBefore, 'TDC training records are untouched by completion');

    Auth::forgetGuards();
    Auth::guard('admin')->setUser($admin);
    $rejected = EnrollmentRequest::where('school_id', $school->id)->where('status', 'approved')
        ->whereHas('course', fn ($query) => $query->where('course_type', 'theoretical'))->get()
        ->first(fn ($enrollment) => !App\Support\EnrollmentValidator::canMarkTheoreticalPassed($enrollment)['allowed']);
    if (!$rejected) throw new RuntimeException('No incomplete TDC enrollment available for rejection test.');
    $request = Request::create('/drived-hub/admin/enrollments/'.$rejected->id.'/theoretical-passed', 'POST');
    $request->setLaravelSession(app('session')->driver());
    $level = DB::transactionLevel();
    $response = app(EnrollmentRequestController::class)->markTheoreticalPassed($request, $school, $rejected);
    $check($response->getStatusCode() === 302 && DB::transactionLevel() === $level, 'Rejected TDC pass returns without leaking a database transaction');
    $check($rejected->fresh()->status === 'approved', 'Rejected TDC pass leaves the enrollment active');

    foreach ([$student->email, $tdc->uid] as $search) {
        $view = app(EnrollmentRequestController::class)->index(Request::create('/enrollments', 'GET', ['search' => $search]), $school);
        $rows = $view->getData()['allRequests'];
        $check($rows->total() > 0 && $rows->every(fn ($row) => $search === $student->email ? $row->learner_id === $student->id : $row->id === $tdc->id), 'Enrollment search scopes matches correctly for '.$search);
    }

    Auth::forgetGuards();
    Auth::guard('instructor')->setUser($instructor);
    $limit = $combo->tdc_hours_limit;
    if ($limit <= 0) throw new RuntimeException('Combo TDC limit is missing.');
    $filler = SessionCompletion::create([
        'school_id' => $school->id, 'enrollment_id' => $combo->id,
        'instructor_id' => $instructor->id, 'session_type' => 'theoretical',
        'hours_completed' => $limit, 'session_date' => now()->toDateString(),
        'session_time' => '01:00', 'status' => 'completed',
    ]);
    $booking = Booking::create([
        'school_id' => $school->id, 'student_id' => $student->id,
        'course_id' => $combo->course_id, 'enrollment_request_id' => $combo->id,
        'instructor_id' => $instructor->id, 'scheduled_at' => now()->startOfDay()->addHours(8),
        'booking_date' => now()->toDateString(), 'status' => 'scheduled', 'session_status' => 'scheduled',
    ]);
    $logRequest = Request::create('/lesson/update', 'POST', ['session_status' => 'done', 'attendance_status' => 'attended']);
    $logRequest->setLaravelSession(app('session')->driver());
    $response = app(InstructorTimeSlotController::class)->updateLessonDetails($school, $booking, $logRequest);
    $check($response->getStatusCode() === 422 && isset($response->getData(true)['errors']['hours_completed']), 'Schedule logging returns a clear validation error at the hour limit');
    $check($booking->fresh()->status === 'scheduled' && !SessionCompletion::where('booking_id', $booking->id)->exists(), 'Rejected schedule logging rolls back the booking and creates no session');
    $filler->update(['status' => 'cancelled']);
    $response = app(InstructorTimeSlotController::class)->updateLessonDetails($school, $booking->fresh(), $logRequest);
    $check($response->getStatusCode() === 200 && SessionCompletion::where('booking_id', $booking->id)->count() === 1, 'Schedule logging succeeds when training hours remain');
    $filler->update(['status' => 'completed']);
    $feedback = Request::create('/lesson/update', 'POST', ['instructor_feedback' => 'Corrected feedback on an existing log.', 'session_grade' => 88]);
    $feedback->setLaravelSession(app('session')->driver());
    $response = app(InstructorTimeSlotController::class)->updateLessonDetails($school, $booking->fresh(), $feedback);
    $saved = SessionCompletion::where('booking_id', $booking->id)->firstOrFail();
    $check($response->getStatusCode() === 200 && $saved->session_grade == 88 && $saved->notes === 'Corrected feedback on an existing log.', 'Existing session grades and feedback can be corrected even with legacy excess hours');
    $check(SessionCompletion::where('booking_id', $booking->id)->count() === 1, 'Feedback correction does not duplicate the training log');
    $pdf = app(TheoreticalCompletionController::class)->export($school, $tdc->id);
    $check(str_starts_with($pdf->getContent(), '%PDF-'), 'Assigned instructor can export a valid individual training PDF');
    $pdf = app(TheoreticalCompletionController::class)->exportList($school);
    $check(str_starts_with($pdf->getContent(), '%PDF-'), 'Instructor training-list PDF renders successfully');

    Auth::forgetGuards();
    Auth::guard('admin')->setUser($admin);
    $otherSchool = new School;
    $verify = Request::create('/booking/status', 'POST', ['status' => 'completed']);
    $verify->headers->set('Accept', 'application/json');
    $verify->setLaravelSession(app('session')->driver());
    $response = app(App\Http\Controllers\BookingController::class)->updateStatus($verify, $school, $booking->fresh());
    $check($response->getStatusCode() === 200 && $booking->fresh()->status === 'completed', 'Admin can verify an existing session even at the training-hour limit');
    $check(SessionCompletion::where('booking_id', $booking->id)->count() === 1, 'Admin verification does not duplicate the existing session');
    $legacy = $booking->replicate();
    $legacy->forceFill(['status' => 'done', 'session_status' => 'done', 'scheduled_at' => now()->startOfDay()->addHours(12)])->save();
    $response = app(App\Http\Controllers\BookingController::class)->updateStatus($verify, $school, $legacy);
    $check($response->getStatusCode() === 422 && $legacy->fresh()->status === 'done', 'Admin verification rejects extra legacy hours with validation feedback and rolls back');
    $otherSchool->id = $school->id + 999;
    try {
        app(TheoreticalCompletionController::class)->export($otherSchool, $tdc->id);
        throw new RuntimeException('Cross-school export was allowed.');
    } catch (HttpException $error) {
        $check($error->getStatusCode() === 403, 'Training PDF export rejects cross-school access');
    }
    $secretary = clone $admin;
    $secretary->role = 'branch_secretary';
    $secretary->branch_id = ($tdc->branch_id ?? 0) + 999;
    Auth::guard('admin')->setUser($secretary);
    try {
        app(TheoreticalCompletionController::class)->export($school, $tdc->id);
        throw new RuntimeException('Cross-branch export was allowed.');
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $error) {
        $check(true, 'Training PDF export respects branch-secretary restrictions');
    }

    Auth::forgetGuards();
    Auth::guard('admin')->setUser($admin);
    $settings = $school->schoolSetting;
    $settings->update(['admin_header_bg' => null, 'instructor_header_bg' => null, 'student_header_bg' => null,
        'login_header_bg_type' => 'image', 'login_header_bg_image' => 'headers/recheck-only.png', 'login_header_bg_color' => '#174b79']);
    $request = Request::create('/drived-hub/admin/settings', 'GET');
    $route = (new \Illuminate\Routing\Route('GET', '/{school}/admin/settings', fn () => null))->name('schools.admin.settings');
    $route->bind($request);
    $route->setParameter('school', $school);
    $request->setRouteResolver(fn () => $route);
    app()->instance('request', $request);
    $html = app(App\Http\Controllers\AdminController::class)->settings($request, $school)
        ->with('errors', new \Illuminate\Support\ViewErrorBag)->render();
    foreach (['admin', 'instructor', 'student'] as $role) {
        preg_match('/id="'.$role.'_header_bg"[^>]*value="([^"]+)"/', $html, $match);
        $check(($match[1] ?? null) === '#174b79', $role.' header color input retains a valid hex value when login uses an image');
    }
    $settings->update(['admin_header_bg' => '#174b79', 'instructor_header_bg' => '#065f46', 'student_header_bg' => '#7c2d12',
        'admin_header_text' => '#ffffff', 'instructor_header_text' => '#ffffff', 'student_header_text' => '#ffffff']);
    foreach (['admin' => [$admin, '#174b79'], 'instructor' => [$instructor, '#065f46'], 'student' => [$student, '#7c2d12']] as $role => [$actor, $background]) {
        Auth::forgetGuards();
        Auth::guard($role)->setUser($actor);
        $request = Request::create('/drived-hub/'.$role, 'GET');
        $route = (new \Illuminate\Routing\Route('GET', '/{school}/'.$role, fn () => null))->name('schools.'.$role.'.dashboard');
        $route->bind($request);
        $route->setParameter('school', $school);
        $request->setRouteResolver(fn () => $route);
        app()->instance('request', $request);
        $html = view('layouts.app', ['school' => $school, 'errors' => new \Illuminate\Support\ViewErrorBag])->render();
        preg_match('/\.topbar\s*\{([^}]+)\}/s', $html, $match);
        $check(str_contains($match[1] ?? '', $background), $role.' layout renders its independently configured header background');
    }
} finally {
    app()->instance('request', $originalRequest);
    while (DB::transactionLevel() > 0) DB::rollBack();
}
echo "PASS: {$checks} recheck edge-case assertions; database changes rolled back.\n";
