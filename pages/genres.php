<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/genres.css">

<main class="genres-page">
    <section class="genres-section" aria-labelledby="genres-title">
        <div class="genres-section-heading">
            <h1 id="genres-title">Thể loại phim</h1>
        </div>

        <div class="genres-status" id="genresStatus" role="status" aria-live="polite"></div>
        <div class="genres-grid" id="genresList"></div>
    </section>
</main>

<script src="/assets/js/genres.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>