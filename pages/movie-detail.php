<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include '../includes/auth_modal.php'; ?>

<?php $movieId = isset($_GET["id"]) ? (int) $_GET["id"] : 1; ?>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/reponsive.css">
<link rel="stylesheet" href="../assets/css/auth.css">
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

                    <div class="detail-status">
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
                        <p><strong>Networks:</strong> Phim chiếu rạp</p>
                        <p><strong>Sản xuất:</strong> Marvel Studios & Sony Pictures</p>
                        <p><strong>Diễn viên:</strong> Tom Holland, Zendaya, Jon Bernthal,...</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="detail-main-content">
            <section class="detail-actions">
                <a class="play-btn" href="watch.php?movie_id=<?= $movieId ?>&ep=1">▶ Xem Ngay</a>

                <button class="detail-action-btn" type="button">♡ Yêu thích</button>
                <button class="detail-action-btn" type="button">＋ Thêm vào</button>
                <button class="detail-action-btn" type="button">↗ Chia sẻ</button>
                <button class="detail-action-btn" type="button">💬 Bình luận</button>
            </section>

            <section class="detail-tabs">
                <button class="detail-tab active" type="button">Tập phim</button>
                <button class="detail-tab" type="button">Gallery</button>
                <button class="detail-tab" type="button">OST</button>
                <button class="detail-tab" type="button">Diễn viên</button>
                <button class="detail-tab" type="button">Đề xuất</button>
            </section>

            <section class="movie-section episode-section">
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

            <section class="movie-section comment-section">
                <div class="section-head">
                    <h2>Bình luận</h2>
                </div>

                <p class="comment-login-note">
                    Vui lòng <a href="../login.php">đăng nhâp</a> để bình luận.
                </p>

                <div class="comment-box">
                    <textarea placeholder="Viết bình luận" disabled></textarea>
                    <div class="comment-actions">
                        <span>0 / 1000</span>
                        <button type="button" disabled>Gửi</button>
                    </div>
                </div>

                <div class="comment-empty">Chưa có bình luận nào</div>
            </section>

            <section class="movie-section related-section">
                <div class="section-head">
                    <h2>Có thể bạn sẽ thích</h2>
                    <a href="#" class="view-more">Xem thêm</a>
                </div>

                <div class="related-movie-grid" id="relatedMovies"></div>
            </section>
        </section>
    </section>
</main>

<script src="../assets/js/movie-detail.js"></script>
<script src="../assets/js/auth_modal.js"></script>


<?php include __DIR__ . '/../includes/footer.php'; ?>