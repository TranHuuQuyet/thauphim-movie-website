<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/genre.css">

<main class="genre-page" aria-label="Danh sách phim theo chủ đề">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 40px 15px;">
        
        <section class="genre-movies" id="genreMovies">
            <div class="genre-section-heading" style="margin-bottom: 30px; display: flex; align-items: center; gap: 15px;">
                <div style="width: 5px; height: 30px; background: #e50914; border-radius: 4px;"></div>
                <h1 id="genre-movies-title" style="margin: 0; font-size: 26px; color: #fff;">Chủ đề Phim</h1>
            </div>

            <div class="genre-movie-status" id="genreMovieStatus" role="status" aria-live="polite" style="text-align: center; color: #aaa; margin: 20px 0;">
                Đang tải dữ liệu...
            </div>
            
            <div class="genre-movie-grid" id="genreMovieList"></div>
        </section>

        <nav class="pagination-container" id="genrePagination" aria-label="Phân trang chủ đề" 
             style="margin-top: 40px; display: flex; justify-content: center;">
        </nav>

    </div>
    
    <noscript>
        <p style="text-align: center; color: red; padding: 20px;">Trang này cần JavaScript để hiển thị phim.</p>
    </noscript>
</main>

<script src="/assets/js/genre.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>