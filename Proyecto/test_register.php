<?php
$url = 'http://localhost:8000/register';
$data = [
    'email' => 'test_zencoder@gmail.com',
    'username' => 'test_zencoder',
    'password' => 'password123',
    'confirm_password' => 'password123'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
        'ignore_errors' => true
    ]
];
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Response headers:\n";
print_r($http_response_header);
echo "\nResponse body:\n";
echo substr($result, 0, 500);
