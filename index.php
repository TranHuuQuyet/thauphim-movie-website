<?php
session_start();
require_once __DIR__ . "/includes/config.php";
?>
<?php include __DIR__ . "/includes/header.php"; ?>
<link rel="stylesheet" href="/thauphim-movie-website/assets/css/home.css">

<main class="page-shell" id="home" aria-label="Nội dung chính">
    <section class="hero" id="hero">
        <div class="video-bg">
            <iframe
                src="https://www.youtube.com/embed/62bIsvRcPv0?autoplay=1&mute=1&loop=1&playlist=62bIsvRcPv0&controls=0&rel=0"
                allow="autoplay; encrypted-media" allowfullscreen>
            </iframe>
        </div>

        <div class="hero-overlay"></div>

        <div class="hero-content">
            <h1 class="hero-title" data-title="Spider Man" aria-label="Spider Man">Spider Man</h1>
            <p class="movie-name">Brand New Day</p>

            <div class="movie-info">
                <span>31/7</span>
                <span>2026</span>
                <span>Trailer 4K</span>
            </div>

            <div class="movie-tags">
                <span>#SpiderMan</span>
                <span>#trailer</span>
                <span>#marvel</span>
                <span>#Sony</span>
            </div>

            <p class="movie-desc">
                Watch the new trailer for #SpiderManBrandNewDay, in theatres July 31. Tickets on sale now.
            </p>

            <a class="play-btn" href="#featured">XEM NGAY</a>
        </div>
    </section>

    <section class="movie-section" id="featured" aria-labelledby="featured-title">
        <div class="section-head">
            <h2 id="featured-title">Phim nổi bật</h2>
            <a href="#featured" class="view-more">Xem thêm</a>
        </div>

        <div class="swiper movieSwiper">
            <div class="swiper-wrapper" id="trendingMovie"></div>

            <div class="swiper-button-prev movie-prev"></div>
            <div class="swiper-button-next movie-next"></div>
        </div>
    </section>

    <section class="movie-top" id="top-week" aria-labelledby="top-week-title">
        <div class="section-head">
            <h2 id="top-week-title">Phim top của tuần</h2>
            <a href="/thauphim-movie-website/pages/browse.php?sort=most_viewed" class="view-more">Xem thêm</a>
        </div>

        <div class="swiper bannerSwiper">
            <div class="swiper-wrapper" id="topMovies"></div>

            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-pagination"></div>
        </div>
    </section>

    <section class="movie-slides" id="new-movies" aria-labelledby="new-movies-title">
        <div class="movie-slides-head">
            <h2 id="new-movies-title">Phim mới gần đây</h2>
            <a href="/thauphim-movie-website/pages/browse.php?sort=newest" class="view-more">Xem thêm</a>
        </div>

        <div class="swiper movieHeroSwiper">
            <div class="swiper-wrapper" id="heroMovies"></div>
        </div>

        <div class="swiper-button-prev hero-prev"></div>
        <div class="swiper-button-next hero-next"></div>

        <div class="swiper movieThumbSwiper">
            <div class="swiper-wrapper" id="thumbMovies"></div>
        </div>
    </section>

    <section class="movie-section movie-grid-section" id="single-movies" aria-labelledby="single-movies-title">
        <div class="section-head">
            <h2 id="single-movies-title">Danh sách phim lẻ</h2>
            <a href="/thauphim-movie-website/pages/browse.php?sort=most_viewed" class="view-more">Xem thêm</a>
        </div>

        <div class="movie-grid" id="singleMovies" aria-live="polite"></div>
    </section>

    <section class="movie-section movie-grid-section" id="series-movies" aria-labelledby="series-movies-title">
        <div class="section-head">
            <h2 id="series-movies-title">Danh sách phim bộ</h2>
            <a href="/thauphim-movie-website/pages/browse.php?sort=most_viewed" class="view-more">Xem thêm</a>
        </div>

        <div class="movie-grid" id="seriesMovies" aria-live="polite"></div>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>
<script>
const TMDB_API_KEY = "<?= TMDB_API_KEY ?>";
</script>
<script src="assets/js/main.js"></script>

<?php include __DIR__ . "/includes/footer.php"; ?>
