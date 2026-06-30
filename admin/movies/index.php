<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$movies = $pdo->query('
    SELECT movies.id, movies.title, movies.release_year, movies.type, movies.status,
           movies.is_premium, movies.views, countries.name AS country_name
    FROM movies
    LEFT JOIN countries ON countries.id = movies.country_id
    ORDER BY movies.id DESC
')->fetchAll();

$pageTitle = 'Admin Movies';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Movies</h1>
        <a href="<?= admin_e(admin_url('movies/create.php')) ?>" class="btn-add">
            <i class="fa-solid fa-plus"></i> Add movie
        </a>
    </div>

    <section class="admin-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Year</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Premium</th>
                    <th>Country</th>
                    <th>Views</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movies as $movie): ?>
                    <tr>
                        <td><?= (int) $movie['id'] ?></td>
                        <td><?= admin_e($movie['title']) ?></td>
                        <td><?= admin_e($movie['release_year'] ?? '') ?></td>
                        <td><?= admin_e($movie['type']) ?></td>
                        <td><?= admin_e($movie['status']) ?></td>
                        <td>
                            <span class="badge <?= !empty($movie['is_premium']) ? 'badge-warning' : 'badge-muted' ?>">
                                <?= !empty($movie['is_premium']) ? 'Premium' : 'Free' ?>
                            </span>
                        </td>
                        <td><?= admin_e($movie['country_name'] ?? '') ?></td>
                        <td><?= number_format((int) $movie['views']) ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-secondary" href="<?= admin_e(admin_url('movies/edit.php?id=' . (int) $movie['id'])) ?>">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <a class="btn btn-info" href="<?= admin_e(admin_url('schedules/index.php?movie_id=' . (int) $movie['id'])) ?>" title="Quản lý lịch chiếu">
                                    <i class="fa-solid fa-calendar"></i> Lịch chiếu
                                </a>
                                <form class="inline-form" method="post" action="<?= admin_e(admin_url('movies/delete.php')) ?>" onsubmit="return confirm('Delete this movie?');">
                                    <?= admin_csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= (int) $movie['id'] ?>">
                                    <button class="btn-danger" type="submit">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
