<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($path === '/health' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    http_response_code(200);
    echo json_encode(['status' => 'ok', 'service' => 'customer-api'], JSON_THROW_ON_ERROR);
    return;
}

if ($path === '/health/ready' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    try {
        $host = getenv('DB_HOST') ?: 'database';
        $port = getenv('DB_PORT') ?: '5432';
        $database = getenv('DB_NAME') ?: 'enterprise_api';
        $username = getenv('DB_USER') ?: 'enterprise_api';
        $password = getenv('DB_PASSWORD') ?: '';

        new PDO(
            sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $database),
            $username,
            $password,
            [PDO::ATTR_TIMEOUT => 3, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );

        http_response_code(200);
        echo json_encode(['status' => 'ready', 'database' => 'connected'], JSON_THROW_ON_ERROR);
    } catch (Throwable) {
        http_response_code(503);
        echo json_encode(['status' => 'not_ready', 'database' => 'unavailable'], JSON_THROW_ON_ERROR);
    }

    return;
}

http_response_code(404);
echo json_encode(['error' => 'Not Found'], JSON_THROW_ON_ERROR);
