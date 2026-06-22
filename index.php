<?php session_start();?>

<?php include __DIR__ . '/includes/header.php'; ?>
<?php include 'includes/auth_modal.php'; ?>
<main class="page-shell" aria-label="Nội dung chính">
    <section class="hero">
        <div class="video-bg">
            <iframe
                src="https://www.youtube.com/embed/62bIsvRcPv0?autoplay=1&mute=1&loop=1&playlist=62bIsvRcPv0&controls=0&rel=0"
                allow="autoplay; encrypted-media" allowfullscreen>
            </iframe>
        </div>

        <div class="hero-overlay"></div>

        <div class="hero-content">
            <h1 class="hero-title" data-title="Spider Man" aria-label="Spider Man">Spider Man</h1>
            <p class="movie-name">Brand-new-day</p>


            <div class="movie-info">
                <span>31/7</span>
                <span>2026</span>
                <span>New trailer(4k)</span>

            </div>

            <div class="movie-tags">
                <span>#SpiderMan</span>
                <span>#trailer</span>
                <span>#marvel</span>
                <span>#Sony</span>
            </div>

            <p class="movie-desc">
                Watch the new trailer for #SpiderManBrandNewDay, in theatres July 31. Tickets on sale NOW.
            </p>

            <button class="play-btn">XEM NGAY
            </button>
        </div>
    </section>
    <!-- phim noi bat -->
    <section class="movie-section">
        <div class="section-head">
            <h2>Phim bộ nổi bật</h2>
            <a href="#" class="view-more">Xem thêm</a>
        </div>

        <div class="swiper movieSwiper">
            <div class="swiper-wrapper" id="trendingMovie">
            </div>

            <div class="swiper-button-prev movie-prev"></div>
            <div class="swiper-button-next movie-next"></div>
        </div>
    </section>

    <!-- top phim -->


    <section class="movie-top">
        <div class="section-head">
            <h2>Phim top của tuần</h2>
            <a href="#" class="view-more">Xem thêm</a>
        </div>
        <div class="swiper bannerSwiper">
            <div class="swiper-wrapper" id="topMovies">

            </div>

            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-pagination"></div>
        </div>
    </section>
    <!-- phimm moi -->
    <section class="movie-slides">
        <div class="movie-slides-head">
            <h2>Phim mới gần đây</h2>
            <a href="#" class="view-more">Xem thêm</a>
        </div>

        <div class="swiper movieHeroSwiper">
            <div class="swiper-wrapper" id="heroMovies">
            </div>
        </div>

        <div class="swiper-button-prev hero-prev"></div>
        <div class="swiper-button-next hero-next"></div>
        </div>

        <div class="swiper movieThumbSwiper">
            <div class="swiper-wrapper" id="thumbMovies">

            </div>
        </div>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/auth_modal.js"></script>


<?php include __DIR__ . '/includes/footer.php'; ?>
