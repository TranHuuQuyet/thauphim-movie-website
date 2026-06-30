<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

try {
    $pdo = getDatabaseConnection();

    $type = strtolower(apiStringParam('type'));
    if ($type === 'tv') {
        $type = 'series';
    }
    $hideEmpty = filter_input(INPUT_GET, 'hide_empty', FILTER_VALIDATE_BOOLEAN);
    $sql = 'SELECT 
                g.id, 
                g.tmdb_genre_id, 
                g.name, 
                g.slug, 
                COUNT(m.id) AS movie_count
            FROM genres g
            LEFT JOIN movie_genres mg ON mg.genre_id = g.id';
    
    $params = [];

    if ($type !== '') {
        if (!in_array($type, ['movie', 'series'], true)) {
            apiError('Loai phim khong hop le.', 400);
        }
        $sql .= ' LEFT JOIN movies m ON mg.movie_id = m.id AND m.type = :type';
        $params['type'] = $type;
    } else {
        $sql .= ' LEFT JOIN movies m ON mg.movie_id = m.id';
    }

    $sql .= ' GROUP BY g.id, g.tmdb_genre_id, g.name, g.slug';

    if ($hideEmpty) {
        $sql .= ' HAVING movie_count > 0';
    }
    $sql .= ' ORDER BY g.name ASC';
    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    $genres = array_map(static function (array $genre): array {
        return [
            'id'            => (int) $genre['id'],
            'tmdb_genre_id' => $genre['tmdb_genre_id'] !== null ? (int) $genre['tmdb_genre_id'] : null,
            'name'          => $genre['name'],
            'slug'          => $genre['slug'],
            'movie_count'   => (int) $genre['movie_count'],
        ];
    }, $statement->fetchAll());

    apiSuccess($genres);
} catch (Throwable $exception) {
    apiServerError('Khong the tai danh sach the loai.', $exception);
}