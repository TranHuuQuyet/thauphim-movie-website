<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

$pdo = getDatabaseConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$currentUser = apiCurrentUser($pdo);

function apiRatingSummary(PDO $pdo, int $movieId, ?array $currentUser): array
{
    $stmt = $pdo->prepare("
        SELECT AVG(rating) AS rating_average, COUNT(*) AS rating_count
        FROM ratings
        WHERE movie_id = ?
    ");
    $stmt->execute([$movieId]);
    $summary = $stmt->fetch() ?: [
        'rating_average' => 0,
        'rating_count' => 0,
    ];

    $ratingAverage = round((float) ($summary['rating_average'] ?? 0), 2);
    $ratingCount = (int) ($summary['rating_count'] ?? 0);

    $userRating = null;
    if ($currentUser !== null) {
        $stmt = $pdo->prepare("
            SELECT rating
            FROM ratings
            WHERE user_id = ? AND movie_id = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $currentUser['id'], $movieId]);
        $value = $stmt->fetchColumn();
        $userRating = $value !== false ? (int) $value : null;
    }

    return [
        'movie_id' => $movieId,
        'rating_average' => $ratingAverage,
        'rating_count' => $ratingCount,
        'user_rating' => $userRating,
    ];
}

function apiSyncMovieRating(PDO $pdo, int $movieId): void
{
    $stmt = $pdo->prepare("
        SELECT AVG(rating) AS rating_average, COUNT(*) AS rating_count
        FROM ratings
        WHERE movie_id = ?
    ");
    $stmt->execute([$movieId]);
    $summary = $stmt->fetch() ?: [
        'rating_average' => 0,
        'rating_count' => 0,
    ];

    $stmt = $pdo->prepare("
        UPDATE movies
        SET rating_average = ?, rating_count = ?
        WHERE id = ?
    ");
    $stmt->execute([
        round((float) ($summary['rating_average'] ?? 0), 2),
        (int) ($summary['rating_count'] ?? 0),
        $movieId,
    ]);
}

if ($method === 'GET') {
    $movieId = apiIntParam('movie_id', 0, 0, PHP_INT_MAX);

    if (!apiMovieExists($pdo, $movieId)) {
        apiError('Phim khong hop le', 400);
    }

    apiSuccess(apiRatingSummary($pdo, $movieId, $currentUser));
}

if ($method !== 'POST') {
    apiError('Method khong duoc ho tro', 405);
}

$user = apiRequireUser($pdo);
$payload = apiReadJson();
$movieId = isset($payload['movie_id']) ? (int) $payload['movie_id'] : 0;
$rating = isset($payload['rating']) ? (int) $payload['rating'] : 0;

if (!apiMovieExists($pdo, $movieId)) {
    apiError('Phim khong hop le', 400);
}

if ($rating < 1 || $rating > 5) {
    apiError('Danh gia can tu 1 den 5 sao', 400);
}

$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("
        INSERT INTO ratings (user_id, movie_id, rating)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
            rating = VALUES(rating),
            updated_at = NOW()
    ");
    $stmt->execute([(int) $user['id'], $movieId, $rating]);

    apiSyncMovieRating($pdo, $movieId);
    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    error_log($exception->getMessage());
    apiError('Khong the luu danh gia', 500);
}

apiSuccess(apiRatingSummary($pdo, $movieId, $user));

