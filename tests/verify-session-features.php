<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\ClaimNoticeController;
use App\Http\Controllers\SessionReportController;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Instructor;
use App\Models\Notification;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$checks = 0;
$check = function ($condition, $message) use (&$checks) {
    if (!$condition) { throw new RuntimeException($message); }
    $checks++;
};
$school = School::where('slug', 'drived-hub')->firstOrFail();
$out = base_path('tmp/report-verification');
if (!is_dir($out)) { mkdir($out, 0775, true); }
$download = function ($role, $user, $format, $query = []) use ($school) {
    Auth::forgetGuards();
    Auth::guard($role)->setUser($user);
    $request = Request::create('/report', 'GET', $query);
    $route = (new Route('GET', '/report', fn () => null))->name('schools.'.$role.'.session-report');
    $request->setRouteResolver(fn () => $route);
    app()->instance('request', $request);
    return app(SessionReportController::class)->download($request, $school, $format);
};

DB::beginTransaction();
try {
    $student = Student::where('email', 'student6@driveedhub.test')->firstOrFail();
    $admin = Admin::where('email', 'admin1@driveedhub.test')->firstOrFail();
    $instructor = Instructor::where('school_id', $school->id)->firstOrFail();
    $booking = Booking::where('student_id', $student->id)->firstOrFail();
    $booking->update(['notes' => '=SUM(1,2) <sample> & feedback', 'session_grade' => 0]);

    foreach (['student' => $student, 'instructor' => $instructor, 'admin' => $admin] as $role => $user) {
        $response = $download($role, $user, 'xlsx');
        ob_start(); $response->sendContent(); $bytes = ob_get_clean();
        $check(str_starts_with($bytes, 'PK'), $role.' must receive a real XLSX.');
        $file = $out.'/'.$role.'-sessions.xlsx';
        file_put_contents($file, $bytes);
        $zip = new ZipArchive; $check($zip->open($file) === true, 'Workbook must open.');
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $check(simplexml_load_string($xml) !== false, 'Worksheet XML must parse.');
        $check(!str_contains($xml, '<f>'), 'User text must never execute as spreadsheet formulas.');
        $check(str_contains($xml, 'state="frozen"') && str_contains($xml, '<autoFilter'), 'Workbook must have frozen headers and filters.');
        if ($role === 'student') {
            $check(str_contains($xml, '=SUM(1,2) &lt;sample&gt; &amp; feedback'), 'Notes must be preserved and escaped.');
            $check(!str_contains($xml, 'Juan Miguel Dela Cruz'), 'Student export must exclude other students.');
        }
        $zip->close();
    }
    $pdf = $download('admin', $admin, 'pdf');
    $check(str_starts_with($pdf->getContent(), '%PDF'), 'PDF export must render.');
    file_put_contents($out.'/admin-sessions.pdf', $pdf->getContent());
    $empty = $download('student', $student, 'pdf', ['start' => '2099-01-01', 'end' => '2099-01-02']);
    $check(str_starts_with($empty->getContent(), '%PDF'), 'Empty report must still render.');
    $endOnly = $download('student', $student, 'pdf', ['end' => '2099-01-02']);
    $check($endOnly->getStatusCode() === 200, 'End-only date filter must work.');
    try {
        $outsider = clone $student; $outsider->school_id = $school->id + 999;
        $download('student', $outsider, 'xlsx');
        $check(false, 'Cross-school access must be denied.');
    } catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
        $check($e->getStatusCode() === 403, 'Cross-school request must return 403.');
    }

    // Instructor Per-Session and Whole-Session Exports
    $instSession = \App\Models\SessionCompletion::where('instructor_id', $instructor->id)->first();
    if (!$instSession) {
        $instSession = \App\Models\SessionCompletion::create([
            'school_id' => $school->id,
            'enrollment_id' => $booking->student_id,
            'instructor_id' => $instructor->id,
            'session_type' => 'practical',
            'session_date' => now()->toDateString(),
            'session_time' => '10:00:00',
            'hours_completed' => 2.0,
            'status' => 'completed',
            'notes' => 'Great parallel parking practice.',
        ]);
    }

    // 1. Instructor Whole Sessions (PDF & XLSX)
    $wholePdf = $download('instructor', $instructor, 'pdf');
    $check(str_starts_with($wholePdf->getContent(), '%PDF'), 'Instructor whole session PDF export must render.');
    $wholeXlsx = $download('instructor', $instructor, 'xlsx');
    $check($wholeXlsx->getStatusCode() === 200, 'Instructor whole session XLSX export must return 200.');

    // 2. Instructor Per-Session Export by session_id (PDF & XLSX)
    $singlePdf = $download('instructor', $instructor, 'pdf', ['session_id' => $instSession->id]);
    $check(str_starts_with($singlePdf->getContent(), '%PDF'), 'Instructor per-session PDF export must render.');
    $singleXlsx = $download('instructor', $instructor, 'xlsx', ['session_id' => $instSession->id]);
    $check($singleXlsx->getStatusCode() === 200, 'Instructor per-session XLSX export must return 200.');

    // 3. Instructor Legacy Export Routes with session_id
    Auth::forgetGuards(); Auth::guard('instructor')->setUser($instructor);
    $exportCtrl = app(\App\Http\Controllers\ExportController::class);
    $reqLegacyWhole = Request::create('/instructor/exports/sessions/pdf', 'GET');
    app()->instance('request', $reqLegacyWhole);
    $legacyWholeRes = $exportCtrl->instructorSessionsPdf($school);
    $check(str_starts_with($legacyWholeRes->getContent(), '%PDF'), 'Legacy whole session export must render.');

    $reqLegacySingle = Request::create('/instructor/exports/sessions/pdf', 'GET', ['session_id' => $instSession->id]);
    app()->instance('request', $reqLegacySingle);
    $legacySingleRes = $exportCtrl->instructorSessionsPdf($school);
    $check(str_starts_with($legacySingleRes->getContent(), '%PDF'), 'Legacy per-session export with session_id must render.');

    Auth::forgetGuards(); Auth::guard('admin')->setUser($admin);
    $noticeRequest = Request::create('/claim-notice', 'POST', ['kind' => 'claim_stub', 'message' => 'Please claim your stub at the school office.']);
    app()->instance('request', $noticeRequest);
    app(ClaimNoticeController::class)->store($noticeRequest, $school, $student);
    $notice = Notification::forNotifiable($student)->where('type', 'certificate_claim_ready')->latest('id')->first();
    $check($notice && $notice->message === 'Please claim your stub at the school office.', 'Custom claim message must reach the intended student.');
    $check($notice->read_at === null, 'Claim notification must be unread.');
    $check(str_contains($notice->action_url, '/student'), 'Notification must link to the student dashboard.');
    echo "PASS: {$checks} session export and claim notification checks.\n";
} finally {
    DB::rollBack();
}
