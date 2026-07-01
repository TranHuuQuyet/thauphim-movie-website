<?php
require_once __DIR__ . "/auth.php";

if (!function_exists("auth_ui_e")) {
    function auth_ui_e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
    }
}

if (!function_exists("auth_ui_movie_image")) {
    function auth_ui_movie_image(?string $poster, ?string $posterPath, string $size = "w780"): string
    {
        $poster = trim((string) $poster);
        if ($poster !== "") {
            if (preg_match('/^https?:\/\//i', $poster) || str_starts_with($poster, "/")) {
                return $poster;
            }

            return "/" . ltrim($poster, "/");
        }

        $posterPath = trim((string) $posterPath);
        if ($posterPath !== "") {
            if (preg_match('/^https?:\/\//i', $posterPath)) {
                return $posterPath;
            }

            return "https://image.tmdb.org/t/p/" . $size . "/" . ltrim($posterPath, "/");
        }

        return "/assets/images/poster_movie.jpg";
    }
}

if (!function_exists("auth_ui_featured_movies")) {
    function auth_ui_featured_movies(?PDO $pdo = null): array
    {
        $slides = [];

        try {
            $pdo = $pdo ?: getDatabaseConnection();
            $stmt = $pdo->query("
                SELECT id, title, poster, poster_path
                FROM movies
                ORDER BY views DESC, popularity DESC, created_at DESC
                LIMIT 3
            ");
            $slides = $stmt->fetchAll() ?: [];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
        }

        $slides = array_map(static function (array $movie): array {
            return [
                "title" => (string) ($movie["title"] ?? "ThauPhim"),
                "poster" => auth_ui_movie_image($movie["poster"] ?? null, $movie["poster_path"] ?? null),
            ];
        }, $slides);

        while (count($slides) < 3) {
            $slides[] = [
                "title" => "ThauPhim",
                "poster" => "/assets/images/poster_movie.jpg",
            ];
        }

        return array_slice($slides, 0, 3);
    }
}

if (!function_exists("auth_ui_render_brand")) {
    function auth_ui_render_brand(): void
    {
        ?>
        <div class="auth-brand">
            <span class="brand-title">Thau<strong>Phim</strong></span>
            <p>Đăng nhập để tiếp tục xem phim yêu thích.</p>
        </div>
        <?php
    }
}

if (!function_exists("auth_ui_render_login_form")) {
    function auth_ui_render_login_form(array $options = []): void
    {
        $idSuffix = (string) ($options["id_suffix"] ?? "modal");
        $errors = $options["errors"] ?? [];
        $notices = $options["notices"] ?? [];
        $old = $options["old"] ?? [];
        $active = (bool) ($options["active"] ?? true);
        $usernameId = "loginUsername-" . $idSuffix;
        $passwordId = "loginPassword-" . $idSuffix;
        ?>
        <form
            action="<?= auth_ui_e(app_url("login.php")) ?>"
            method="post"
            class="auth-form"
            data-auth-panel="login"
            id="auth-login-panel-<?= auth_ui_e($idSuffix) ?>"
            aria-labelledby="auth-login-tab-<?= auth_ui_e($idSuffix) ?>"
            <?= $active ? "" : "hidden" ?>>
            <h1>Đăng nhập</h1>

            <?php if (!empty($notices)): ?>
                <div class="auth-success" role="status">
                    <?php foreach ($notices as $notice): ?>
                        <p><?= auth_ui_e($notice) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="auth-errors" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?= auth_ui_e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="auth-field">
                <label for="<?= auth_ui_e($usernameId) ?>">Username</label>
                <input
                    id="<?= auth_ui_e($usernameId) ?>"
                    type="text"
                    name="username"
                    placeholder="Nhập username hoặc email"
                    autocomplete="username"
                    value="<?= auth_ui_e($old["username"] ?? "") ?>"
                    required>
            </div>

            <div class="auth-field">
                <label for="<?= auth_ui_e($passwordId) ?>">Password</label>
                <input
                    id="<?= auth_ui_e($passwordId) ?>"
                    type="password"
                    name="password"
                    placeholder="Nhập password"
                    autocomplete="current-password"
                    required>
            </div>

            <button type="submit" class="auth-submit">Đăng nhập</button>
            <a class="auth-forgot" href="<?= auth_ui_e(app_url("forgot-password.php")) ?>">Quên mật khẩu?</a>
        </form>
        <?php
    }
}

if (!function_exists("auth_ui_render_register_form")) {
    function auth_ui_render_register_form(array $options = []): void
    {
        $idSuffix = (string) ($options["id_suffix"] ?? "page");
        $errors = $options["errors"] ?? [];
        $old = $options["old"] ?? [];
        $active = (bool) ($options["active"] ?? true);
        $usernameId = "registerUsername-" . $idSuffix;
        $emailId = "registerEmail-" . $idSuffix;
        $passwordId = "registerPassword-" . $idSuffix;
        $confirmId = "registerPasswordConfirm-" . $idSuffix;
        ?>
        <form
            action="<?= auth_ui_e(app_url("register.php")) ?>"
            method="post"
            class="auth-form auth-register-form"
            data-auth-panel="register"
            id="auth-register-panel-<?= auth_ui_e($idSuffix) ?>"
            aria-labelledby="auth-register-tab-<?= auth_ui_e($idSuffix) ?>"
            <?= $active ? "" : "hidden" ?>>
            <h1>Tạo tài khoản</h1>

            <?php if (!empty($errors)): ?>
                <div class="auth-errors" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?= auth_ui_e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="auth-field">
                <label for="<?= auth_ui_e($usernameId) ?>">Username</label>
                <input
                    id="<?= auth_ui_e($usernameId) ?>"
                    type="text"
                    name="username"
                    placeholder="Chọn username"
                    autocomplete="username"
                    value="<?= auth_ui_e($old["username"] ?? "") ?>"
                    required>
            </div>

            <div class="auth-field">
                <label for="<?= auth_ui_e($emailId) ?>">Email</label>
                <input
                    id="<?= auth_ui_e($emailId) ?>"
                    type="email"
                    name="email"
                    placeholder="Nhập email"
                    autocomplete="email"
                    value="<?= auth_ui_e($old["email"] ?? "") ?>"
                    required>
            </div>

            <div class="auth-field">
                <label for="<?= auth_ui_e($passwordId) ?>">Password</label>
                <input
                    id="<?= auth_ui_e($passwordId) ?>"
                    type="password"
                    name="password"
                    placeholder="Tạo password"
                    autocomplete="new-password"
                    required>
            </div>

            <div class="auth-field">
                <label for="<?= auth_ui_e($confirmId) ?>">Nhập lại password</label>
                <input
                    id="<?= auth_ui_e($confirmId) ?>"
                    type="password"
                    name="password_confirm"
                    placeholder="Nhập lại password"
                    autocomplete="new-password"
                    required>
            </div>

            <button type="submit" class="auth-submit">Đăng ký</button>
            <p class="auth-switch-copy">Đã có tài khoản? <a href="<?= auth_ui_e(app_url("index.php#authModal")) ?>" data-auth-switch="login">Đăng nhập</a></p>
        </form>
        <?php
    }
}

if (!function_exists("auth_ui_render_surface")) {
    function auth_ui_render_surface(array $options = []): void
    {
        $context = (string) ($options["context"] ?? "modal");
        $mode = (string) ($options["mode"] ?? "login");
        $mode = in_array($mode, ["login", "register"], true) ? $mode : "login";
        $idSuffix = preg_replace('/[^A-Za-z0-9_-]/', "", (string) ($options["id_suffix"] ?? $context));
        $idSuffix = $idSuffix !== "" ? $idSuffix : "auth";
        $slides = $options["slides"] ?? auth_ui_featured_movies($options["pdo"] ?? null);
        $loginErrors = $options["login_errors"] ?? [];
        $loginNotices = $options["login_notices"] ?? [];
        $registerErrors = $options["register_errors"] ?? [];
        $loginOld = $options["login_old"] ?? [];
        $registerOld = $options["register_old"] ?? [];
        $isPage = $context === "page";
        ?>
        <section class="auth-card auth-card--<?= auth_ui_e($context) ?>" data-auth-root data-auth-mode="<?= auth_ui_e($mode) ?>">
            <div class="auth-screen" data-auth-carousel>
                <div class="auth-slides">
                    <?php foreach ($slides as $index => $slide): ?>
                        <?php $isActive = $index === 0; ?>
                        <figure class="auth-slide<?= $isActive ? " is-active" : "" ?>" data-auth-slide>
                            <img
                                src="<?= auth_ui_e($slide["poster"] ?? "/assets/images/poster_movie.jpg") ?>"
                                alt="Poster phim <?= auth_ui_e($slide["title"] ?? "ThauPhim") ?>"
                                loading="<?= $index === 0 ? "eager" : "lazy" ?>">
                            <figcaption>
                                <span>Top phim hot</span>
                                <strong><?= auth_ui_e($slide["title"] ?? "ThauPhim") ?></strong>
                            </figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>

                <?php if ($isPage): ?>
                    <a class="auth-back" href="<?= auth_ui_e(app_url("index.php")) ?>" aria-label="Quay lại trang chủ">
                        <span>Quay lại</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <button type="button" id="closeAuth" class="auth-back" data-auth-close aria-label="Đóng đăng nhập">
                        <span>Quay lại</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </button>
                <?php endif; ?>

                <div class="auth-slide-dots" aria-label="Poster phim nổi bật">
                    <?php foreach ($slides as $index => $slide): ?>
                        <button
                            type="button"
                            class="<?= $index === 0 ? "is-active" : "" ?>"
                            data-auth-dot
                            aria-label="Xem poster <?= $index + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="auth-panel">
                <?php auth_ui_render_brand(); ?>

                <div class="auth-tabs" role="tablist" aria-label="Chọn form tài khoản">
                    <button
                        type="button"
                        id="auth-login-tab-<?= auth_ui_e($idSuffix) ?>"
                        class="<?= $mode === "login" ? "is-active" : "" ?>"
                        data-auth-tab="login"
                        role="tab"
                        aria-controls="auth-login-panel-<?= auth_ui_e($idSuffix) ?>"
                        aria-selected="<?= $mode === "login" ? "true" : "false" ?>">
                        Đăng nhập
                    </button>
                    <button
                        type="button"
                        id="auth-register-tab-<?= auth_ui_e($idSuffix) ?>"
                        class="<?= $mode === "register" ? "is-active" : "" ?>"
                        data-auth-tab="register"
                        role="tab"
                        aria-controls="auth-register-panel-<?= auth_ui_e($idSuffix) ?>"
                        aria-selected="<?= $mode === "register" ? "true" : "false" ?>">
                        Tạo tài khoản
                    </button>
                </div>

                <div class="auth-form-stack">
                    <?php
                    auth_ui_render_login_form([
                        "id_suffix" => $idSuffix,
                        "errors" => $loginErrors,
                        "notices" => $loginNotices,
                        "old" => $loginOld,
                        "active" => $mode === "login",
                    ]);

                    auth_ui_render_register_form([
                        "id_suffix" => $idSuffix,
                        "errors" => $registerErrors,
                        "old" => $registerOld,
                        "active" => $mode === "register",
                    ]);
                    ?>
                </div>
            </div>
        </section>
        <?php
    }
}
