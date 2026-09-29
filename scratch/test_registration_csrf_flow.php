<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use Illuminate\Http\Request;

$school = School::where('slug', 'drived-hub')->first();

echo "--- 1. Testing Registration Submission with Invalid/Expired CSRF Token ---" . PHP_EOL;

$req = Request::create("/{$school->slug}/register", 'POST', [
    '_token' => 'invalid_or_expired_token_12345',
    'name' => 'Juan Test Santos',
    'email' => 'juantest@example.com',
    'contact' => '9123456789',
    'password' => 'Pass1234!',
    'password_confirmation' => 'Pass1234!',
    'accept_privacy' => '1',
    'accept_terms' => '1',
], [], [], [
    'HTTP_HOST' => 'localhost:8004',
    'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
]);

$session = $app->make('session')->driver();
$session->start();
$req->setLaravelSession($session);

$response = $kernel->handle($req);

echo "HTTP Status: " . $response->getStatusCode() . PHP_EOL;
echo "Response Class: " . get_class($response) . PHP_EOL;
if ($response instanceof \Illuminate\Http\RedirectResponse) {
    echo "Redirect Target: " . $response->getTargetUrl() . PHP_EOL;
    echo "Session Error Flash: " . session('error') . PHP_EOL;
    echo "Session Old Name: " . $session->getOldInput('name') . PHP_EOL;
    echo "Session Old Email: " . $session->getOldInput('email') . PHP_EOL;
    echo "Session Old Contact: " . $session->getOldInput('contact') . PHP_EOL;
} else {
    echo "Content snippet: " . substr($response->getContent(), 0, 200) . PHP_EOL;
}
