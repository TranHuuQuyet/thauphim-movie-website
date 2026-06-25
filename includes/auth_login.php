<div class="auth-modal <?= !empty($_SESSION["login_errors"]) ? "active" : "" ?>" id="authModal">
    <div class="auth-card">
        <div class="auth-screen">
            <img src="/thauphim-movie-website/assets/images/poster_movie.jpg" alt="">
        </div>
        <div class="auth-panel">
            <button type="button" id="closeAuth" class="auth-back">
                <span>Quay lại </span><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                    width="24px" fill="#e3e3e3">
                    <path d="M647-440H160v-80h487L423-744l57-56 320 320-320 320-57-56 224-224Z" />
                </svg>
            </button>
            <form action="/thauphim-movie-website/login.php" method="post" class="auth-form">
                <span class="brand-title">Thau<strong>Phim</strong></span>

                <?php if (!empty($_SESSION["login_errors"])): ?>
                <div class="auth-errors">
                    <?php foreach ($_SESSION["login_errors"] as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION["login_errors"]); ?>
                <?php endif; ?>

                <label class="sr-only" for="loginUsername">Ten dang nhap</label>
                <input id="loginUsername" type="text" placeholder="username/email" name="username" autocomplete="username"
                    value="<?= htmlspecialchars((string) ($_SESSION["login_old"]["username"] ?? ""), ENT_QUOTES, "UTF-8") ?>" />
                <label class="sr-only" for="loginPassword">Mat khau</label>
                <input id="loginPassword" type="password" placeholder="password" name="password"
                    autocomplete="current-password" />
                <button type="submit">Đăng nhập</button>
                <p>Bạn chưa có tài khoản,<a href="/thauphim-movie-website/register.php">đăng ký ngay.</a></p>
                <p class="forgot">QUÊN MẬT KHẨU?</p>
            </form>
            <?php unset($_SESSION["login_old"]); ?>
        </div>
    </div>
</div>
