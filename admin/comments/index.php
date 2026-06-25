<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$status = (string) ($_GET['status'] ?? '');
$allowedStatuses = ['visible', 'hidden'];

$sql = '
    SELECT comments.*, users.username, movies.title AS movie_title
    FROM comments
    INNER JOIN users ON users.id = comments.user_id
    INNER JOIN movies ON movies.id = comments.movie_id
';
$params = [];

if (in_array($status, $allowedStatuses, true)) {
    $sql .= ' WHERE comments.status = ?';
    $params[] = $status;
}

$sql .= ' ORDER BY comments.created_at DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$comments = $stmt->fetchAll();

$pageTitle = 'Admin Comments';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Comments</h1>
    </div>

    <form class="admin-form" method="get" action="<?= admin_e(admin_url('comments/index.php')) ?>">
        <div class="form-grid">
            <div class="form-row">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All</option>
                    <option value="visible" <?= $status === 'visible' ? 'selected' : '' ?>>Visible</option>
                    <option value="hidden" <?= $status === 'hidden' ? 'selected' : '' ?>>Hidden</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
            <a class="btn btn-secondary" href="<?= admin_e(admin_url('comments/index.php')) ?>">Reset</a>
        </div>
    </form>

    <section class="admin-table" style="margin-top: 18px;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Movie</th>
                    <th>Content</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td><?= (int) $comment['id'] ?></td>
                        <td><?= admin_e($comment['username']) ?></td>
                        <td><?= admin_e($comment['movie_title']) ?></td>
                        <td class="truncate-cell" title="<?= admin_e($comment['content']) ?>"><?= admin_e($comment['content']) ?></td>
                        <td>
                            <span class="badge <?= $comment['status'] === 'visible' ? 'badge-success' : 'badge-muted' ?>">
                                <?= admin_e($comment['status']) ?>
                            </span>
                        </td>
                        <td><?= admin_e($comment['created_at']) ?></td>
                        <td>
                            <div class="table-actions">
                                <form class="inline-form" method="post" action="<?= admin_e(admin_url('comments/hide.php')) ?>">
                                    <?= admin_csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                                    <input type="hidden" name="status" value="<?= $comment['status'] === 'visible' ? 'hidden' : 'visible' ?>">
                                    <button class="btn-warning" type="submit">
                                        <?= $comment['status'] === 'visible' ? 'Hide' : 'Show' ?>
                                    </button>
                                </form>
                                <form class="inline-form" method="post" action="<?= admin_e(admin_url('comments/delete.php')) ?>" onsubmit="return confirm('Delete this comment?');">
                                    <?= admin_csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                                    <button class="btn-danger" type="submit">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($comments)): ?>
                    <tr>
                        <td colspan="7" class="muted">No comments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
