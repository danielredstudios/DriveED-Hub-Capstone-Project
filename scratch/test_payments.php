<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$school = App\Models\School::where('slug', 'drived-hub')->first();
$student = App\Models\Student::where('role', 'student')->where('school_id', $school->id)->first();
Illuminate\Support\Facades\Auth::guard('student')->setUser($student);

$controller = app()->make(App\Http\Controllers\PaymentController::class);
$req = Illuminate\Http\Request::create('/' . $school->slug . '/student/payments?enrollment_id=1', 'GET');
$req->setLaravelSession($app->make('session')->driver());

try {
    $res = app()->call([$controller, 'index'], ['request' => $req, 'school' => $school]);
    echo "OK: " . get_class($res) . PHP_EOL;
    echo "Length: " . strlen($res->render()) . PHP_EOL;
} catch (\Throwable $e) {
    echo "ERR: " . $e->getMessage() . PHP_EOL;
    echo $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
