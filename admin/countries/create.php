<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$errors = [];
$country = [
    'code' => '',
    'name' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();

    $country = [
        'code' => strtoupper(trim((string) ($_POST['code'] ?? ''))),
        'name' => trim((string) ($_POST['name'] ?? '')),
    ];

    if (!preg_match('/^[A-Z]{2}$/', $country['code'])) {
        $errors[] = 'Country code must be 2 letters, for example US or VN.';
    }

    if ($country['name'] === '') {
        $errors[] = 'Name is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO countries (code, name) VALUES (?, ?)');
            $stmt->execute([$country['code'], $country['name']]);
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

$pageTitle = 'Add Country';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Add country</h1>
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
