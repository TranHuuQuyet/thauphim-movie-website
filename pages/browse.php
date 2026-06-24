<?php
require_once __DIR__ . '/../includes/functions.php';

$limit = 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$params = [
    'q'            => $_GET['q'] ?? '',
    'type'         => $_GET['type'] ?? '', 
    'genre_id'     => $_GET['genre'] ?? '',
    'country'      => $_GET['country'] ?? '',
    'release_year' => $_GET['year'] ?? '',
    'sort'         => $_GET['sort'] ?? 'newest'
];

$movies      = getFilteredMovies($params, $limit, $offset);
$totalMovies = countFilteredMovies($params); 
$totalPages  = ceil($totalMovies / $limit);

$genres    = getAllGenres();
$countries = getAllCountriesFromDB();
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<link rel="stylesheet" href="/thauphim-movie-website/assets/css/browse.css">

<main class="page-shell" aria-label="Bộ lọc và tìm kiếm phim">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px 15px;">
        
        <section class="filter-section" style="background: #1a1a1a; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <form action="/thauphim-movie-website/pages/browse.php" method="GET" class="filter-form" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
                
                <div class="filter-group" style="flex: 1; min-width: 200px;">
                    <label style="display: block; color: #ccc; margin-bottom: 5px; font-size: 14px;">Từ khóa</label>
                    <input type="text" name="keyword" placeholder="Nhập tên phim cần tìm..." 
                           value="<?= htmlspecialchars($_GET['keyword'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           style="width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                </div>

                <div class="filter-group" style="min-width: 150px;">
                    <label style="display: block; color: #ccc; margin-bottom: 5px; font-size: 14px;">Định dạng</label>
                    <select name="type" style="width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                        <option value="">-- Tất cả --</option>
                        <option value="movie" <?= ($_GET['type'] ?? '') === 'movie' ? 'selected' : '' ?>>Phim lẻ</option>
                        <option value="series" <?= ($_GET['type'] ?? '') === 'series' ? 'selected' : '' ?>>Phim bộ</option>
                    </select>
                </div>

                <div class="filter-group" style="min-width: 160px;">
                    <label style="display: block; color: #ccc; margin-bottom: 5px; font-size: 14px;">Thể loại</label>
                    <select name="genre" style="width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                        <option value="">-- Tất cả thể loại --</option>
                        <?php foreach ($genres as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= ($_GET['genre'] ?? '') == $g['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group" style="min-width: 160px;">
                    <label style="display: block; color: #ccc; margin-bottom: 5px; font-size: 14px;">Quốc gia</label>
                    <select name="country" style="width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                        <option value="">-- Tất cả quốc gia --</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?= $c['code'] ?>" <?= ($_GET['country'] ?? '') === $c['code'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group" style="min-width: 150px;">
                    <label style="display: block; color: #ccc; margin-bottom: 5px; font-size: 14px;">Năm phát hành</label>
                    <select name="year" style="width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                        <option value="">-- Tất cả các năm --</option>
                        <?php for($y = date('Y'); $y >= 2000; $y--): ?>
                            <option value="<?= $y ?>" <?= ($_GET['year'] ?? '') == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="filter-group" style="min-width: 150px;">
                    <label style="display: block; color: #ccc; margin-bottom: 5px; font-size: 14px;">Sắp xếp theo</label>
                    <select name="sort" style="width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px;">
                        <option value="newest" <?= ($_GET['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Mới cập nhật</option>
                        <option value="most_viewed" <?= ($_GET['sort'] ?? '') === 'most_viewed' ? 'selected' : '' ?>>Lượt xem nhiều nhất</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" style="padding: 10px 25px; background: #e50914; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
                        Lọc Phim
                    </button>
                    <button type="button" class="btn-clear-filter" style="padding: 10px 15px; background: #444; color: #fff; border: none; border-radius: 4px; font-size: 14px; margin-left: 5px; cursor: pointer;">
                        Xóa lọc
                    </button>
                </div>
            </form>
        </section>

        <section class="movies-result-section">
            <h2 style="color: #fff; font-size: 22px; margin-bottom: 20px; border-left: 4px solid #e50914; padding-left: 10px;">
                <?php 
                    if (!empty($_GET['keyword'])) {
                        echo "Kết quả tìm kiếm cho: \"" . htmlspecialchars($_GET['keyword'], ENT_QUOTES, 'UTF-8') . "\"";
                    } else {
                        echo "Danh sách phim tổng hợp";
                    }
                    echo " (" . $totalMovies . " phim)";
                ?>
            </h2>

            <?php if (!empty($movies)): ?>
                <div class="movies-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px;">
                    <?php foreach ($movies as $movie): ?>
                        <div class="movie-card" style="background: #111; border-radius: 6px; overflow: hidden; border: 1px solid #222; position: relative;">
                            <a href="/thauphim-movie-website/pages/movie-detail.php?id=<?= $movie['id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                                
                                <div class="poster-wrapper" style="position: relative; padding-top: 145%; background: #222; overflow: hidden;">
                                    <?php 
                                        $posterSrc = $movie['poster_url'] ?? $movie['poster'] ?? '/thauphim-movie-website/assets/images/default.jpg';
                                    ?>
                                    <img src="<?= htmlspecialchars($posterSrc, ENT_QUOTES, 'UTF-8') ?>" 
                                         alt="<?= htmlspecialchars($movie['title'], ENT_QUOTES, 'UTF-8') ?>"
                                         style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">

                                    <span style="position: absolute; top: 8px; left: 8px; background: rgba(229, 9, 20, 0.9); color: #fff; padding: 3px 6px; font-size: 11px; font-weight: bold; border-radius: 3px; text-transform: uppercase;">
                                        <?= (isset($movie['type']) && $movie['type'] === 'series') ? 'Phim Bộ' : 'Phim Lẻ' ?>
                                    </span>
                                </div>

                                <div class="movie-meta" style="padding: 12px;">
                                    <h3 style="margin: 0 0 5px 0; font-size: 15px; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($movie['title'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($movie['title'], ENT_QUOTES, 'UTF-8') ?>
                                    </h3>
                                    <div style="display: flex; justify-content: space-between; align-items: center; color: #888; font-size: 12px;">
                                        <span><?= htmlspecialchars($movie['release_year'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
                                        <span>Lượt xem: <?= number_format($movie['views'] ?? 0) ?></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results-box" style="text-align: center; padding: 60px 20px; background: #111; border-radius: 8px; border: 1px dashed #333; margin-top: 20px;">
                    <p style="color: #aaa; font-size: 16px; margin: 0;">Không có bộ phim nào khớp với các bộ lọc của bạn.</p>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination-container" aria-label="Phân trang" style="margin-top: 40px; display: flex; justify-content: center;">
                <ul style="display: flex; list-style: none; padding: 0; margin: 0; gap: 8px;">
                    <?php 
                    $queryParams = $_GET;
                    ?>
                    <?php
                    $queryParams['page'] = $i;
                    ?>
                    <a href="?<?= http_build_query($queryParams) ?>"
                        class="page-link <?= $page == $i ? 'active-page' : '' ?>">
                            <?= $i ?>
                    </a>
                </ul>
            </nav>
        <?php endif; ?>

    </div>
</main>

<script src="/thauphim-movie-website/assets/js/browse.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
    </div>
</main>

