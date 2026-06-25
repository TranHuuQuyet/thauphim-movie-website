<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

const APP_BASE_PATH = '/thauphim-movie-website/';
const TMDB_IMAGE_BASE = 'https://image.tmdb.org/t/p/';

function apiSend(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function apiSuccess(mixed $data, ?array $meta = null): void
{
    $payload = [
        'success' => true,
        'data' => $data,
    ];

    if ($meta !== null) {
        $payload['meta'] = $meta;
    }

    apiSend($payload);
}

function apiError(string $message, int $statusCode = 500): void
{
    apiSend([
        'success' => false,
        'message' => $message,
    ], $statusCode);
}

function apiReadJson(): array
{
    $rawInput = file_get_contents('php://input');
    if ($rawInput === false || trim($rawInput) === '') {
        return [];
    }

    $payload = json_decode($rawInput, true);

    if (!is_array($payload)) {
        apiError('JSON khong hop le', 400);
    }

    return $payload;
}

function apiCurrentUser(PDO $pdo): ?array
{
    return auth_current_user($pdo);
}

function apiRequireUser(PDO $pdo): array
{
    $user = apiCurrentUser($pdo);

    if ($user === null) {
        apiError('Vui long dang nhap', 401);
    }

    return $user;
}

function apiMovieExists(PDO $pdo, int $movieId): bool
{
    if ($movieId <= 0) {
        return false;
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM movies
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$movieId]);

    return (bool) $stmt->fetch();
}

function apiIntParam(string $name, int $default, int $min, int $max): int
{
    $value = filter_input(INPUT_GET, $name, FILTER_VALIDATE_INT);
    if ($value === false || $value === null) {
        return $default;
    }

    return max($min, min($max, (int) $value));
}

function apiStringParam(string $name): string
{
    return trim((string) ($_GET[$name] ?? ''));
}

function apiImageUrl(?string $override, ?string $tmdbPath, string $size): ?string
{
    $override = trim((string) $override);
    if ($override !== '') {
        if (preg_match('/^https?:\/\//i', $override) || str_starts_with($override, '/')) {
            return $override;
        }

        return APP_BASE_PATH . ltrim($override, '/');
    }

    $tmdbPath = trim((string) $tmdbPath);
    if ($tmdbPath === '') {
        return null;
    }

    return TMDB_IMAGE_BASE . $size . $tmdbPath;
}

function apiMovieRow(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'tmdb_id' => isset($row['tmdb_id']) ? (int) $row['tmdb_id'] : null,
        'tmdb_type' => $row['tmdb_type'] ?? null,
        'type' => $row['type'],
        'title' => $row['title'],
        'original_title' => $row['original_title'] ?? null,
        'overview' => $row['overview'] ?? null,
        'description' => $row['description'] ?? null,
        'poster' => $row['poster'] ?? null,
        'backdrop' => $row['backdrop'] ?? null,
        'poster_path' => $row['poster_path'] ?? null,
        'backdrop_path' => $row['backdrop_path'] ?? null,
        'poster_url' => apiImageUrl($row['poster'] ?? null, $row['poster_path'] ?? null, 'w500'),
        'backdrop_url' => apiImageUrl($row['backdrop'] ?? null, $row['backdrop_path'] ?? null, 'original'),
        'release_date' => $row['release_date'] ?? null,
        'release_year' => isset($row['release_year']) ? (int) $row['release_year'] : null,
        'runtime' => isset($row['runtime']) ? (int) $row['runtime'] : null,
        'quality' => $row['quality'] ?? 'HD',
        'status' => $row['status'] ?? 'completed',
        'is_premium' => !empty($row['is_premium']),
        'views' => isset($row['views']) ? (int) $row['views'] : 0,
        'rating_average' => isset($row['rating_average']) ? (float) $row['rating_average'] : 0.0,
        'rating_count' => isset($row['rating_count']) ? (int) $row['rating_count'] : 0,
        'vote_average' => isset($row['vote_average']) ? (float) $row['vote_average'] : 0.0,
        'vote_count' => isset($row['vote_count']) ? (int) $row['vote_count'] : 0,
        'popularity' => isset($row['popularity']) ? (float) $row['popularity'] : 0.0,
        'original_language' => $row['original_language'] ?? null,
        'country' => !empty($row['country_id']) ? [
            'id' => (int) $row['country_id'],
            'code' => $row['country_code'] ?? null,
            'name' => $row['country_name'] ?? null,
        ] : null,
    ];
}

function apiPaginationMeta(int $page, int $limit, int $total): array
{
    return [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'total_pages' => $limit > 0 ? (int) ceil($total / $limit) : 0,
    ];
}
