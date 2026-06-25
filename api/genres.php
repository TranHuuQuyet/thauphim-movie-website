<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

try {
    $pdo = getDatabaseConnection();
    $statement = $pdo->query(
        'SELECT
            g.id,
            g.tmdb_genre_id,
            g.name,
            g.slug,
            COUNT(mg.movie_id) AS movie_count
         FROM genres g
         LEFT JOIN movie_genres mg ON mg.genre_id = g.id
         GROUP BY g.id, g.tmdb_genre_id, g.name, g.slug
         ORDER BY g.name ASC'
    );

    $genres = array_map(static function (array $genre): array {
        return [
            'id' => (int) $genre['id'],
            'tmdb_genre_id' => isset($genre['tmdb_genre_id']) ? (int) $genre['tmdb_genre_id'] : null,
            'name' => $genre['name'],
            'slug' => $genre['slug'],
            'movie_count' => (int) $genre['movie_count'],
        ];
    }, $statement->fetchAll());

    apiSuccess($genres);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    apiError('Khong the tai danh sach the loai.');
}
