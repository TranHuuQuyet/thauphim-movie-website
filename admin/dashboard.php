<?php

require_once '../includes/config.php';
require_once '../includes/db.php';

$pdo = getDatabaseConnection();

$totalMovies = $pdo->query("
    SELECT COUNT(*) 
    FROM movies
")->fetchColumn();

$totalUsers = $pdo->query("
    SELECT COUNT(*) 
    FROM users
")->fetchColumn();

$totalComments = $pdo->query("
    SELECT COUNT(*) 
    FROM comments
")->fetchColumn();

$totalViews = $pdo->query("
    SELECT COALESCE(SUM(views),0)
    FROM movies
")->fetchColumn();

$latestMovies = $pdo->query("
    SELECT *
    FROM movies
    ORDER BY id DESC
    LIMIT 5
")->fetchAll();

include 'layout_header.php';
include 'layout_sidebar.php';
?>
<div class="admin-content">

    <h1 class="page-title">
        Dashboard
    </h1>

    <div class="dashboard-cards">

        <div class="dashboard-card">
            <span>Tổng phim</span>
            <h2><?= $totalMovies ?></h2>
        </div>

        <div class="dashboard-card">
            <span>Người dùng</span>
            <h2><?= $totalUsers ?></h2>
        </div>

        <div class="dashboard-card">
            <span>Bình luận</span>
            <h2><?= $totalComments ?></h2>
        </div>

        <div class="dashboard-card">
            <span>Lượt xem</span>
            <h2><?= number_format($totalViews) ?></h2>
        </div>

    </div>

    <div class="admin-table">

        <h2>Phim mới thêm</h2>

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên phim</th>
                    <th>Năm</th>
                    <th>Loại</th>
                    <th>Lượt xem</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach($latestMovies as $movie): ?>

                <tr>
                    <td><?= $movie['id'] ?></td>
                    <td><?= htmlspecialchars($movie['title']) ?></td>
                    <td><?= $movie['release_year'] ?></td>
                    <td><?= $movie['type'] ?></td>
                    <td><?= number_format($movie['views']) ?></td>
                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<?php include 'layout_footer.php'; ?>