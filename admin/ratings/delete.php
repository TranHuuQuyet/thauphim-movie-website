<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    admin_redirect('ratings/index.php');
}

admin_verify_csrf();

$id = admin_get_id($_POST);
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT movie_id FROM ratings WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $movieId = $stmt->fetchColumn();

    $stmt = $pdo->prepare('DELETE FROM ratings WHERE id = ?');
    $stmt->execute([$id]);

    if ($movieId !== false) {
        admin_sync_movie_rating($pdo, (int) $movieId);
    }
}

admin_redirect('ratings/index.php');
