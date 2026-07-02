<?php
require_once __DIR__ . '/../includes/config.php';

$pageStyles = ['assets/css/movie-list.css'];
$pageScripts = [
    'assets/js/main.js',
    'assets/js/movie-list.js'
];

include __DIR__ . '/../includes/header.php';
?>

<main class="page-shell movie-list-page" aria-label="Danh sách phim lẻ">
    <div class="container movie-list-container">
        <section class="movie-list-header-section">
            <h2>Phim lẻ</h2>

            <div class="search-box">
                <input type="text" id="movieSearchInput" placeholder="Tìm tên phim lẻ..." autocomplete="off">
            </div>
        </section>

        <div class="movie-list-load-status" id="movieStatus" role="status" aria-live="polite"></div>

        <section class="movie-grid-section">
            <div class="movie-list-grid" id="movieList"></div>
        </section>

        <div class="pagination-container" id="moviePagination"></div>
    </div>
</main>

<script>
    window.MOVIE_LIST_CONFIG = {
        type: "movie",
        heading: "Phim lẻ",
        emptyText: "Không tìm thấy phim lẻ nào phù hợp."
    };
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>