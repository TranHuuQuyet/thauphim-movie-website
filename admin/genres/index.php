<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$search = trim((string) ($_GET['q'] ?? ''));
$page = admin_page_number($_GET['page'] ?? null);
$perPage = 20;
$where = '';
$params = [];

if ($search !== '') {
    $where = ' WHERE genres.name LIKE ? OR genres.slug LIKE ?';
    $searchValue = '%' . $search . '%';
    $params = [$searchValue, $searchValue];
}

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM genres ' . $where);
$countStmt->execute($params);
$totalItems = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalItems / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare('
    SELECT genres.*,
           (SELECT COUNT(*) FROM movie_genres WHERE movie_genres.genre_id = genres.id) AS movie_count
    FROM genres
    ' . $where . '
    ORDER BY genres.id ASC
    LIMIT ' . $perPage . ' OFFSET ' . $offset . '
');
$stmt->execute($params);
$genres = $stmt->fetchAll();

$pageTitle = 'Admin Genres';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content admin-list-content">
    <div class="page-header">
        <h1>Genres</h1>
        <a class="btn-add" href="<?= admin_e(admin_url('genres/create.php')) ?>">
            <i class="fa-solid fa-plus"></i> Add genre
        </a>
    </div>

    <form class="admin-form admin-filter" method="get" action="<?= admin_e(admin_url('genres/index.php')) ?>">
        <div class="filter-fields">
            <div class="form-row filter-search">
                <label for="q">Search genres</label>
                <input id="q" type="search" name="q" value="<?= admin_e($search) ?>" placeholder="Genre name or slug">
            </div>
            <div class="form-actions">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                <a class="btn btn-secondary" href="<?= admin_e(admin_url('genres/index.php')) ?>">Reset</a>
            </div>
        </div>
    </form>

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
    <?php admin_render_pagination(
        'genres/index.php',
        ['q' => $search],
        $page,
        $totalPages,
        $totalItems,
        $offset,
        $perPage
    ); ?>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
