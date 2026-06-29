<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';

if (!defined('APP_BASE_PATH')) {
    define('APP_BASE_PATH', '/');
}

if (!defined('ADMIN_BASE_PATH')) {
    define('ADMIN_BASE_PATH', APP_BASE_PATH . 'admin/');
}

function admin_e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_url(string $path = ''): string
{
    return rtrim(APP_BASE_PATH, '/') . '/' . ltrim($path, '/');
}

function admin_url(string $path = ''): string
{
    return rtrim(ADMIN_BASE_PATH, '/') . '/' . ltrim($path, '/');
}

function admin_redirect(string $path): never
{
    header('Location: ' . admin_url($path));
    exit;
}

function admin_csrf_token(): string
{
    auth_start_session();

    if (empty($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['admin_csrf_token'];
}

function admin_csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . admin_e(admin_csrf_token()) . '">';
}

function admin_verify_csrf(): void
{
    $submitted = (string) ($_POST['csrf_token'] ?? '');

    if ($submitted === '' || !hash_equals(admin_csrf_token(), $submitted)) {
        http_response_code(403);
        echo 'Invalid CSRF token.';
        exit;
    }
}

function admin_get_id(array $source): int
{
    $id = filter_var($source['id'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    return $id === false || $id === null ? 0 : (int) $id;
}

function admin_nullable_int(mixed $value): ?int
{
    if ($value === null || trim((string) $value) === '') {
        return null;
    }

    $intValue = filter_var($value, FILTER_VALIDATE_INT);

    return $intValue === false ? null : (int) $intValue;
}

function admin_slugify(string $value): string
{
    $value = trim($value);

    if (function_exists('iconv')) {
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($converted !== false) {
            $value = $converted;
        }
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value !== '' ? $value : 'item';
}

function admin_normalize_youtube_url(string $url): string
{
    $url = trim($url);

    if ($url === '') {
        return '';
    }

    $parts = parse_url($url);

    if ($parts === false || empty($parts['host'])) {
        return '';
    }

    $host = strtolower((string) $parts['host']);
    $host = preg_replace('/^www\./', '', $host) ?? $host;
    $path = trim((string) ($parts['path'] ?? ''), '/');
    $segments = $path === '' ? [] : explode('/', $path);
    $query = [];

    if (!empty($parts['query'])) {
        parse_str((string) $parts['query'], $query);
    }

    $videoId = '';

    if ($host === 'youtu.be') {
        $videoId = (string) ($segments[0] ?? '');
    } elseif (in_array($host, ['youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtube-nocookie.com'], true)) {
        $firstSegment = (string) ($segments[0] ?? '');

        if (in_array($firstSegment, ['embed', 'shorts', 'live', 'v'], true)) {
            $videoId = (string) ($segments[1] ?? '');
        } elseif ($firstSegment === 'watch') {
            $videoId = (string) ($query['v'] ?? '');
        }
    }

    if (!preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
        return '';
    }

    return 'https://www.youtube.com/embed/' . $videoId;
}

function admin_render_messages(array $errors, string $success = ''): void
{
    if ($success !== '') {
        echo '<div class="admin-alert admin-alert--success">' . admin_e($success) . '</div>';
    }

    foreach ($errors as $error) {
        echo '<div class="admin-alert admin-alert--danger">' . admin_e($error) . '</div>';
    }
}

function admin_sync_movie_rating(PDO $pdo, int $movieId): void
{
    $stmt = $pdo->prepare('
        SELECT AVG(rating) AS rating_average, COUNT(*) AS rating_count
        FROM ratings
        WHERE movie_id = ?
    ');
    $stmt->execute([$movieId]);
    $summary = $stmt->fetch() ?: ['rating_average' => 0, 'rating_count' => 0];

    $stmt = $pdo->prepare('
        UPDATE movies
        SET rating_average = ?, rating_count = ?
        WHERE id = ?
    ');
    $stmt->execute([
        round((float) ($summary['rating_average'] ?? 0), 2),
        (int) ($summary['rating_count'] ?? 0),
        $movieId,
    ]);
}
