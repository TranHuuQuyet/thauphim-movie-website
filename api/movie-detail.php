<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

try {
    $movieId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($movieId === false || $movieId === null || $movieId <= 0) {
        apiError('ID phim khong hop le.', 400);
    }

    $pdo = getDatabaseConnection();

    $statement = $pdo->prepare(
        'SELECT
            m.*,
            c.code AS country_code,
            c.name AS country_name
         FROM movies m
         LEFT JOIN countries c ON c.id = m.country_id
         WHERE m.id = :id
         LIMIT 1'
    );
    $statement->execute(['id' => (int) $movieId]);
    $movie = $statement->fetch();

    if (!$movie) {
        apiError('Khong tim thay phim.', 404);
    }

    $genreStatement = $pdo->prepare(
        'SELECT g.id, g.tmdb_genre_id, g.name, g.slug
         FROM genres g
         INNER JOIN movie_genres mg ON mg.genre_id = g.id
         WHERE mg.movie_id = :movie_id
         ORDER BY g.name ASC'
    );
    $genreStatement->execute(['movie_id' => (int) $movieId]);

    $actorStatement = $pdo->prepare(
        'SELECT
            a.id,
            a.tmdb_actor_id,
            a.name,
            a.avatar,
            a.profile_path,
            a.known_for_department,
            ma.character_name,
            ma.cast_order
         FROM actors a
         INNER JOIN movie_actors ma ON ma.actor_id = a.id
         WHERE ma.movie_id = :movie_id
         ORDER BY ma.cast_order ASC, a.name ASC'
    );
    $actorStatement->execute(['movie_id' => (int) $movieId]);

    $episodeStatement = $pdo->prepare(
        'SELECT id, movie_id, episode_number, title, youtube_url, duration_seconds, is_published
         FROM episodes
         WHERE movie_id = :movie_id
           AND is_published = 1
         ORDER BY episode_number ASC'
    );
    $episodeStatement->execute(['movie_id' => (int) $movieId]);

    $actors = array_map(static function (array $actor): array {
        return [
            'id' => (int) $actor['id'],
            'tmdb_actor_id' => isset($actor['tmdb_actor_id']) ? (int) $actor['tmdb_actor_id'] : null,
            'name' => $actor['name'],
            'avatar' => $actor['avatar'],
            'profile_path' => $actor['profile_path'],
            'profile_url' => apiImageUrl($actor['avatar'], $actor['profile_path'], 'w500'),
            'known_for_department' => $actor['known_for_department'],
            'character_name' => $actor['character_name'],
            'cast_order' => isset($actor['cast_order']) ? (int) $actor['cast_order'] : null,
        ];
    }, $actorStatement->fetchAll());

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
    }, $episodeStatement->fetchAll());

    apiSuccess([
        'movie' => apiMovieRow($movie),
        'genres' => $genreStatement->fetchAll(),
        'actors' => $actors,
        'episodes' => $episodes,
    ]);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    apiError('Khong the tai chi tiet phim.');
}
