<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$ratings = $pdo->query('
    SELECT ratings.*, users.username, movies.title AS movie_title
    FROM ratings
    INNER JOIN users ON users.id = ratings.user_id
    INNER JOIN movies ON movies.id = ratings.movie_id
    ORDER BY ratings.updated_at DESC
    LIMIT 200
')->fetchAll();

$pageTitle = 'Admin Ratings';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Ratings</h1>
    </div>

    <section class="admin-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Movie</th>
                    <th>Rating</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ratings as $rating): ?>
                    <tr>
                        <td><?= (int) $rating['id'] ?></td>
                        <td><?= admin_e($rating['username']) ?></td>
                        <td><?= admin_e($rating['movie_title']) ?></td>
                        <td><?= (int) $rating['rating'] ?> / 5</td>
                        <td><?= admin_e($rating['updated_at']) ?></td>
                        <td>
                            <form class="inline-form" method="post" action="<?= admin_e(admin_url('ratings/delete.php')) ?>" onsubmit="return confirm('Delete this rating?');">
                                <?= admin_csrf_input() ?>
                                <input type="hidden" name="id" value="<?= (int) $rating['id'] ?>">
                                <button class="btn-danger" type="submit">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($ratings)): ?>
                    <tr>
                        <td colspan="6" class="muted">No ratings found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
