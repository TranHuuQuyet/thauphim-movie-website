<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$pdo = getDatabaseConnection();
$adminUser = auth_current_user($pdo);

if (!auth_is_admin($adminUser)) {
    if ($adminUser === null) {
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '/thauphim-movie-website/';
        header('Location: ' . rtrim($basePath, '/') . '/index.php#authModal');
        exit;
    }

    http_response_code(403);
    echo '403 Forbidden: admin access required.';
    exit;
}
