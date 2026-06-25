<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$id = admin_get_id($_GET);
if ($id <= 0) {
    http_response_code(404);
    exit('Actor not found.');
}

$stmt = $pdo->prepare('SELECT * FROM actors WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$actor = $stmt->fetch();

if (!$actor) {
    http_response_code(404);
    exit('Actor not found.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();

    $actor = array_merge($actor, [
        'name' => trim((string) ($_POST['name'] ?? '')),
        'avatar' => trim((string) ($_POST['avatar'] ?? '')),
        'profile_path' => trim((string) ($_POST['profile_path'] ?? '')),
        'known_for_department' => trim((string) ($_POST['known_for_department'] ?? '')),
        'biography' => trim((string) ($_POST['biography'] ?? '')),
    ]);

    if ($actor['name'] === '') {
        $errors[] = 'Name is required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('
            UPDATE actors
            SET name = ?, avatar = ?, profile_path = ?, known_for_department = ?, biography = ?
            WHERE id = ?
        ');
        $stmt->execute([
            $actor['name'],
            $actor['avatar'] !== '' ? $actor['avatar'] : null,
            $actor['profile_path'] !== '' ? $actor['profile_path'] : null,
            $actor['known_for_department'] !== '' ? $actor['known_for_department'] : null,
            $actor['biography'] !== '' ? $actor['biography'] : null,
            $id,
        ]);

        admin_redirect('actors/index.php');
    }
}

$pageTitle = 'Edit Actor';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Edit actor</h1>
        <a class="btn btn-secondary" href="<?= admin_e(admin_url('actors/index.php')) ?>">Back</a>
    </div>

    <?php admin_render_messages($errors); ?>

    <form class="admin-form" method="post">
        <?= admin_csrf_input() ?>
        <?php include __DIR__ . '/form.php'; ?>
    </form>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
