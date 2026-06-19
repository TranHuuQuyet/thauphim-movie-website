<?php include __DIR__ . '/includes/header.php'; ?>
<?php include 'includes/auth_modal.php'; ?>

<main class="page-shell" aria-label="Nội dung chính">
    <section class="hero">
        <div class="video-bg">
            <iframe
                src="https://www.youtube.com/embed/SlQR9iu09bQ?autoplay=1&mute=1&loop=1&playlist=SlQR9iu09bQ&controls=0&rel=0"
                allow="autoplay; encrypted-media" allowfullscreen>
            </iframe>
        </div>

        <div class="hero-overlay"></div>

        <div class="hero-content">
            <h1>Come My Way</h1>
            <p class="movie-name">SONTUNGMPT & TYGA</p>


            <div class="movie-info">
                <span>28/5</span>
                <span>2026</span>
                <span>Music Video</span>

            </div>

            <div class="movie-tags">
                <span>#CMW</span>
                <span>#commyway</span>
                <span>#sontungmtp</span>
                <span>#tyga</span>
            </div>

            <p class="movie-desc">
                ▶ CLICK TO SUBSCRIBE: https://mmusicrecords.lnk.to/sontungmtp
                #sontungmtp #sontung #mtp #mtpentertainment
            </p>

            <button class="play-btn">XEM NGAY
            </button>
        </div>
    </section>
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