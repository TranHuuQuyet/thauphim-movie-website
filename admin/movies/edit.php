<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$id = admin_get_id($_GET);
if ($id <= 0) {
    http_response_code(404);
    exit('Movie not found.');
}

$stmt = $pdo->prepare('
    SELECT *
    FROM movies
    WHERE id = ?
    LIMIT 1
');
$stmt->execute([$id]);
$movie = $stmt->fetch();

if (!$movie) {
    http_response_code(404);
    exit('Movie not found.');
}

$errors = [];
$countries = $pdo->query('
    SELECT id, name
    FROM countries
    ORDER BY name ASC
')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();

    $movie = array_merge($movie, [
        'title' => trim((string) ($_POST['title'] ?? '')),
        'original_title' => trim((string) ($_POST['original_title'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
        'release_date' => trim((string) ($_POST['release_date'] ?? '')),
        'release_year' => trim((string) ($_POST['release_year'] ?? '')),
        'runtime' => trim((string) ($_POST['runtime'] ?? '')),
        'type' => (string) ($_POST['type'] ?? 'movie'),
        'quality' => trim((string) ($_POST['quality'] ?? 'HD')),
        'country_id' => trim((string) ($_POST['country_id'] ?? '')),
        'status' => (string) ($_POST['status'] ?? 'completed'),
        'poster' => trim((string) ($_POST['poster'] ?? '')),
        'backdrop' => trim((string) ($_POST['backdrop'] ?? '')),
        'is_premium' => isset($_POST['is_premium']) ? 1 : 0,
    ]);

    if ($movie['title'] === '') {
        $errors[] = 'Title is required.';
    }

    if (!in_array($movie['type'], ['movie', 'series'], true)) {
        $errors[] = 'Movie type is invalid.';
    }

    if (!in_array($movie['status'], ['coming_soon', 'ongoing', 'completed'], true)) {
        $errors[] = 'Status is invalid.';
    }

    $releaseYear = admin_nullable_int($movie['release_year']);
    if ($movie['release_year'] !== '' && ($releaseYear === null || $releaseYear < 1888 || $releaseYear > ((int) date('Y') + 5))) {
        $errors[] = 'Release year is invalid.';
    }

    $runtime = admin_nullable_int($movie['runtime']);
    if ($movie['runtime'] !== '' && ($runtime === null || $runtime < 0)) {
        $errors[] = 'Runtime is invalid.';
    }

    $countryId = admin_nullable_int($movie['country_id']);
    $releaseDate = $movie['release_date'] !== '' ? $movie['release_date'] : null;

    if ($releaseDate !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $releaseDate)) {
        $errors[] = 'Release date must use YYYY-MM-DD.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('
            UPDATE movies
            SET title = ?, original_title = ?, description = ?, release_date = ?,
                release_year = ?, runtime = ?, type = ?, quality = ?, country_id = ?,
                status = ?, is_premium = ?, poster = ?, backdrop = ?
            WHERE id = ?
        ');
        $stmt->execute([
            $movie['title'],
            $movie['original_title'] !== '' ? $movie['original_title'] : null,
            $movie['description'] !== '' ? $movie['description'] : null,
            $releaseDate,
            $releaseYear,
            $runtime,
            $movie['type'],
            $movie['quality'] !== '' ? $movie['quality'] : 'HD',
            $countryId,
            $movie['status'],
            (int) $movie['is_premium'],
            $movie['poster'] !== '' ? $movie['poster'] : null,
            $movie['backdrop'] !== '' ? $movie['backdrop'] : null,
            $id,
        ]);

        admin_redirect('movies/index.php');
    }
}

$pageTitle = 'Edit Movie';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Edit movie</h1>
        <a class="btn btn-secondary" href="<?= admin_e(admin_url('movies/index.php')) ?>">Back</a>
    </div>

    <?php admin_render_messages($errors); ?>

    <form class="admin-form" method="post">
        <?= admin_csrf_input() ?>
        <?php include __DIR__ . '/form.php'; ?>
    </form>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
