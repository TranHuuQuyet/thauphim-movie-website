<?php

require_once '../../includes/config.php';
require_once '../../includes/db.php';

$pdo = getDatabaseConnection();

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT *
    FROM movies
    WHERE id = ?
");

$stmt->execute([$id]);

$movie = $stmt->fetch();

if(!$movie)
{
    die('Không tìm thấy phim');
}

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $stmt = $pdo->prepare("
        UPDATE movies
        SET
            title = ?,
            description = ?,
            release_year = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['title'],
        $_POST['description'],
        $_POST['release_year'],
        $id
    ]);

    header("Location: index.php");
    exit;
}

include '../layout_header.php';
include '../layout_sidebar.php';
?>