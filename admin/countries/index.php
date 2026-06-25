<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$countries = $pdo->query('
    SELECT countries.*,
           (SELECT COUNT(*) FROM movies WHERE movies.country_id = countries.id) AS movie_count
    FROM countries
    ORDER BY countries.name ASC
')->fetchAll();

$pageTitle = 'Admin Countries';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Countries</h1>
        <a class="btn-add" href="<?= admin_e(admin_url('countries/create.php')) ?>">
            <i class="fa-solid fa-plus"></i> Add country
        </a>
    </div>

    <section class="admin-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Movies</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($countries as $country): ?>
                    <tr>
                        <td><?= (int) $country['id'] ?></td>
                        <td><?= admin_e($country['code']) ?></td>
                        <td><?= admin_e($country['name']) ?></td>
                        <td><?= (int) $country['movie_count'] ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-secondary" href="<?= admin_e(admin_url('countries/edit.php?id=' . (int) $country['id'])) ?>">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <form class="inline-form" method="post" action="<?= admin_e(admin_url('countries/delete.php')) ?>" onsubmit="return confirm('Delete this country? Movies will keep working without a country.');">
                                    <?= admin_csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= (int) $country['id'] ?>">
                                    <button class="btn-danger" type="submit">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($countries)): ?>
                    <tr>
                        <td colspan="5" class="muted">No countries found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
