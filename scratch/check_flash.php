<?php
$ch = curl_init();
$cookieFile = __DIR__ . '/cookies.txt';

curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:80/drived-hub/student/courses/1',
    CURLOPT_HTTPGET => true,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_RETURNTRANSFER => true,
]);
$html = curl_exec($ch);
preg_match('/<div class="alert[^>]*>(.*?)<\/div>/s', $html, $m);
if (!empty($m[0])) {
    echo "Alert: " . strip_tags($m[0]) . PHP_EOL;
}
preg_match('/toastr\.(error|warning|info|success)\("([^"]+)"\)/', $html, $m2);
if (!empty($m2[0])) {
    echo "Toastr: " . $m2[0] . PHP_EOL;
}
preg_match_all('/(alert-[a-z]+|text-danger|error)[^>]*>(.*?)<\//', $html, $all);
print_r($all[0] ?? []);
