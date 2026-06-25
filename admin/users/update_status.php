<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    admin_redirect('users/index.php');
}

admin_verify_csrf();

$id = admin_get_id($_POST);
$status = (string) ($_POST['status'] ?? '');
$currentAdminId = (int) ($adminUser['id'] ?? 0);

if ($id > 0 && $id !== $currentAdminId && in_array($status, ['active', 'locked'], true)) {
    $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
}

admin_redirect('users/index.php');
