<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$movieId = admin_nullable_int($_GET['movie_id'] ?? null);
$search = trim((string) ($_GET['q'] ?? ''));
$page = admin_page_number($_GET['page'] ?? null);
$perPage = 20;
$movies = $pdo->query('
    SELECT id, title
    FROM movies
    ORDER BY title ASC
')->fetchAll();

$where = [];
$params = [];

if ($movieId !== null && $movieId > 0) {
    $where[] = 'episodes.movie_id = ?';
    $params[] = $movieId;
}

if ($search !== '') {
    $where[] = '(movies.title LIKE ? OR episodes.title LIKE ?)';
    $searchValue = '%' . $search . '%';
    $params[] = $searchValue;
    $params[] = $searchValue;
}

$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare('
    SELECT COUNT(*)
    FROM episodes
    INNER JOIN movies ON movies.id = episodes.movie_id
    ' . $whereSql);
$countStmt->execute($params);
$totalItems = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalItems / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = '
    SELECT episodes.*, movies.title AS movie_title, movies.type AS movie_type
    FROM episodes
    INNER JOIN movies ON movies.id = episodes.movie_id
    ' . $whereSql . '
    ORDER BY episodes.id ASC
    LIMIT ' . $perPage . ' OFFSET ' . $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$episodes = $stmt->fetchAll();

$pageTitle = 'Admin Episodes';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content admin-list-content">
    <div class="page-header">
        <h1>Episodes</h1>
        <a href="<?= admin_e(admin_url('episodes/create.php' . ($movieId ? '?movie_id=' . $movieId : ''))) ?>" class="btn-add">
            <i class="fa-solid fa-plus"></i> Add episode
        </a>
    </div>

    <form class="admin-form admin-filter" method="get" action="<?= admin_e(admin_url('episodes/index.php')) ?>">
        <div class="filter-fields">
            <div class="form-row">
                <label for="movie_id">Filter by movie</label>
                <select id="movie_id" name="movie_id">
                    <option value="">All movies</option>
                    <?php foreach ($movies as $movie): ?>
                        <option value="<?= (int) $movie['id'] ?>" <?= $movieId === (int) $movie['id'] ? 'selected' : '' ?>>
                            <?= admin_e($movie['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row filter-search">
                <label for="q">Search episodes</label>
                <input id="q" type="search" name="q" value="<?= admin_e($search) ?>" placeholder="Movie or episode title">
            </div>
            <div class="form-actions">
                <button type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
                <a class="btn btn-secondary" href="<?= admin_e(admin_url('episodes/index.php')) ?>">Reset</a>
            </div>
        </div>
    </form>

    <section class="admin-table" style="margin-top: 18px;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Movie</th>
                    <th>No.</th>
                    <th>Title</th>
                    <th>YouTube URL</th>
                    <th>Published</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($episodes as $episode): ?>
                    <tr>
                        <td><?= (int) $episode['id'] ?></td>
                        <td><?= admin_e($episode['movie_title']) ?></td>
                        <td><?= (int) $episode['episode_number'] ?></td>
                        <td><?= admin_e($episode['title']) ?></td>
                        <td class="truncate-cell"><?= admin_e($episode['youtube_url'] ?? '') ?></td>
                        <td>
                            <span class="badge <?= !empty($episode['is_published']) ? 'badge-success' : 'badge-muted' ?>">
                                <?= !empty($episode['is_published']) ? 'Published' : 'Draft' ?>
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-secondary" href="<?= admin_e(admin_url('episodes/edit.php?id=' . (int) $episode['id'])) ?>">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <form class="inline-form" method="post" action="<?= admin_e(admin_url('episodes/delete.php')) ?>" onsubmit="return confirm('Delete this episode?');">
                                    <?= admin_csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= (int) $episode['id'] ?>">
                                    <button class="btn-danger" type="submit">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($episodes)): ?>
                    <tr>
                        <td colspan="7" class="muted">No episodes found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
    <?php admin_render_pagination(
        'episodes/index.php',
        ['movie_id' => $movieId, 'q' => $search],
        $page,
        $totalPages,
        $totalItems,
        $offset,
        $perPage
    ); ?>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
