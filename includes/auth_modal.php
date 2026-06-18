<div class="auth-modal" id="authModal">
    <div class="auth-card">
        <div class="auth-screen">
            <img src="assets/images/poster_movie.jpg" alt="">
        </div>
        <div class="auth-panel">
            <button type="button" id="closeAuth" class="auth-back">
                <span>Quay lại </span><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                    width="24px" fill="#e3e3e3">
                    <path d="M647-440H160v-80h487L423-744l57-56 320 320-320 320-57-56 224-224Z" />
                </svg>
            </button>
            <form action="login.php" method="post" class="auth-form">
                <span class="brand-title">Thau<strong>Phim</strong></span>
                <p>Bạn chưa có tài khoản,<a href="register.php">đăng ký ngay.</a></p>
                <label class="sr-only" for="loginUsername">Ten dang nhap</label>
                <input id="loginUsername" type="text" placeholder="username" name="username" autocomplete="username" />
                <label class="sr-only" for="loginPassword">Mat khau</label>
                <input id="loginPassword" type="password" placeholder="password" name="password"
                    autocomplete="current-password" />
                <button type="submit">Đăng nhập</button>
                <p class="forgot">QUÊN MẬT KHẨU?</p>
            </form>
        </div>
    </div>
</div>