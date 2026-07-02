<?php

if (!function_exists("auth_reset_e")) {
    function auth_reset_e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
    }
}

if (!function_exists("auth_reset_render_topbar")) {
    function auth_reset_render_topbar(): void
    {
        ?>
        <div class="reset-auth-topbar">
            <a class="reset-auth-logo" href="<?= auth_reset_e(app_url("index.php")) ?>" aria-label="ThauPhim home">
                <span class="reset-auth-logo__mark" aria-hidden="true"></span>
                <span class="reset-auth-logo__copy">
                    <span class="reset-auth-logo__text">Thau<strong>Phim</strong></span>
                    <span class="reset-auth-logo__tagline">Phim hay cả thau</span>
                </span>
            </a>

            <p class="reset-auth-register">
                <span>Don't have an account?</span>
                <a href="<?= auth_reset_e(app_url("index.php#authModal")) ?>" data-open-register>Register now</a>
            </p>
        </div>
        <?php
    }
}

if (!function_exists("auth_reset_render_illustration")) {
    function auth_reset_render_illustration(string $name): void
    {
        $name = in_array($name, ["forgot", "email", "mailbox", "success", "invalid"], true) ? $name : "forgot";
        ?>
        <div class="reset-auth-illustration reset-auth-illustration--<?= auth_reset_e($name) ?>" aria-hidden="true">
            <?php if ($name === "forgot"): ?>
                <svg viewBox="0 0 140 110" role="img" focusable="false">
                    <path d="M50 42h50l12 10-12 10H50z" />
                    <path d="M58 50h32M62 57h20" />
                    <path d="M50 42v44M38 86h66" />
                    <path d="M28 92c13-10 25 6 36-3 9-7 18-4 28 2" />
                    <path d="M96 74c10 1 18 4 24 10" />
                </svg>
            <?php elseif ($name === "email"): ?>
                <svg viewBox="0 0 140 110" role="img" focusable="false">
                    <path d="M28 42h62a9 9 0 0 1 9 9v35a9 9 0 0 1-9 9H28a9 9 0 0 1-9-9V51a9 9 0 0 1 9-9z" />
                    <path d="m21 51 38 27 38-27" />
                    <circle cx="95" cy="38" r="23" class="reset-auth-illustration__fill" />
                    <path d="m84 38 8 8 16-19" class="reset-auth-illustration__light" />
                    <path d="M116 23l8-9M122 38h12M111 18l2-12" />
                </svg>
            <?php elseif ($name === "mailbox"): ?>
                <svg viewBox="0 0 140 110" role="img" focusable="false">
                    <path d="M39 50c0-19 15-34 34-34h8c16 0 29 13 29 29v39H39z" />
                    <path d="M39 50h71M73 16v68M22 84h100" />
                    <path d="M50 50c0-12 10-22 23-22h2" />
                    <path d="M23 58h29l-10 17H13z" />
                    <path d="m23 58 15 10 14-10" />
                    <circle cx="25" cy="38" r="8" />
                    <path d="m22 38 3 3 6-7" />
                </svg>
            <?php elseif ($name === "success"): ?>
                <svg viewBox="0 0 140 110" role="img" focusable="false">
                    <circle cx="70" cy="50" r="32" class="reset-auth-illustration__fill" />
                    <path d="m54 49 12 13 22-28" class="reset-auth-illustration__light" />
                    <path d="M43 88h54M70 82v16M35 32c8-12 19-20 34-20M103 74c-7 11-17 18-31 20" />
                </svg>
            <?php else: ?>
                <svg viewBox="0 0 140 110" role="img" focusable="false">
                    <path d="M31 39h78a9 9 0 0 1 9 9v36a9 9 0 0 1-9 9H31a9 9 0 0 1-9-9V48a9 9 0 0 1 9-9z" />
                    <path d="m24 49 46 30 46-30" />
                    <circle cx="70" cy="30" r="19" class="reset-auth-illustration__fill" />
                    <path d="m62 22 16 16M78 22 62 38" class="reset-auth-illustration__light" />
                </svg>
            <?php endif; ?>
        </div>
        <?php
    }
}

if (!function_exists("auth_reset_render_errors")) {
    function auth_reset_render_errors(array $errors): void
    {
        if (empty($errors)) {
            return;
        }
        ?>
        <div class="reset-auth-alert reset-auth-alert--error" role="alert" data-server-errors>
            <?php foreach ($errors as $error): ?>
                <p><?= auth_reset_e($error) ?></p>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

if (!function_exists("auth_reset_render_toast_region")) {
    function auth_reset_render_toast_region(): void
    {
        ?>
        <div class="reset-auth-toast-region" aria-live="polite" aria-atomic="true" data-reset-toast-region></div>
        <?php
    }
}

