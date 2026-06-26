<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

$code = strtoupper(apiStringParam('code'));

if ($code === '' || !preg_match('/^[A-Z]{2}$/', $code)) {
    apiError('Ma quoc gia khong hop le.', 400);
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
        apiError('Khong tim thay quoc gia.', 404);
    }

    $movieStatement = $pdo->prepare(
        'SELECT
            m.*,
            c.code AS country_code,
            c.name AS country_name
         FROM movies m
         LEFT JOIN countries c ON c.id = m.country_id
         WHERE m.country_id = :country_id
         ORDER BY m.release_date DESC, m.created_at DESC, m.id DESC
         LIMIT 24'
    );
    $movieStatement->execute(['country_id' => (int) $country['id']]);

    apiSuccess([
        'country' => [
            'id' => (int) $country['id'],
            'code' => $country['code'],
            'name' => $country['name'],
        ],
        'movies' => array_map('apiMovieRow', $movieStatement->fetchAll()),
    ]);
} catch (Throwable $exception) {
    apiServerError('Khong the tai phim theo quoc gia.', $exception);
}
