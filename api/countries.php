<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

try {
    $pdo = getDatabaseConnection();
    $statement = $pdo->query(
        'SELECT
            c.id,
            c.code,
            c.name,
            COUNT(m.id) AS movie_count
         FROM countries c
         LEFT JOIN movies m ON m.country_id = c.id
         GROUP BY c.id, c.code, c.name
         ORDER BY c.name ASC'
    );

    $countries = array_map(static function (array $country): array {
        return [
            'id' => (int) $country['id'],
            'code' => $country['code'],
            'name' => $country['name'],
            'movie_count' => (int) $country['movie_count'],
        ];
    }, $statement->fetchAll());

    apiSuccess($countries);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    apiError('Khong the tai danh sach quoc gia.');
}
