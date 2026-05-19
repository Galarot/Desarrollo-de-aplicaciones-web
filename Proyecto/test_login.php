<?php
$loginUrl = 'http://localhost:8000/login';

// Get CSRF token
$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
$html = curl_exec($ch);
curl_close($ch);

preg_match('/name="_csrf_token" value="([^"]+)"/', $html, $matches);
$csrfToken = $matches[1] ?? '';

echo "CSRF Token: $csrfToken\n";

if (!$csrfToken) {
    echo "Could not find CSRF token\n";
    exit;
}

// Perform login
$data = [
    '_username' => 'test_zencoder',
    '_password' => 'password123',
    '_csrf_token' => $csrfToken
];

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't follow to see the 302
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
curl_close($ch);

echo "Login Response:\n";
echo $response;
