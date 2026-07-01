<?php
declare(strict_types=1);
require_once __DIR__ . '/../_helpers.php';
function watch_error_field(string $content, string $label): string
{
    $labelPattern = preg_quote($label, '/');
    $pattern = '/(?:^|\s)' . $labelPattern . ':\s*(.*?)(?=\s+(?:Movie ID|Episode ID|Link|Description):|$)/su';
    if (preg_match($pattern, $content, $matches)) {
        return trim((string) $matches[1]);
    }
    return '';
}

function watch_error_description(string $content): string
{
    if (preg_match('/(?:^|\s)Description:\s*(.+)$/su', $content, $matches)) {
        return trim((string) $matches[1]);
    }

    $content = str_replace('[Fixed]', '', $content);
    $content = preg_replace('/^\[Error\]\s*/u', '', $content) ?? $content;

    return trim($content);
}

$status = (string) ($_GET['status'] ?? 'open');
$search = trim((string) ($_GET['q'] ?? ''));

if (!in_array($status, ['all', 'open', 'fixed'], true)) {
    $status = 'open';
}
$page = admin_page_number($_GET['page'] ?? 1);
$perPage = 12;
$where = [
    "comments.content LIKE '[Error]%'",
];

$params = [];
if ($status === 'open') {
    $where[] = "comments.content NOT LIKE '%[Fixed]%'";
}

if ($status === 'fixed') {
    $where[] = "comments.content LIKE '%[Fixed]%'";
}

if ($search !== '') {
    $where[] = "(users.username LIKE ? OR movies.title LIKE ? OR comments.content LIKE ?)";
    $searchValue = '%' . $search . '%';
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}

$whereSql = ' WHERE ' . implode(' AND ', $where);

$countStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM comments
    INNER JOIN users ON users.id = comments.user_id
    INNER JOIN movies ON movies.id = comments.movie_id
 $whereSql
");
$countStmt->execute($params);

$totalItems = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalItems / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = "
    SELECT
        comments.id,
        comments.content,
        comments.status AS comment_status,
        comments.created_at,
        users.username,
        movies.id AS movie_id,
        movies.title AS movie_title
    FROM comments
    INNER JOIN users ON users.id = comments.user_id
    INNER JOIN movies ON movies.id = comments.movie_id
    $whereSql
    ORDER BY comments.created_at DESC
    LIMIT $perPage OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$watchErrors = $stmt->fetchAll();

$success = isset($_GET['updated']) ? 'Watch error status updated successfully.' : '';

// SQL migration guidance for existing older Vietnamese markers:
// UPDATE comments
// SET content = REPLACE(content, '[Báo lỗi]', '[Error]')
// WHERE content LIKE '[Báo lỗi]%';
//
// UPDATE comments
// SET content = REPLACE(content, '[Đã fix]', '[Fixed]')
// WHERE content LIKE '%[Đã fix]%';

$pageTitle = 'Admin Watch Errors';

include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>

<main class="admin-content admin-list-content">

    <div class="page-header">
        <h1>Watch Errors</h1>
    </div>

    <?php admin_render_messages([], $success); ?>

    <form class="admin-form admin-filter" method="get">
        <div class="filter-fields">

            <div class="form-row">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="fixed" <?= $status === 'fixed' ? 'selected' : '' ?>>Fixed</option>
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                </select>
            </div>

            <div class="form-row filter-search">
                <label for="q">Search</label>
                <input id="q" name="q" type="search" value="<?= admin_e($search) ?>"
                    placeholder="Search by user, movie, or error content">
            </div>

            <div class="form-actions">
                <button type="submit">Filter</button>
                <a class="btn btn-secondary" href="<?= admin_url('watch-errors/watch-errors.php') ?>">Reset</a>
            </div>

        </div>
    </form>

    <section class="admin-table watch-error-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Movie</th>
                    <th>Episode</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Link</th>
                    <th>Created at</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($watchErrors)): ?>
                    <tr>
                        <td colspan="9" class="muted">No watch errors found.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($watchErrors as $error): ?>
                    <?php
                    $content = (string) $error['content'];
                    $isFixed = strpos($content, '[Fixed]') !== false;
                    $episodeId = watch_error_field($content, 'Episode ID');
                    $reportLink = watch_error_field($content, 'Link');
                    $description = watch_error_description($content);

                    $returnUrl = 'watch-errors/watch-errors.php?' . http_build_query([
                        'status' => $status,
                        'q' => $search,
                        'page' => $page,
                        'updated' => 1,
                    ]);
                    ?>

                    <tr>
                        <td><?= (int) $error['id'] ?></td>

                        <td><?= admin_e($error['username']) ?></td>

                        <td>
                            <?= admin_e($error['movie_title']) ?>
                            <div class="muted">Movie ID: <?= (int) $error['movie_id'] ?></div>
                        </td>

                        <td>
                            <?= $episodeId !== '' ? admin_e($episodeId) : '<span class="muted">N/A</span>' ?>
                        </td>

                        <td>
                            <?php if ($isFixed): ?>
                                <span class="badge badge-success">Fixed</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Open</span>
                            <?php endif; ?>

                            <div class="muted">
                                Comment: <?= admin_e($error['comment_status']) ?>
                            </div>
                        </td>

                        <td class="watch-error-description"><?= admin_e($description) ?></td>

                        <td class="watch-error-link">
                            <?php if ($reportLink !== ''): ?>
                                <a href="<?= admin_e($reportLink) ?>" target="_blank" rel="noopener">View</a>
                            <?php else: ?>
                                <span class="muted">N/A</span>
                            <?php endif; ?>
                        </td>

                        <td><?= admin_e($error['created_at']) ?></td>

                        <td>
                            <form class="inline-form" method="post"
                                action="<?= admin_url('watch-errors/update-errors.php') ?>">
                                <?= admin_csrf_input() ?>

                                <input type="hidden" name="id" value="<?= (int) $error['id'] ?>">
                                <input type="hidden" name="return" value="<?= admin_e($returnUrl) ?>">

                                <?php if ($isFixed): ?>
                                    <input type="hidden" name="action" value="unfix">
                                    <button class="btn btn-warning" type="submit">Mark as open</button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="fix">
                                    <button class="btn btn-info" type="submit">Mark as fixed</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <?php
    admin_render_pagination(
        'watch-errors/watch-errors.php',
        ['status' => $status, 'q' => $search],
        $page,
        $totalPages,
        $totalItems,
        $offset,
        $perPage
    );
    ?>

</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>