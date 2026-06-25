<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

try {
    $movieId = filter_input(INPUT_GET, 'movie_id', FILTER_VALIDATE_INT);
    if ($movieId === false || $movieId === null || $movieId <= 0) {
        apiError('ID phim khong hop le.', 400);
    }

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'SELECT id, movie_id, episode_number, title, youtube_url, duration_seconds, is_published
         FROM episodes
         WHERE movie_id = :movie_id
           AND is_published = 1
         ORDER BY episode_number ASC'
    );
    $statement->execute(['movie_id' => (int) $movieId]);

    $episodes = array_map(static function (array $episode): array {
        return [
            'id' => (int) $episode['id'],
            'movie_id' => (int) $episode['movie_id'],
            'episode_number' => (int) $episode['episode_number'],
            'title' => $episode['title'],
            'youtube_url' => $episode['youtube_url'],
            'duration_seconds' => isset($episode['duration_seconds']) ? (int) $episode['duration_seconds'] : null,
            'is_published' => !empty($episode['is_published']),
        ];
    }, $statement->fetchAll());

    apiSuccess($episodes);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    apiError('Khong the tai danh sach tap phim.');
}
