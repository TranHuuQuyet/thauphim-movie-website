<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$id = admin_get_id($_GET);
if ($id <= 0) {
    http_response_code(404);
    exit('Genre not found.');
}

$stmt = $pdo->prepare('SELECT * FROM genres WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$genre = $stmt->fetch();

if (!$genre) {
    http_response_code(404);
    exit('Genre not found.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();

    $genre = array_merge($genre, [
        'name' => trim((string) ($_POST['name'] ?? '')),
        'slug' => trim((string) ($_POST['slug'] ?? '')),
    ]);

    if ($genre['name'] === '') {
        $errors[] = 'Name is required.';
    }

    if ($genre['slug'] === '') {
        $genre['slug'] = admin_slugify($genre['name']);
    } else {
        $genre['slug'] = admin_slugify($genre['slug']);
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('
                UPDATE genres
                SET name = ?, slug = ?
                WHERE id = ?
            ');
            $stmt->execute([$genre['name'], $genre['slug'], $id]);
            admin_redirect('genres/index.php');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $errors[] = 'Genre name or slug already exists.';
            } else {
                throw $exception;
            }
        }
    }
}

$pageTitle = 'Edit Genre';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Edit genre</h1>
        <a class="btn btn-secondary" href="<?= admin_e(admin_url('genres/index.php')) ?>">Back</a>
    </div>

    <?php admin_render_messages($errors); ?>

    <form class="admin-form" method="post">
        <?= admin_csrf_input() ?>
        <div class="form-grid">
            <div class="form-row">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="<?= admin_e($genre['name']) ?>" required>
            </div>
            <div class="form-row">
                <label for="slug">Slug</label>
                <input id="slug" name="slug" type="text" value="<?= admin_e($genre['slug']) ?>">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
            <a class="btn btn-secondary" href="<?= admin_e(admin_url('genres/index.php')) ?>">Cancel</a>
        </div>
    </form>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
