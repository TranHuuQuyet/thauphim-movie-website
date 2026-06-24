<?php include __DIR__ . '/../includes/header.php'; ?>

<?php
$movieId = isset($_GET["id"]) ? (int) $_GET["id"] : 1;

$isLoggedIn = !empty($_SESSION["is_login"]);
$currentUserId = $_SESSION["username"] ?? null;
$currentUserName = $_SESSION["username"] ?? "Bạn";
?>

<link rel="stylesheet" href="../assets/css/movie-detail.css">

<main class="page-shell detail-page">
    <section class="hero detail-hero">
        <div class="video-bg">
            <iframe
                src="https://www.youtube.com/embed/62bIsvRcPv0?autoplay=1&mute=1&loop=1&playlist=62bIsvRcPv0&controls=0&rel=0"
                allow="autoplay; encrypted-media" allowfullscreen>
            </iframe>
        </div>

        <div class="hero-overlay"></div>
    </section>

    <section class="detail-body">
        <section id="movie-info" class="detail-sidebar">
            <div class="detail-info-card">
                <div class="detail-poster">
                    <img src="../assets/images/poster-spiderman.webp" alt="Spider Man">
                </div>

                <div class="detail-info-text">
                    <h1>Spider Man</h1>
                    <p class="movie-name">Brand New Day</p>

                    <a href="#movie-info" class="detail-info-link">Thông tin phim</a>
                </div>

                <div class="detail-info-meta">
                    <div class="detail-badges">
                        <span class="detail-badge age">T13</span>
                        <span class="detail-badge">31/7</span>
                        <span class="detail-badge">2026</span>
                        <span class="detail-badge">4K</span>
                    </div>

                    <div class="detail-tags">
                        <span>#SpiderMan</span>
                        <span>#trailer</span>
                        <span>#marvel</span>
                        <span>#Sony</span>
                    </div>

                    <div class="detail-status status-warning">
                        <span>Chưa ra mắt</span>
                    </div>

                    <div class="detail-desc-block">
                        <h3>Giới thiệu</h3>
                        <p>
                            Watch the new trailer for #SpiderManBrandNewDay, in theatres July 31. Tickets on sale NOW.
                        </p>
                    </div>

                    <div class="detail-info-list">
                        <p><strong>Thời lượng:</strong> 2h25p</p>
                        <p><strong>Quốc gia:</strong> USA</p>
                        <p><strong>Sản xuất:</strong> Marvel Studios & Sony Pictures</p>
                        <p><strong>Diễn viên:</strong> Tom Holland, Zendaya, Jon Bernthal,...</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="detail-main-content">
            <section class="detail-actions">
                <a class="play-btn" id="watchNowBtn" href="watch.php?movie_id=<?= $movieId ?>&ep=1">▶ Xem Ngay</a>

                <button class="detail-action-btn" id="favoriteBtn" type="button">♡ Yêu thích</button>
                <button class="detail-action-btn" id="addListBtn" type="button">＋ Thêm vào</button>
                <button class="detail-action-btn" id="shareBtn" type="button">↗ Chia sẻ</button>
                <button class="detail-action-btn" id="commentBtn" type="button">💬 Bình luận</button>
            </section>

            <section class="detail-tabs">
                <button class="detail-tab active" type="button" data-target="#episode-section">Tập phim</button>
                <button class="detail-tab" type="button" data-target="#gallery-section">Gallery</button>
                <button class="detail-tab" type="button" data-target="#ost-section">OST</button>
                <button class="detail-tab" type="button" data-target="#actors-section">Diễn viên</button>
                <button class="detail-tab" type="button" data-target="#related-section">Đề xuất</button>
            </section>

            <section class="movie-section episode-section detail-tab-panel active" id="episode-section">
                <div class="episode-head">
                    <h2>☰ Phần 1</h2>

                    <div class="episode-options">
                        <span>Phụ đề</span>
                        <span>4K</span>
                    </div>
                </div>

                <div class="episode-list" id="episodeList">
                    <a class="episode-btn" href="watch.php?movie_id=<?= $movieId ?>&ep=1">▶ Tập 1</a>
                </div>
            </section>

            <section class="movie-section gallery-section detail-tab-panel" id="gallery-section">
                <div class="section-head">
                    <h2>Gallery</h2>
                </div>

                <div class="detail-placeholder">Đang cập nhật</div>
            </section>

            <section class="movie-section ost-section detail-tab-panel" id="ost-section">
                <div class="section-head">
                    <h2>OST</h2>
                </div>

                <div class="detail-placeholder">Đang cập nhật</div>
            </section>

            <section class="movie-section actors-section detail-tab-panel" id="actors-section">
                <div class="section-head">
                    <h2>Diễn viên</h2>
                </div>

                <div class="detail-placeholder" id="actorsPanel">
                    Đang cập nhật
                </div>
            </section>

            <section class="movie-section comment-section">
                <div class="section-head">
                    <h2>Bình luận</h2>
                </div>

                <?php if (!$isLoggedIn): ?>
                    <p class="comment-login-note">
                        Vui lòng <a href="../login.php">đăng nhập</a> để bình luận.
                    </p>

                    <div class="comment-box">
                        <textarea placeholder="Viết bình luận" disabled></textarea>
                        <div class="comment-actions">
                            <span>0 / 1000</span>
                            <button type="button" disabled>Gửi</button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="comment-box">
                        <textarea id="commentInput" placeholder="Viết bình luận"></textarea>
                        <div class="comment-actions">
                            <span id="commentCount">0 / 1000</span>
                            <button id="sendCommentBtn" type="button">Gửi</button>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="comment-empty">Chưa có bình luận nào</div>
            </section>

            <section class="movie-section rating-section" id="rating-section">
                <div class="section-head">
                    <h2>Đánh giá phim</h2>
                </div>

                <div class="rating-box">
                    <div class="rating-summary">
                        <strong id="ratingAverage">Chưa có đánh giá</strong>
                        <span id="ratingTotal">0 lượt đánh giá</span>
                    </div>

                    <?php if (!$isLoggedIn): ?>
                        <p class="rating-login-note">
                            Vui lòng <a href="../login.php">đăng nhập</a> để đánh giá.
                        </p>
                    <?php else: ?>
                        <div class="rating-stars" id="ratingStars">
                            <button type="button" data-rating="1">☆</button>
                            <button type="button" data-rating="2">☆</button>
                            <button type="button" data-rating="3">☆</button>
                            <button type="button" data-rating="4">☆</button>
                            <button type="button" data-rating="5">☆</button>
                        </div>

                        <p class="rating-message" id="ratingMessage">Chọn số sao để đánh giá phim.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="movie-section related-section" id="related-section">
                <div class="section-head">
                    <h2>Có thể bạn sẽ thích</h2>
                    <a href="#" class="view-more">Xem thêm</a>
                </div>

                <div class="related-movie-grid" id="relatedMovies"></div>
            </section>
        </section>
    </section>
</main>

<script>
    const TMDB_API_KEY = "9b4592d22d37d5f7ac7a5f6514fbdc0b";

    window.currentUser = {
        id: <?= json_encode($currentUserId) ?>,
        name: <?= json_encode($currentUserName) ?>
    };
</script>

<script src="../assets/js/main.js"></script>
<script src="../assets/js/movie-detail.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>