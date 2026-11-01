<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$snapshotPath = __DIR__.'/../tmp/nov1-findings/package-snapshot.json';
if (($argv[1] ?? '') === 'save') {
    $package = DB::table('course_packages')->where('course_id', 1)->firstOrFail();
    file_put_contents($snapshotPath, json_encode($package, JSON_THROW_ON_ERROR));
} elseif (($argv[1] ?? '') === 'restore') {
    $package = json_decode(file_get_contents($snapshotPath), true, flags: JSON_THROW_ON_ERROR);
    DB::table('course_packages')->where('id', $package['id'])->where('course_id', 1)->update($package);
    unlink($snapshotPath);
} else {
    throw new InvalidArgumentException('Expected save or restore');
}
