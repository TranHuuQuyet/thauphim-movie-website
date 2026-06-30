<?php
require_once __DIR__ . '/../includes/functions.php';

$genres    = getAllGenres();
$countries = getAllCountriesFromDB();
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<link rel="stylesheet" href="/thauphim-review-website/assets/css/browse.css">

<main class="page-shell" aria-label="Bộ lọc và tìm kiếm phim">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px 15px;">
        
        <section class="filter-section" style="background: #1a1a1a; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <form id="filterForm" class="filter-form" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
                
                <div class="filter-group" style="flex: 1; min-width: 200px;">
                    <label style="display: block; color: #ccc; margin-bottom: 5px; font-size: 14px;">Từ khóa</label>
                    <input type="text" name="q" placeholder="Nhập tên phim cần tìm..." 
                           style="width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                </div>

                <div class="filter-group" style="min-width: 150px;">
                    <label style="display: block; color: #ccc; margin-bottom: 5px; font-size: 14px;">Định dạng</label>
                    <select name="type" style="width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                        <option value="">-- Tất cả --</option>
                        <option value="movie">Phim lẻ</option>
                        <option value="series">Phim bộ</option>
                    </select>
                </div>

                <div class="filter-group" style="min-width: 160px;">
                    <label style="display: block; color: #ccc; margin-bottom: 5px; font-size: 14px;">Thể loại</label>
                    <select name="genre" style="width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                        <option value="">-- Tất cả thể loại --</option>
                        <?php foreach ($genres as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group" style="min-width: 160px;">
                    <label style="display: block; color: #ccc; margin-bottom: 5px; font-size: 14px;">Quốc gia</label>
                    <select name="country" style="width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                        <option value="">-- Tất cả quốc gia --</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?= $c['code'] ?>"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group" style="min-width: 150px;">
                    <label style="display: block; color: #ccc; margin-bottom: 5px; font-size: 14px;">Năm phát hành</label>
                    <select name="year" style="width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                        <option value="">-- Tất cả các năm --</option>
                        <?php for($y = date('Y'); $y >= 2000; $y--): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="filter-group" style="min-width: 150px;">
                    <label style="display: block; color: #ccc; margin-bottom: 5px; font-size: 14px;">Sắp xếp theo</label>
                    <select name="sort" style="width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                        <option value="newest">Mới cập nhật</option>
                        <option value="most_viewed">Lượt xem nhiều nhất</option>
                        <option value="popular">Phổ biến</option>
                        <option value="top_rated">Đánh giá cao</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="button" class="btn-clear-filter" style="padding: 10px 15px; background: #444; color: #fff; border: none; border-radius: 4px; font-size: 14px; cursor: pointer;">
                        Xóa lọc
                    </button>
                </div>
            </form>
        </section>

        <section class="movies-result-section">
            <h2 id="browseResultTitle" style="color: #fff; font-size: 22px; margin-bottom: 20px; border-left: 4px solid #e50914; padding-left: 10px;">
                Danh sách phim tổng hợp
            </h2>
            
            <div id="browseMovieStatus" style="text-align: center; color: #aaa; margin: 20px 0;"></div>

            <div class="movies-grid" id="browseMovieList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px;">
            </div>
        </section>

        <nav class="pagination-container" id="browsePagination" aria-label="Phân trang" style="margin-top: 40px; display: flex; justify-content: center;">
        </nav>

    </div>
</main>

<script src="/thauphim-movie-website/assets/js/browse.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>