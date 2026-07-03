<footer class="site-footer" id="footer">
    <div class="footer-container">
        <div class="footer-top">
            <div class="footer-brand">
                <?php
                $brandClass = 'brand footer-brand-lockup';
                $brandHref = app_url('index.php#hero');
                include __DIR__ . '/brand_lockup.php';
                ?>

                <div class="footer-socials" aria-label="Theo dõi ThauPhim">
                    <a href="#footer" aria-label="Facebook" title="Facebook">
                        <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
                    </a>
                    <a href="#footer" aria-label="Discord" title="Discord">
                        <i class="fa-brands fa-discord" aria-hidden="true"></i>
                    </a>
                    <a href="#footer" aria-label="GitHub" title="GitHub">
                        <i class="fa-brands fa-github" aria-hidden="true"></i>
                    </a>
                    <a href="#footer" aria-label="YouTube" title="YouTube">
                        <i class="fa-brands fa-youtube" aria-hidden="true"></i>
                    </a>
                </div>
            </div>

            <nav class="footer-links" aria-label="Điều hướng chân trang">
                <div class="footer-col">
                    <h2>Khám phá</h2>
                    <a href="<?= htmlspecialchars(app_url('index.php#hero'), ENT_QUOTES, 'UTF-8') ?>">Trang chủ</a>
                    <a href="<?= htmlspecialchars(app_url('index.php#single-movies'), ENT_QUOTES, 'UTF-8') ?>">Phim lẻ</a>
                    <a href="<?= htmlspecialchars(app_url('index.php#series-movies'), ENT_QUOTES, 'UTF-8') ?>">Phim bộ</a>
                    <a href="<?= htmlspecialchars(app_url('pages/genres.php'), ENT_QUOTES, 'UTF-8') ?>">Thể loại</a>
                </div>

                <div class="footer-col">
                    <h2>Hỗ trợ</h2>
                    <a href="#footer">FAQ</a>
                    <a href="#footer">Liên hệ</a>
                    <a href="#footer">Chính sách</a>
                    <a href="#footer">Điều khoản</a>
                </div>

                <div class="footer-col">
                    <h2>Dự án</h2>
                    <a href="#footer">Đồ án Web</a>
                    <a href="#footer">PHP + MySQL</a>
                    <a href="#footer">HTML/CSS/JS</a>
                    <a href="<?= htmlspecialchars(app_url('index.php#hero'), ENT_QUOTES, 'UTF-8') ?>">ThauPhim</a>
                </div>
            </nav>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 ThauPhim - Đồ án học phần Lập trình Web</p>

            <div class="footer-legal" aria-label="Liên kết pháp lý">
                <a href="#footer">Chính sách</a>
                <a href="#footer">Điều khoản</a>
                <a href="#footer">Liên hệ</a>
            </div>
        </div>
    </div>
</footer>
