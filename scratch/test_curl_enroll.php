<?php
$ch = curl_init();
$cookieFile = __DIR__ . '/cookies.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

// 1. GET login page
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:80/drived-hub/login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_FOLLOWLOCATION => true,
]);
$html = curl_exec($ch);

// Extract CSRF token
preg_match('/name="_token" value="([^"]+)"/', $html, $matches);
$token = $matches[1] ?? null;

// 2. POST login as student4 (who has 0% progress and unverified/no license, or guest)
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:80/drived-hub/login',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        '_token' => $token,
        'email' => 'student4@driveedhub.test',
        'password' => 'DriveDemo123',
    ]),
    CURLOPT_FOLLOWLOCATION => true,
]);
$loginResp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
echo "Login HTTP code: $httpCode, Effective URL: $effectiveUrl" . PHP_EOL;

// 3. GET student course show page (course 1: TDC)
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:80/drived-hub/student/courses/1',
    CURLOPT_HTTPGET => true,
    CURLOPT_FOLLOWLOCATION => true,
]);
$courseHtml = curl_exec($ch);
preg_match('/name="_token" value="([^"]+)"/', $courseHtml, $matches);
$courseToken = $matches[1] ?? $token;

preg_match('/name="package_id"[^>]*>.*?<option value="(\d+)"/s', $courseHtml, $pkgMatches);
$packageId = $pkgMatches[1] ?? null;
echo "Found package ID: " . ($packageId ?? 'none') . PHP_EOL;

// 4. POST enroll
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:80/drived-hub/student/enroll/1',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        '_token' => $courseToken,
        'experience_level' => 'new_driver',
        'package_id' => $packageId,
        'branch_id' => '1',
        'requested_dl_code' => 'A',
        'notes' => 'Testing student enrollment 500 error',
    ],
    CURLOPT_FOLLOWLOCATION => false,
]);
$enrollResp = curl_exec($ch);
$enrollCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
echo "Enroll HTTP code: $enrollCode, Redirect URL: $redirectUrl" . PHP_EOL;
if ($enrollCode >= 400) {
    echo "Response body: " . substr($enrollResp, 0, 1000) . PHP_EOL;
}
curl_close($ch);
