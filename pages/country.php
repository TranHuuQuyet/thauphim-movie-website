<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/country.css">

<main class="country-page">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 40px 15px;">
        
        <section class="country-movies" id="countryMovies" aria-labelledby="country-movies-title">
            <div class="country-section-heading" style="margin-bottom: 30px;">
                <h1 id="country-movies-title" style="margin: 0; font-size: 26px; color: #fff;">Phim Theo Quốc Gia</h1>
            </div>

            <div class="country-movie-status" id="countryMovieStatus" role="status" aria-live="polite" style="text-align: center; color: #aaa; margin: 20px 0;"></div>
            
            <div class="country-movie-grid" id="countryMovieList"></div>
        </section>

        <nav class="pagination-container" id="countryPagination" aria-label="Phân trang quốc gia" 
             style="margin-top: 40px; display: flex; justify-content: center;">
        </nav>

    </div>
    
    <noscript>
        <p class="country-noscript" style="text-align: center; color: red; padding: 20px;">Trang quốc gia để tải danh sách phim.</p>
    </noscript>
</main>

<script src="/assets/js/country.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
