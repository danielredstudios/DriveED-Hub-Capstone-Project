<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Requests\StoreEnrollmentRequestRequest;
use Illuminate\Support\Facades\Validator;

$prepare = new ReflectionMethod(StoreEnrollmentRequestRequest::class, 'prepareForValidation');
foreach (['new_driver_with_license' => 'new_driver', 'new_driver' => 'new_driver', 'experienced' => 'experienced'] as $submitted => $stored) {
    $request = StoreEnrollmentRequestRequest::create('/', 'POST', ['experience_level' => $submitted]);
    $prepare->invoke($request);
    if ($request->input('experience_level') !== $stored) {
        throw new RuntimeException("Incorrect stored experience for {$submitted}");
    }
    $validator = Validator::make($request->all(), ['experience_level' => $request->rules()['experience_level']]);
    if ($validator->fails()) {
        throw new RuntimeException("Experience rejected: {$submitted}");
    }
    echo "PASS: {$submitted} validates and persists as {$stored}\n";
}

$request = StoreEnrollmentRequestRequest::create('/', 'POST', ['experience_level' => 'invalid']);
$prepare->invoke($request);
if (!Validator::make($request->all(), ['experience_level' => $request->rules()['experience_level']])->fails()) {
    throw new RuntimeException('Unknown experience must be rejected');
}
echo "PASS: Unknown experience is rejected\n";
