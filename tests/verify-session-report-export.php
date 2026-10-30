<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
$initialReq = Illuminate\Http\Request::create('/drived-hub/admin/schedules');
$session = $app->make('session')->driver();
$initialReq->setLaravelSession($session);
$app->instance('request', $initialReq);

$admin = App\Models\Admin::where('email', 'admin1@driveedhub.test')->first();
Illuminate\Support\Facades\Auth::guard('admin')->setUser($admin);
$school = $admin->school;

$initialReq->setRouteResolver(function() use ($school, $initialReq) {
    $route = new Illuminate\Routing\Route('GET', '/{school}/admin/schedules', []);
    $route->bind($initialReq);
    $route->setParameter('school', $school);
    return $route;
});
app()->instance('current_school', $school);
view()->share('errors', new Illuminate\Support\ViewErrorBag());

$passed = 0;
$failed = 0;

function assertTest($condition, $name) {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] $name\n";
        $passed++;
    } else {
        echo " [FAIL] $name\n";
        $failed++;
    }
}

echo "--- 1. Testing Schedule Page View (Verify export form removed) ---\n";
$scheduleView = view('school.admin.schedules', [
    'school' => $school,
    'currentSchool' => $school,
    'timeSlots' => collect([]),
    'timeslots' => collect([]),
    'stats' => [],
    'courses' => collect([]),
    'instructors' => collect([]),
    'vehicles' => collect([]),
    'branches' => collect([]),
    'activeFilter' => 'all',
    'activeSort' => 'all',
    'dateFilter' => null,
    'courseFilter' => null,
    'startDate' => now()->format('Y-m-d'),
    'endDate' => now()->addMonth()->format('Y-m-d'),
    'isAjax' => false
])->render();

assertTest(strpos($scheduleView, 'Training & Session Records') === false, "Schedule page does NOT contain Training & Session Records");

echo "\n--- 2. Testing Verify Session Completion Page View (Verify export form present) ---\n";
$students = App\Models\Student::where('school_id', $school->id)->get();
$instructors = App\Models\Instructor::where('school_id', $school->id)->get();
$firstStudent = $students->first();
$firstInstructor = $instructors->first();

$verifyView = view('school.admin.verify-session-completion', [
    'school' => $school,
    'currentSchool' => $school,
    'bookings' => new Illuminate\Pagination\LengthAwarePaginator(collect([]), 0, 15),
    'stats' => ['all' => 0, 'pending' => 0, 'done' => 0, 'completed' => 0, 'flagged' => 0],
    'allSessionsCount' => 0,
    'pendingRequestsCount' => 0,
    'awaitingVerificationCount' => 0,
    'verifiedSessionsCount' => 0,
    'flaggedIssuesCount' => 0,
    'activeFilter' => 'all',
    'activeSort' => 'audit_priority',
    'activeTab' => 'verify',
    'instructors' => $instructors,
    'students' => $students,
    'branches' => collect([]),
    'isAjax' => false
])->render();

assertTest(strpos($verifyView, 'Schedule Logs') !== false, "Verify Session Completion page contains Schedule Logs section");
assertTest(strpos($verifyView, 'name="student_id"') !== false, "Export form contains student_id filter dropdown");
assertTest(strpos($verifyView, 'name="instructor_id"') !== false, "Export form contains instructor_id filter dropdown");
assertTest(strpos($verifyView, 'Export All Records') !== false, "Export menu offers all records");
assertTest(strpos($verifyView, 'Export Filtered Records') !== false, "Export menu offers filtered records");
assertTest(substr_count($verifyView, '>Export Report<') === 1, "One Export Report menu replaces duplicate export controls");
assertTest(strpos($verifyView, 'value="pdf"') !== false && strpos($verifyView, 'value="xlsx"') !== false, "Export menu retains PDF and Excel formats");

echo "\n--- 3. Testing PDF and Excel Export Functionality via SessionReportController ---\n";
$controller = app(App\Http\Controllers\SessionReportController::class);

function makeReportRequest($school, $format, array $params = []) {
    global $session;
    $queryString = http_build_query($params);
    $url = "/{$school->slug}/admin/session-report/{$format}" . ($queryString ? '?' . $queryString : '');
    $request = Illuminate\Http\Request::create($url, 'GET');
    $request->setLaravelSession($session);
    $request->setRouteResolver(function() use ($request) {
        $route = new Illuminate\Routing\Route('GET', '/{school}/admin/session-report/{format}', []);
        $route->name('schools.admin.session-report');
        $route->bind($request);
        return $route;
    });
    app()->instance('request', $request);
    return $request;
}

// A. Download PDF - No filter
try {
    $req = makeReportRequest($school, 'pdf');
    $response = $controller->download($req, $school, 'pdf');
    $content = $response->getContent();
    assertTest($response->getStatusCode() === 200, "PDF export status is 200");
    assertTest(str_starts_with($content, "%PDF-"), "PDF binary has valid %PDF header");
    assertTest(strlen($content) > 1000, "PDF file is not empty (" . strlen($content) . " bytes)");
} catch (\Throwable $e) {
    assertTest(false, "PDF export failed: " . $e->getMessage());
}

// B. Download Excel - No filter
try {
    $req = makeReportRequest($school, 'xlsx');
    $response = $controller->download($req, $school, 'xlsx');
    ob_start();
    $response->sendContent();
    $excelContent = ob_get_clean();
    assertTest($response->getStatusCode() === 200, "Excel export status is 200");
    assertTest(str_starts_with($excelContent, "PK"), "Excel output has valid ZIP/PK header");
    assertTest(strlen($excelContent) > 500, "Excel file is not empty (" . strlen($excelContent) . " bytes)");
} catch (\Throwable $e) {
    assertTest(false, "Excel export failed: " . $e->getMessage());
}

// C. Download PDF - Filtered by Student
if ($firstStudent) {
    try {
        $req = makeReportRequest($school, 'pdf', ['student_id' => $firstStudent->id]);
        $response = $controller->download($req, $school, 'pdf');
        $content = $response->getContent();
        assertTest($response->getStatusCode() === 200, "PDF export filtered by Student returns status 200");
        assertTest(str_starts_with($content, "%PDF-"), "Filtered student PDF has valid %PDF header");
    } catch (\Throwable $e) {
        assertTest(false, "PDF student filter failed: " . $e->getMessage());
    }
}

// D. Download PDF - Filtered by Instructor
if ($firstInstructor) {
    try {
        $req = makeReportRequest($school, 'pdf', ['instructor_id' => $firstInstructor->id]);
        $response = $controller->download($req, $school, 'pdf');
        $content = $response->getContent();
        assertTest($response->getStatusCode() === 200, "PDF export filtered by Instructor returns status 200");
        assertTest(str_starts_with($content, "%PDF-"), "Filtered instructor PDF has valid %PDF header");
    } catch (\Throwable $e) {
        assertTest(false, "PDF instructor filter failed: " . $e->getMessage());
    }
}

// E. Download Excel - Filtered by Student and Instructor
if ($firstStudent && $firstInstructor) {
    try {
        $req = makeReportRequest($school, 'xlsx', [
            'student_id' => $firstStudent->id,
            'instructor_id' => $firstInstructor->id,
            'start' => date('Y-01-01'),
            'end' => date('Y-12-31'),
        ]);
        $response = $controller->download($req, $school, 'xlsx');
        ob_start();
        $response->sendContent();
        $excelContent = ob_get_clean();
        assertTest($response->getStatusCode() === 200, "Excel export filtered by Student and Instructor returns 200");
        assertTest(str_starts_with($excelContent, "PK"), "Excel has valid PK header");
    } catch (\Throwable $e) {
        assertTest(false, "Excel student+instructor filter failed: " . $e->getMessage());
    }
}

// F. Empty string query params (as submitted by browser default)
try {
    $req = makeReportRequest($school, 'pdf', [
        'start' => '',
        'end' => '',
        'student_id' => '',
        'instructor_id' => '',
    ]);
    $response = $controller->download($req, $school, 'pdf');
    $content = $response->getContent();
    assertTest($response->getStatusCode() === 200, "PDF export with empty strings (browser default) returns status 200");
    assertTest(str_starts_with($content, "%PDF-"), "Empty filter PDF has valid %PDF header");
} catch (\Throwable $e) {
    assertTest(false, "PDF with empty filter inputs failed: " . $e->getMessage());
}

echo "\n=========================================\n";
echo "Total Passed: $passed, Total Failed: $failed\n";
echo "=========================================\n";
exit($failed > 0 ? 1 : 0);
