<?php

declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    admin_redirect('watch-errors/watch-errors.php');
}

admin_verify_csrf();

$id = admin_get_id($_POST);
$action = (string) ($_POST['action'] ?? '');
$return = (string) ($_POST['return'] ?? 'watch-errors/watch-errors.php');

$allowedReturns = [
    'watch-errors/watch-errors.php',
    'dashboard.php',
];

$isSafeReturn = false;

foreach ($allowedReturns as $allowedReturn) {
    if (str_starts_with($return, $allowedReturn)) {
        $isSafeReturn = true;
        break;
    }
}

if (!$isSafeReturn) {
    $return = 'watch-errors/watch-errors.php';
}

if ($id <= 0 || !in_array($action, ['fix', 'unfix'], true)) {
    admin_redirect($return);
}

$stmt = $pdo->prepare("
    SELECT id, content
    FROM comments
    WHERE id = ?
        AND content LIKE '[Error]%'
    LIMIT 1
");
$stmt->execute([$id]);

$comment = $stmt->fetch();

if (!$comment) {
    admin_redirect($return);
}

$content = (string) $comment['content'];

if ($action === 'fix') {
    $content = str_replace('[Fixed]', '', $content);
    $content = preg_replace('/^\[Error\]\s*/u', '[Error][Fixed] ', $content, 1) ?? $content;
}

if ($action === 'unfix') {
    $content = str_replace('[Fixed]', '', $content);
    $content = preg_replace('/^\[Error\]\s*/u', '[Error] ', $content, 1) ?? $content;
}

$stmt = $pdo->prepare("
    UPDATE comments
    SET content = ?, status = 'hidden'
    WHERE id = ?
");
$stmt->execute([$content, $id]);

admin_redirect($return);