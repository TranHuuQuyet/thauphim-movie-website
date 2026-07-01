<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

$pdo = getDatabaseConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$currentUser = apiCurrentUser($pdo);

function apiCommentRow(array $row, ?array $currentUser): array
{
    $createdAt = (string) ($row['created_at'] ?? '');
    $timestamp = $createdAt !== '' ? strtotime($createdAt) : false;

    return [
        'id' => (int) $row['id'],
        'movie_id' => (int) $row['movie_id'],
        'content' => (string) $row['content'],
        'username' => (string) $row['username'],
        'created_at' => $createdAt,
        'created_at_label' => $timestamp ? date('d/m/Y H:i', $timestamp) : '',
        'can_delete' => $currentUser !== null && (
            (int) $currentUser['id'] === (int) $row['user_id'] ||
            auth_is_admin($currentUser)
        ),
    ];
}

if ($method === 'GET') {
    $movieId = apiIntParam('movie_id', 0, 0, PHP_INT_MAX);

    if (!apiMovieExists($pdo, $movieId)) {
        apiError('Phim khong hop le', 400);
    }

    $stmt = $pdo->prepare("
        SELECT comments.*, users.username
        FROM comments
        INNER JOIN users ON comments.user_id = users.id
        WHERE comments.movie_id = ?
          AND comments.status = 'visible'
        ORDER BY comments.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$movieId]);

    $comments = array_map(
        static fn(array $row): array => apiCommentRow($row, $currentUser),
        $stmt->fetchAll()
    );

    apiSuccess([
        'movie_id' => $movieId,
        'comments' => $comments,
    ]);
}

if ($method === 'POST') {
    $user = apiRequireUser($pdo);
    $payload = apiReadJson();
    $movieId = isset($payload['movie_id']) ? (int) $payload['movie_id'] : 0;
    $content = trim((string) ($payload['content'] ?? ''));

    if (!apiMovieExists($pdo, $movieId)) {
        apiError('Phim khong hop le', 400);
    }

    if ($content === '') {
        apiError('Binh luan khong duoc trong', 400);
    }

    $contentLength = function_exists('mb_strlen') ? mb_strlen($content, 'UTF-8') : strlen($content);

    if ($contentLength > 1000) {
        apiError('Binh luan toi da 1000 ky tu', 400);
    }

    $isWatchError = str_starts_with($content, '[Error]');
    $commentStatus = $isWatchError ? 'hidden' : 'visible';

    $stmt = $pdo->prepare("
        INSERT INTO comments (user_id, movie_id, content, status)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([(int) $user['id'], $movieId, $content, $commentStatus]);

    $commentId = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare("
        SELECT comments.*, users.username
        FROM comments
        INNER JOIN users ON comments.user_id = users.id
        WHERE comments.id = ?
        LIMIT 1
    ");
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch();

    apiSuccess([
        'comment' => apiCommentRow($comment, $user),
    ], null);
}

if ($method === 'DELETE') {
    $user = apiRequireUser($pdo);
    $payload = apiReadJson();
    $commentId = isset($payload['comment_id']) ? (int) $payload['comment_id'] : 0;

    if ($commentId <= 0) {
        apiError('Binh luan khong hop le', 400);
    }

    $stmt = $pdo->prepare("
        SELECT id, user_id
        FROM comments
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch();

    if (!$comment) {
        apiError('Khong tim thay binh luan', 404);
    }

    if ((int) $comment['user_id'] !== (int) $user['id'] && !auth_is_admin($user)) {
        apiError('Khong co quyen xoa binh luan', 403);
    }

    $stmt = $pdo->prepare("
        DELETE FROM comments
        WHERE id = ?
    ");
    $stmt->execute([$commentId]);

    apiSuccess([
        'comment_id' => $commentId,
        'deleted' => true,
    ]);
}

apiError('Method khong duoc ho tro', 405);
