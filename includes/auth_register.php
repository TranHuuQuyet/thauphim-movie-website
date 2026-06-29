<form action="/register.php" method="post" class="auth-form auth-register-form">
    <span class="brand-title">Thau<strong>Phim</strong></span>
    <h1>Đăng ký tài khoản</h1>

    <?php if (!empty($registerErrors)): ?>
        <div class="auth-errors">
            <?php foreach ($registerErrors as $error): ?>
                <p><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <label class="sr-only" for="registerUsername">Tên đăng nhập</label>
    <input
        id="registerUsername"
        type="text"
        name="username"
        placeholder="username"
        autocomplete="username"
        value="<?= htmlspecialchars((string) ($registerOld["username"] ?? ""), ENT_QUOTES, "UTF-8") ?>"
        required>

    <label class="sr-only" for="registerEmail">Email</label>
    <input
        id="registerEmail"
        type="email"
        name="email"
        placeholder="email"
        autocomplete="email"
        value="<?= htmlspecialchars((string) ($registerOld["email"] ?? ""), ENT_QUOTES, "UTF-8") ?>"
        required>

    <label class="sr-only" for="registerPassword">Mật khẩu</label>
    <input
        id="registerPassword"
        type="password"
        name="password"
        placeholder="password"
        autocomplete="new-password"
        required>

    <label class="sr-only" for="registerPasswordConfirm">Nhập lại mật khẩu</label>
    <input
        id="registerPasswordConfirm"
        type="password"
        name="password_confirm"
        placeholder="confirm password"
        autocomplete="new-password"
        required>

    <button type="submit">Đăng ký</button>
    <p>Đã có tài khoản, <a href="/index.php#authModal">đăng nhập ngay.</a></p>
</form>
