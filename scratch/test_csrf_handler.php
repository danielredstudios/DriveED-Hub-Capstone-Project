<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$handler = $app->make(Illuminate\Contracts\Debug\ExceptionHandler::class);

$req = Illuminate\Http\Request::create('/drived-hub/register', 'POST');
$session = $app->make('session')->driver();
$session->start();
$req->setLaravelSession($session);

$resp = $handler->render($req, new Illuminate\Session\TokenMismatchException('CSRF mismatch'));
echo "Status: " . $resp->getStatusCode() . PHP_EOL;
echo "Class: " . get_class($resp) . PHP_EOL;
if ($resp instanceof Illuminate\Http\RedirectResponse) {
    echo "Target URL: " . $resp->getTargetUrl() . PHP_EOL;
    echo "Session Error: " . session('error') . PHP_EOL;
}
