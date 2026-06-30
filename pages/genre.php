<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/thauphim-movie-website/assets/css/genre.css">

<main class="genre-page">
    <section class="genre-movies" id="genreMovies" aria-labelledby="genre-movies-title">
        <div class="genre-section-heading">
            <h1 id="genre-movies-title">Thể loại phim</h1>
        </div>

        <div class="genre-movie-status" id="genreMovieStatus" role="status" aria-live="polite"></div>
        <div class="genre-movie-grid" id="genreMovieList"></div>
    </section>

    <noscript>
        <p class="genre-noscript">Trang thể loại cần để tải danh sách phim.</p>
    </noscript>
</main>

<script src="/thauphim-movie-website/assets/js/genre.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>