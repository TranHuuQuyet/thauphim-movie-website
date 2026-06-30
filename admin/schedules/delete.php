<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    admin_redirect('schedules/index.php');
}

admin_verify_csrf();

$id = admin_get_id($_POST);
$movieId = admin_nullable_int($_POST['movie_id'] ?? null);

if ($id > 0) {
    $stmt = $pdo->prepare('DELETE FROM schedules WHERE id = ?');
    $stmt->execute([$id]);
}

$params = ['success' => 'deleted'];
if ($movieId !== null && $movieId > 0) {
    $params['movie_id'] = $movieId;
}

$query = empty($params) ? '' : '?' . http_build_query($params);

admin_redirect('schedules/index.php' . $query);
