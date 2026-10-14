<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$log = App\Models\SystemLog::orderBy('id', 'desc')->first();
echo $log->message . " | " . json_encode($log->context) . "\n";
