<?php
require_once __DIR__ . '/../includes/db_connection.php';

header('Content-Type: application/json; charset=utf-8');

$code = strtoupper(trim($_GET['code'] ?? ''));

if ($code === '' || !preg_match('/^[A-Z]{2}$/', $code)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Mã quốc gia không hợp lệ.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = getDatabaseConnection();

    $countryStatement = $pdo->prepare(
        'SELECT id, code, name
         FROM countries
         WHERE code = :code
         LIMIT 1'
    );
    $countryStatement->execute(['code' => $code]);
    $country = $countryStatement->fetch();

    if (!$country) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy quốc gia.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $movieStatement = $pdo->prepare(
        'SELECT
            id,
            title,
            poster,
            backdrop,
            release_year,
            type,
            quality,
            status,
            is_premium,
            views,
            rating_average,
            rating_count
         FROM movies
         WHERE country_id = :country_id
         ORDER BY created_at DESC, id DESC
         LIMIT 24'
    );
    $movieStatement->execute(['country_id' => $country['id']]);

    echo json_encode([
        'success' => true,
        'country' => $country,
        'movies' => $movieStatement->fetchAll(),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Không thể tải phim theo quốc gia.',
    ], JSON_UNESCAPED_UNICODE);
}
