<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="<?= htmlspecialchars(APP_BASE_PATH . 'assets/css/genres.css', ENT_QUOTES, 'UTF-8') ?>">

<main class="genres-page">
    <section class="genres-section" aria-labelledby="genres-title">
        <div class="genres-section-heading">
            <h1 id="genres-title">Thể loại phim</h1>
        </div>

        <div class="genres-status" id="genresStatus" role="status" aria-live="polite"></div>
        <div class="genres-grid" id="genresList"></div>
    </section>
</main>

<script>
window.APP_BASE_PATH = <?= json_encode(APP_BASE_PATH, JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= htmlspecialchars(APP_BASE_PATH . 'assets/js/genres.js', ENT_QUOTES, 'UTF-8') ?>"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>