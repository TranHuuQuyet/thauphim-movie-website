<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$genres = $pdo->query('
    SELECT genres.*,
           (SELECT COUNT(*) FROM movie_genres WHERE movie_genres.genre_id = genres.id) AS movie_count
    FROM genres
    ORDER BY genres.id DESC
')->fetchAll();

$pageTitle = 'Admin Genres';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Genres</h1>
        <a class="btn-add" href="<?= admin_e(admin_url('genres/create.php')) ?>">
            <i class="fa-solid fa-plus"></i> Add genre
        </a>
    </div>

    <section class="admin-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Movies</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($genres as $genre): ?>
                    <tr>
                        <td><?= (int) $genre['id'] ?></td>
                        <td><?= admin_e($genre['name']) ?></td>
                        <td><?= admin_e($genre['slug']) ?></td>
                        <td><?= (int) $genre['movie_count'] ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-secondary" href="<?= admin_e(admin_url('genres/edit.php?id=' . (int) $genre['id'])) ?>">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <form class="inline-form" method="post" action="<?= admin_e(admin_url('genres/delete.php')) ?>" onsubmit="return confirm('Delete this genre?');">
                                    <?= admin_csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= (int) $genre['id'] ?>">
                                    <button class="btn-danger" type="submit">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($genres)): ?>
                    <tr>
                        <td colspan="5" class="muted">No genres found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
