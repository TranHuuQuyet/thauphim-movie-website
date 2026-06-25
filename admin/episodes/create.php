<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

$errors = [];
$prefillMovieId = admin_nullable_int($_GET['movie_id'] ?? null);
$episode = [
    'movie_id' => $prefillMovieId ?? '',
    'episode_number' => 1,
    'title' => '',
    'youtube_url' => '',
    'duration_seconds' => '',
    'is_published' => 0,
];

$movies = $pdo->query('
    SELECT id, title, type
    FROM movies
    ORDER BY title ASC
')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();

    $episode = [
        'movie_id' => trim((string) ($_POST['movie_id'] ?? '')),
        'episode_number' => trim((string) ($_POST['episode_number'] ?? '')),
        'title' => trim((string) ($_POST['title'] ?? '')),
        'youtube_url' => trim((string) ($_POST['youtube_url'] ?? '')),
        'duration_seconds' => trim((string) ($_POST['duration_seconds'] ?? '')),
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
    ];

    $movieId = admin_nullable_int($episode['movie_id']);
    $episodeNumber = admin_nullable_int($episode['episode_number']);
    $durationSeconds = admin_nullable_int($episode['duration_seconds']);
    $youtubeUrl = admin_normalize_youtube_url($episode['youtube_url']);

    if ($movieId === null || $movieId <= 0) {
        $errors[] = 'Movie is required.';
    }

    if ($episodeNumber === null || $episodeNumber <= 0) {
        $errors[] = 'Episode number must be greater than 0.';
    }

    if ($episode['title'] === '') {
        $errors[] = 'Episode title is required.';
    }

    if ($episode['duration_seconds'] !== '' && ($durationSeconds === null || $durationSeconds < 0)) {
        $errors[] = 'Duration is invalid.';
    }

    if ($episode['youtube_url'] !== '' && $youtubeUrl === '') {
        $errors[] = 'YouTube URL is invalid.';
    }

    if (!empty($episode['is_published']) && $youtubeUrl === '') {
        $errors[] = 'Published episodes require a valid YouTube URL.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('
                INSERT INTO episodes (movie_id, episode_number, title, youtube_url, duration_seconds, is_published)
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $movieId,
                $episodeNumber,
                $episode['title'],
                $youtubeUrl !== '' ? $youtubeUrl : null,
                $durationSeconds,
                (int) $episode['is_published'],
            ]);

            admin_redirect('episodes/index.php?movie_id=' . $movieId);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $errors[] = 'This movie already has an episode with that number.';
            } else {
                throw $exception;
            }
        }
    }
}

$pageTitle = 'Add Episode';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Add episode</h1>
        <a class="btn btn-secondary" href="<?= admin_e(admin_url('episodes/index.php')) ?>">Back</a>
    </div>

    <?php admin_render_messages($errors); ?>

    <form class="admin-form" method="post">
        <?= admin_csrf_input() ?>
        <?php include __DIR__ . '/form.php'; ?>
    </form>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
