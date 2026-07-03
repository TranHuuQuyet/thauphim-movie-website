<?php

declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    apiError('Phuong thuc khong duoc ho tro', 405);
}

$pdo = getDatabaseConnection();
$currentUser = apiCurrentUser($pdo);
$input = apiReadJson();

$movieId = isset($input['movie_id']) ? (int) $input['movie_id'] : 0;
$episodeId = isset($input['episode_id']) ? (int) $input['episode_id'] : 0;

if ($movieId <= 0 || $episodeId <= 0) {
    apiError('Thieu movie_id hoac episode_id', 400);
}

$stmt = $pdo->prepare("
    SELECT movies.*
    FROM episodes
    INNER JOIN movies ON movies.id = episodes.movie_id
    WHERE episodes.id = ?
      AND episodes.movie_id = ?
      AND episodes.is_published = 1
      AND episodes.youtube_url IS NOT NULL
      AND episodes.youtube_url <> ''
    LIMIT 1
");
$stmt->execute([$episodeId, $movieId]);
$movie = $stmt->fetch();

if (!$movie) {
    apiError('Phim hoac tap phim khong hop le', 404);
}

$watchAccess = auth_can_watch_movie($movie, $currentUser);

if (!$watchAccess['allowed']) {
    apiError((string) $watchAccess['message'], 403);
}

$countedMovieViews = $_SESSION['counted_movie_views'] ?? [];

if (!is_array($countedMovieViews)) {
    $countedMovieViews = [];
}

$sessionMovieKey = (string) $movieId;

if (!empty($countedMovieViews[$sessionMovieKey])) {
    apiSuccess([
        'counted' => false,
        'movie_id' => $movieId,
    ]);
}

try {
    $stmt = $pdo->prepare('
        UPDATE movies
        SET views = views + 1
        WHERE id = ?
    ');
    $stmt->execute([$movieId]);

    if ($stmt->rowCount() !== 1) {
        apiError('Khong the ghi nhan luot xem', 404);
    }

    $countedMovieViews[$sessionMovieKey] = true;
    $_SESSION['counted_movie_views'] = $countedMovieViews;

    apiSuccess([
        'counted' => true,
        'movie_id' => $movieId,
    ]);
} catch (Throwable $exception) {
    apiServerError('Khong the ghi nhan luot xem', $exception);
}
