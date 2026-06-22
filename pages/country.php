<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/thauphim-movie-website/assets/css/country.css">

<main class="country-page">
    <section class="country-hero" aria-labelledby="country-page-title">
        <div class="country-hero__content">
            <p class="country-eyebrow">Khám phá điện ảnh thế giới</p>
            <h1 id="country-page-title">Phim theo quốc gia</h1>
            <p class="country-hero__description">
                Chọn một quốc gia để xem những bộ phim đang được quan tâm tại khu vực đó.
            </p>
        </div>

        <form class="country-search" id="countrySearchForm" role="search">
            <label for="countrySearch">Tìm quốc gia</label>
            <div class="country-search__field">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input id="countrySearch" type="search" placeholder="Ví dụ: Hàn Quốc" autocomplete="off">
                <button class="country-search__clear" id="clearCountrySearch" type="button"
                    aria-label="Xóa nội dung tìm kiếm">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
        </form>
    </section>

    <section class="country-browser" aria-labelledby="country-list-title">
        <div class="country-section-heading">
            <div>
                <h2 id="country-list-title">Danh sách quốc gia</h2>
                <p>Những nền điện ảnh quen thuộc với khán giả Việt Nam.</p>
            </div>
            <span class="country-result-count" id="countryResultCount" aria-live="polite"></span>
        </div>

        <div class="country-grid" id="countryList" role="list"></div>

        <div class="country-empty" id="countryEmpty" hidden>
            <i class="fa-solid fa-earth-asia" aria-hidden="true"></i>
            <h3>Không tìm thấy quốc gia</h3>
            <p>Hãy thử một tên quốc gia khác.</p>
        </div>
    </section>

    <section class="country-movies" id="countryMovies" aria-labelledby="country-movies-title">
        <div class="country-section-heading country-section-heading--movies">
            <div>
                <h2 id="country-movies-title">Phim nổi bật</h2>
                <p id="countryMoviesDescription">Chọn một quốc gia để bắt đầu khám phá.</p>
            </div>
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
</script>
<script src="/thauphim-movie-website/assets/js/country.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>