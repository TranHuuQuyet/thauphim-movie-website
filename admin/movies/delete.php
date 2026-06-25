<?php

require_once '../../includes/config.php';
require_once '../../includes/db.php';

$pdo = getDatabaseConnection();

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    DELETE FROM movies
    WHERE id = ?
");

$stmt->execute([$id]);

header("Location: index.php");
exit;