<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$search = trim((string) ($_GET['q'] ?? ''));
$page = admin_page_number($_GET['page'] ?? null);
$perPage = 20;
$where = '';
$params = [];

if ($search !== '') {
    $where = ' WHERE users.username LIKE ? OR users.email LIKE ? OR users.role LIKE ?
               OR users.membership LIKE ? OR users.status LIKE ?';
    $searchValue = '%' . $search . '%';
    $params = [$searchValue, $searchValue, $searchValue, $searchValue, $searchValue];
}

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM users ' . $where);
$countStmt->execute($params);
$totalItems = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalItems / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare('
    SELECT users.id, users.username, users.email, users.role, users.membership,
           users.status, users.last_login_at, users.created_at,
           (SELECT COUNT(*) FROM comments WHERE comments.user_id = users.id) AS comment_count,
           (SELECT COUNT(*) FROM ratings WHERE ratings.user_id = users.id) AS rating_count,
           (SELECT COUNT(*) FROM favorites WHERE favorites.user_id = users.id) AS favorite_count,
           (SELECT COUNT(*) FROM watch_history WHERE watch_history.user_id = users.id) AS history_count
    FROM users
    ' . $where . '
    ORDER BY users.id ASC
    LIMIT ' . $perPage . ' OFFSET ' . $offset . '
');
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'Admin Users';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content admin-list-content">
    <div class="page-header">
        <h1>Users</h1>
    </div>

    <form class="admin-form admin-filter" method="get" action="<?= admin_e(admin_url('users/index.php')) ?>">
        <div class="filter-fields">
            <div class="form-row filter-search">
                <label for="q">Search users</label>
                <input id="q" type="search" name="q" value="<?= admin_e($search) ?>" placeholder="Username, email, role or status">
            </div>
            <div class="form-actions">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                <a class="btn btn-secondary" href="<?= admin_e(admin_url('users/index.php')) ?>">Reset</a>
            </div>
        </div>
    </form>

    <section class="admin-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Membership</th>
                    <th>Status</th>
                    <th>Activity</th>
                    <th>Last login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= (int) $user['id'] ?></td>
                        <td>
                            <strong><?= admin_e($user['username']) ?></strong><br>
                            <span class="muted"><?= admin_e($user['email']) ?></span>
                        </td>
                        <td><?= admin_e($user['role']) ?></td>
                        <td>
                            <span class="badge <?= $user['membership'] === 'premium' ? 'badge-warning' : 'badge-muted' ?>">
                                <?= admin_e($user['membership']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $user['status'] === 'active' ? 'badge-success' : 'badge-muted' ?>">
                                <?= admin_e($user['status']) ?>
                            </span>
                        </td>
                        <td>
                            Comments: <?= (int) $user['comment_count'] ?><br>
                            Ratings: <?= (int) $user['rating_count'] ?><br>
                            Favorites: <?= (int) $user['favorite_count'] ?><br>
                            History: <?= (int) $user['history_count'] ?>
                        </td>
                        <td><?= admin_e($user['last_login_at'] ?? '') ?></td>
                        <td>
                            <div class="table-actions">
                                <form method="post" action="<?= admin_e(admin_url('users/update_membership.php')) ?>">
                                    <?= admin_csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                    <select name="membership">
                                        <option value="free" <?= $user['membership'] === 'free' ? 'selected' : '' ?>>Free</option>
                                        <option value="premium" <?= $user['membership'] === 'premium' ? 'selected' : '' ?>>Premium</option>
                                    </select>
                                    <button type="submit">Save membership</button>
                                </form>

                                <?php if ((int) $user['id'] !== (int) ($adminUser['id'] ?? 0)): ?>
                                    <form method="post" action="<?= admin_e(admin_url('users/update_status.php')) ?>">
                                        <?= admin_csrf_input() ?>
                                        <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                        <select name="status">
                                            <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="locked" <?= $user['status'] === 'locked' ? 'selected' : '' ?>>Locked</option>
                                        </select>
                                        <button type="submit">Save status</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted">Current admin</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="muted">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
    <?php admin_render_pagination(
        'users/index.php',
        ['q' => $search],
        $page,
        $totalPages,
        $totalItems,
        $offset,
        $perPage
    ); ?>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
