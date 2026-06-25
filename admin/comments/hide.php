<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    admin_redirect('comments/index.php');
}

admin_verify_csrf();

$id = admin_get_id($_POST);
$status = (string) ($_POST['status'] ?? '');

if ($id > 0 && in_array($status, ['visible', 'hidden'], true)) {
    $stmt = $pdo->prepare('UPDATE comments SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
}

admin_redirect('comments/index.php');
