<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    admin_redirect('countries/index.php');
}

admin_verify_csrf();

$id = admin_get_id($_POST);
if ($id > 0) {
    $stmt = $pdo->prepare('DELETE FROM countries WHERE id = ?');
    $stmt->execute([$id]);
}

admin_redirect('countries/index.php');
