<?php

$baseUrl = 'http://localhost:8000/api';

echo "Testing News Aggregation API\n";
echo "============================\n\n";

// Test 1: Register a new user
echo "1. Testing user registration...\n";
$registerData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/register');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registerData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "✓ User registered successfully\n";
    $token = $data['token'] ?? null;
} else {
    echo "✗ Registration failed (HTTP $httpCode): $response\n";
    exit(1);
}

// Test 2: Get latest articles
echo "\n2. Testing get latest articles...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/articles');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✓ Articles endpoint working\n";
} else {
    echo "✗ Articles endpoint failed (HTTP $httpCode): $response\n";
}

// Test 3: Search articles
echo "\n3. Testing search articles...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/articles/search?keyword=technology&per_page=5');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✓ Search endpoint working\n";
} else {
    echo "✗ Search endpoint failed (HTTP $httpCode): $response\n";
}

// Test 4: Get user preferences (requires auth)
if ($token) {
    echo "\n4. Testing get user preferences...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/user/preferences');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        echo "✓ User preferences endpoint working\n";
    } else {
        echo "✗ User preferences endpoint failed (HTTP $httpCode): $response\n";
    }
}

echo "\nAPI Test completed!\n";
