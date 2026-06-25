<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$id = admin_get_id($_GET);
if ($id <= 0) {
    http_response_code(404);
    exit('Country not found.');
}

$stmt = $pdo->prepare('SELECT * FROM countries WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$country = $stmt->fetch();

if (!$country) {
    http_response_code(404);
    exit('Country not found.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();

    $country = array_merge($country, [
        'code' => strtoupper(trim((string) ($_POST['code'] ?? ''))),
        'name' => trim((string) ($_POST['name'] ?? '')),
    ]);

    if (!preg_match('/^[A-Z]{2}$/', $country['code'])) {
        $errors[] = 'Country code must be 2 letters, for example US or VN.';
    }

    if ($country['name'] === '') {
        $errors[] = 'Name is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('UPDATE countries SET code = ?, name = ? WHERE id = ?');
            $stmt->execute([$country['code'], $country['name'], $id]);
            admin_redirect('countries/index.php');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $errors[] = 'Country code already exists.';
            } else {
                throw $exception;
            }
        }
    }
}

$pageTitle = 'Edit Country';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Edit country</h1>
        <a class="btn btn-secondary" href="<?= admin_e(admin_url('countries/index.php')) ?>">Back</a>
    </div>

    <?php admin_render_messages($errors); ?>

    <form class="admin-form" method="post">
        <?= admin_csrf_input() ?>
        <div class="form-grid">
            <div class="form-row">
                <label for="code">Code</label>
                <input id="code" name="code" type="text" maxlength="2" value="<?= admin_e($country['code']) ?>" required>
            </div>
            <div class="form-row">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="<?= admin_e($country['name']) ?>" required>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
            <a class="btn btn-secondary" href="<?= admin_e(admin_url('countries/index.php')) ?>">Cancel</a>
        </div>
    </form>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
