<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/thauphim-movie-website/assets/css/country.css">

<main class="country-page">
    <section class="country-movies" id="countryMovies" aria-labelledby="country-movies-title">
        <div class="country-section-heading">
            <h1 id="country-movies-title"></h1>
        </div>

        <div class="country-movie-status" id="countryMovieStatus" role="status" aria-live="polite"></div>
        <div class="country-movie-grid" id="countryMovieList"></div>
    </section>

    <noscript>
        <p class="country-noscript">Trang quốc gia cần JavaScript để tải danh sách phim.</p>
    </noscript>
</main>

<script>
const TMDB_API_KEY = <?= json_encode(TMDB_API_KEY, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const TMDB_COUNTRIES = <?= json_encode($TMDB_COUNTRIES ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="/thauphim-movie-website/assets/js/country.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
