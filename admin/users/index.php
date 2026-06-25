<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$users = $pdo->query('
    SELECT users.id, users.username, users.email, users.role, users.membership,
           users.status, users.last_login_at, users.created_at,
           (SELECT COUNT(*) FROM comments WHERE comments.user_id = users.id) AS comment_count,
           (SELECT COUNT(*) FROM ratings WHERE ratings.user_id = users.id) AS rating_count,
           (SELECT COUNT(*) FROM favorites WHERE favorites.user_id = users.id) AS favorite_count,
           (SELECT COUNT(*) FROM watch_history WHERE watch_history.user_id = users.id) AS history_count
    FROM users
    ORDER BY users.id DESC
')->fetchAll();

$pageTitle = 'Admin Users';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Users</h1>
    </div>

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
            </tbody>
        </table>
    </section>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
