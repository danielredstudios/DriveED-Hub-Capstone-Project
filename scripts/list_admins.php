<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;

$admins = Admin::orderBy('created_at', 'desc')->take(10)->get([
    'id','school_id','email','role','is_active','failed_login_attempts','locked_until','created_at'
]);

echo json_encode($admins->toArray(), JSON_PRETTY_PRINT) . PHP_EOL;
