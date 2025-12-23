<?php

$baseUrl = 'http://127.0.0.1:8000/api/cities';

function test($name, $callback) {
    echo "Testing: $name ... ";
    try {
        $callback();
        echo "PASSED\n";
    } catch (Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
        exit(1);
    }
}

function request($method, $url, $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'body' => json_decode($response, true)];
}

// 1. List cities (should be empty initially)
test('List cities (empty)', function() use ($baseUrl) {
    $res = request('GET', $baseUrl);
    if ($res['code'] !== 200 || !empty($res['body'])) {
        throw new Exception("Expected 200 and empty array, got {$res['code']} and " . json_encode($res['body']));
    }
});

// 2. Create a city
$cityId = null;
test('Create city', function() use ($baseUrl, &$cityId) {
    $data = ['name' => 'Tokyo', 'country' => 'Japan', 'population' => 14000000];
    $res = request('POST', $baseUrl, $data);
    if ($res['code'] !== 201 || $res['body']['name'] !== 'Tokyo') {
        throw new Exception("Expected 201 and name Tokyo, got {$res['code']} and " . json_encode($res['body']));
    }
    $cityId = $res['body']['id'];
});

// 3. Get specific city
test('Get city', function() use ($baseUrl, &$cityId) {
    $res = request('GET', "$baseUrl/$cityId");
    if ($res['code'] !== 200 || $res['body']['name'] !== 'Tokyo') {
        throw new Exception("Expected 200 and name Tokyo, got {$res['code']} and " . json_encode($res['body']));
    }
});

// 4. Update city
test('Update city', function() use ($baseUrl, &$cityId) {
    $data = ['population' => 14000001];
    $res = request('PUT', "$baseUrl/$cityId", $data);
    if ($res['code'] !== 200 || $res['body']['population'] !== 14000001) {
        throw new Exception("Expected 200 and population 14000001, got {$res['code']} and " . json_encode($res['body']));
    }
});

// 5. Delete city
test('Delete city', function() use ($baseUrl, &$cityId) {
    $res = request('DELETE', "$baseUrl/$cityId");
    if ($res['code'] !== 204) {
        throw new Exception("Expected 204, got {$res['code']}");
    }
});

// 6. Verify deletion
test('Verify deletion', function() use ($baseUrl, &$cityId) {
    $res = request('GET', "$baseUrl/$cityId");
    if ($res['code'] !== 404) {
        throw new Exception("Expected 404, got {$res['code']}");
    }
});
