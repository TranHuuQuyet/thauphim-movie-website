<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

$totalMovies = (int) $pdo->query('SELECT COUNT(*) FROM movies')->fetchColumn();
$totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalComments = (int) $pdo->query('SELECT COUNT(*) FROM comments')->fetchColumn();
$totalEpisodes = (int) $pdo->query('SELECT COUNT(*) FROM episodes')->fetchColumn();
$totalPublishedEpisodes = (int) $pdo->query('SELECT COUNT(*) FROM episodes WHERE is_published = 1')->fetchColumn();
$totalViews = (int) $pdo->query('SELECT COALESCE(SUM(views), 0) FROM movies')->fetchColumn();

$latestMovies = $pdo->query('
    SELECT id, title, release_year, type, views
    FROM movies
    ORDER BY id DESC
    LIMIT 5
')->fetchAll();

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/layout_header.php';
include __DIR__ . '/layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Dashboard</h1>
    </div>

    <div class="dashboard-cards">
        <div class="dashboard-card">
            <span>Total movies</span>
            <h2><?= $totalMovies ?></h2>
        </div>
        <div class="dashboard-card">
            <span>Users</span>
            <h2><?= $totalUsers ?></h2>
        </div>
        <div class="dashboard-card">
            <span>Comments</span>
            <h2><?= $totalComments ?></h2>
        </div>
        <div class="dashboard-card">
            <span>Views</span>
            <h2><?= number_format($totalViews) ?></h2>
        </div>
        <div class="dashboard-card">
            <span>Episodes</span>
            <h2><?= $totalEpisodes ?></h2>
        </div>
        <div class="dashboard-card">
            <span>Published episodes</span>
            <h2><?= $totalPublishedEpisodes ?></h2>
        </div>
    </div>

    <section class="admin-table">
        <h2>Latest movies</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Year</th>
                    <th>Type</th>
                    <th>Views</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($latestMovies as $movie): ?>
                    <tr>
                        <td><?= (int) $movie['id'] ?></td>
                        <td><?= admin_e($movie['title']) ?></td>
                        <td><?= admin_e($movie['release_year'] ?? '') ?></td>
                        <td><?= admin_e($movie['type']) ?></td>
                        <td><?= number_format((int) $movie['views']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include __DIR__ . '/layout_footer.php'; ?>
