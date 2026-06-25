<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

$pdo = getDatabaseConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$user = apiRequireUser($pdo);

if ($method === 'GET') {
    $movieId = apiIntParam('movie_id', 0, 0, PHP_INT_MAX);
} else {
    $payload = apiReadJson();
    $movieId = isset($payload['movie_id']) ? (int) $payload['movie_id'] : 0;
}

if (!apiMovieExists($pdo, $movieId)) {
    apiError('Phim khong hop le', 400);
}

$countStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM favorites
    WHERE movie_id = ?
");
$stateStmt = $pdo->prepare("
    SELECT id
    FROM favorites
    WHERE user_id = ? AND movie_id = ?
    LIMIT 1
");

if ($method === 'GET') {
    $stateStmt->execute([(int) $user['id'], $movieId]);
    $countStmt->execute([$movieId]);

    apiSuccess([
        'movie_id' => $movieId,
        'is_favorite' => (bool) $stateStmt->fetch(),
        'favorite_count' => (int) $countStmt->fetchColumn(),
    ]);
}

if ($method !== 'POST') {
    apiError('Method khong duoc ho tro', 405);
}

$stateStmt->execute([(int) $user['id'], $movieId]);
$favorite = $stateStmt->fetch();

if ($favorite) {
    $stmt = $pdo->prepare("
        DELETE FROM favorites
        WHERE id = ?
    ");
    $stmt->execute([(int) $favorite['id']]);
    $isFavorite = false;
} else {
    $stmt = $pdo->prepare("
        INSERT INTO favorites (user_id, movie_id)
        VALUES (?, ?)
    ");
    $stmt->execute([(int) $user['id'], $movieId]);
    $isFavorite = true;
}

$countStmt->execute([$movieId]);

apiSuccess([
    'movie_id' => $movieId,
    'is_favorite' => $isFavorite,
    'favorite_count' => (int) $countStmt->fetchColumn(),
]);

