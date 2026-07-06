<?php
require_once __DIR__ . '/../includes/functions.php';

$genres = getAllGenres();
$countries = getAllCountriesFromDB();
$pageStyles = ['assets/css/browse.css'];
$pageScripts = ['assets/js/browse.js'];

include __DIR__ . '/../includes/header.php';
?>

<main class="page-shell browse-page" aria-label="Bộ lọc và tìm kiếm phim">
    <div class="browse-container">
        <section class="filter-section" aria-label="Lọc phim">
            <form
                id="filterForm"
                class="filter-form"
                data-api-url="<?= htmlspecialchars(app_url('api/movies.php'), ENT_QUOTES, 'UTF-8') ?>"
                data-detail-base="<?= htmlspecialchars(app_url('pages/movie-detail.php'), ENT_QUOTES, 'UTF-8') ?>"
                data-fallback-poster="<?= htmlspecialchars(app_url('assets/images/poster_movie.jpg'), ENT_QUOTES, 'UTF-8') ?>"
            >
                <div class="filter-group">
                    <label for="filterType">Định dạng</label>
                    <select id="filterType" name="type">
                        <option value="">Tất cả định dạng</option>
                        <option value="movie">Phim lẻ</option>
                        <option value="series">Phim bộ</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filterGenre">Thể loại</label>
                    <select id="filterGenre" name="genre">
                        <option value="">Tất cả thể loại</option>
                        <?php foreach ($genres as $genre): ?>
                            <option value="<?= (int) $genre['id'] ?>">
                                <?= htmlspecialchars($genre['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filterCountry">Quốc gia</label>
                    <select id="filterCountry" name="country">
                        <option value="">Tất cả quốc gia</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= htmlspecialchars($country['code'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($country['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filterYear">Năm phát hành</label>
                    <select id="filterYear" name="year">
                        <option value="">Tất cả các năm</option>
                        <?php for ($year = (int) date('Y'); $year >= 2000; $year--): ?>
                            <option value="<?= $year ?>"><?= $year ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filterSort">Sắp xếp theo</label>
                    <select id="filterSort" name="sort">
                        <option value="newest">Mới cập nhật</option>
                        <option value="most_viewed">Lượt xem nhiều nhất</option>
                        <option value="popular">Phổ biến</option>
                        <option value="top_rated">Đánh giá cao</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="button" class="btn-clear-filter">Xóa lọc</button>
                </div>
            </form>
        </section>

        <section class="movies-result-section" aria-labelledby="browseResultTitle">
            <h1 class="result-title" id="browseResultTitle">Danh sách phim tổng hợp</h1>
            <div class="browse-status" id="browseMovieStatus" role="status" aria-live="polite"></div>
            <div class="movies-grid" id="browseMovieList"></div>
        </section>

        <nav class="pagination-container" id="browsePagination" aria-label="Phân trang"></nav>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
