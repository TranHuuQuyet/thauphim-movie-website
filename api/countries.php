<?php
require_once __DIR__ . '/../includes/db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDatabaseConnection();
    $statement = $pdo->query(
        'SELECT id, code, name
         FROM countries
         ORDER BY id ASC'
    );

    echo json_encode([
        'success' => true,
        'data' => $statement->fetchAll(),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Không thể tải danh sách quốc gia.',
    ], JSON_UNESCAPED_UNICODE);
}
