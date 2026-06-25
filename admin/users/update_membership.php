<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    admin_redirect('users/index.php');
}

admin_verify_csrf();

$id = admin_get_id($_POST);
$membership = (string) ($_POST['membership'] ?? '');

if ($id > 0 && in_array($membership, ['free', 'premium'], true)) {
    $stmt = $pdo->prepare('UPDATE users SET membership = ? WHERE id = ?');
    $stmt->execute([$membership, $id]);
}

admin_redirect('users/index.php');
