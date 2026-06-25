<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$actors = $pdo->query('
    SELECT actors.*,
           (SELECT COUNT(*) FROM movie_actors WHERE movie_actors.actor_id = actors.id) AS movie_count
    FROM actors
    ORDER BY actors.id DESC
')->fetchAll();

$pageTitle = 'Admin Actors';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Actors</h1>
        <a class="btn-add" href="<?= admin_e(admin_url('actors/create.php')) ?>">
            <i class="fa-solid fa-plus"></i> Add actor
        </a>
    </div>

    <section class="admin-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Movies</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($actors as $actor): ?>
                    <tr>
                        <td><?= (int) $actor['id'] ?></td>
                        <td><?= admin_e($actor['name']) ?></td>
                        <td><?= admin_e($actor['known_for_department'] ?? '') ?></td>
                        <td><?= (int) $actor['movie_count'] ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-secondary" href="<?= admin_e(admin_url('actors/edit.php?id=' . (int) $actor['id'])) ?>">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <form class="inline-form" method="post" action="<?= admin_e(admin_url('actors/delete.php')) ?>" onsubmit="return confirm('Delete this actor?');">
                                    <?= admin_csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= (int) $actor['id'] ?>">
                                    <button class="btn-danger" type="submit">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($actors)): ?>
                    <tr>
                        <td colspan="5" class="muted">No actors found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
