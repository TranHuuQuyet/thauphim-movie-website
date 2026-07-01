<?php
require_once __DIR__ . "/includes/password_reset.php";
require_once __DIR__ . "/includes/auth_ui.php";
require_once __DIR__ . "/includes/auth_reset_ui.php";

auth_start_session();

$pdo = getDatabaseConnection();
$errors = [];
$token = trim((string) ($_GET["token"] ?? $_POST["token"] ?? ""));
$password = "";
$passwordConfirm = "";
$reset = $token !== "" ? password_reset_find_valid_token($pdo, $token) : null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = (string) ($_POST["password"] ?? "");
    $passwordConfirm = (string) ($_POST["password_confirm"] ?? "");

    if ($reset === null) {
        $errors[] = "This password reset link is invalid or has expired";
    }

    if ($password === "") {
        $errors[] = "New password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "New password must be at least 6 characters";
    }

    if ($password !== $passwordConfirm) {
        $errors[] = "Passwords do not match";
    }

    if (empty($errors)) {
        if (password_reset_consume($pdo, $token, $password)) {
            auth_clear_user_session();
            $_SESSION["login_notice"] = "Password changed. Please log in again.";

            header("Location: " . app_url("index.php#authModal"));
            exit;
        }

        $reset = null;
        $errors[] = "This password reset link is invalid or has expired";
    }
}

$canReset = $reset !== null;
$bodyClass = "reset-auth-body";
$pageStyles = ["assets/css/auth-reset.css"];
$pageScripts = ["assets/js/auth-reset.js"];
?>
<?php include __DIR__ . "/includes/header.php"; ?>

<main class="reset-auth-page" data-reset-flow>
    <?php auth_reset_render_topbar(); ?>
    <?php auth_reset_render_toast_region(); ?>

    <section class="reset-auth-card" aria-labelledby="resetFlowTitle">
        <?php if (!$canReset): ?>
            <?php auth_reset_render_illustration("invalid"); ?>

            <div class="reset-auth-copy">
                <h1 id="resetFlowTitle">Reset link expired</h1>
                <p>This link is invalid, expired, or already used. Request a fresh reset link to continue.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <?php auth_reset_render_errors($errors); ?>
            <?php else: ?>
                <div class="reset-auth-alert reset-auth-alert--error" role="alert" data-server-errors>
                    <p>This password reset link is invalid or has expired.</p>
                </div>
            <?php endif; ?>

            <a class="reset-auth-submit" href="<?= auth_reset_e(app_url("forgot-password.php")) ?>">
                <span>Request New Link</span>
            </a>

            <p class="reset-auth-back">
                <a href="<?= auth_reset_e(app_url("index.php#authModal")) ?>" data-open-login>
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    <span>Back to Login</span>
                </a>
            </p>
        <?php else: ?>
            <?php auth_reset_render_illustration("mailbox"); ?>

            <form
                action="<?= auth_reset_e(app_url("reset-password.php")) ?>"
                method="post"
                class="reset-auth-form"
                data-reset-form="password"
                data-success-state="#passwordChangedState"
                novalidate>
                <div class="reset-auth-copy">
                    <h1 id="resetFlowTitle">Reset Password</h1>
                    <p>Please create a new password for your account.</p>
                </div>

                <?php auth_reset_render_errors($errors); ?>

                <input type="hidden" name="token" value="<?= auth_reset_e($token) ?>">

                <div class="reset-auth-field" data-field="password">
                    <label for="newPassword">New Password</label>
                    <div class="reset-auth-input-wrap">
                        <input
                            id="newPassword"
                            type="password"
                            name="password"
                            placeholder="Enter a new password"
                            autocomplete="new-password"
                            aria-describedby="passwordHelp passwordError"
                            data-password-input
                            data-autofocus
                            required>
                        <button type="button" class="reset-auth-toggle" data-toggle-password aria-label="Show password">
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <p class="reset-auth-error" id="passwordError" data-field-error aria-live="polite"></p>

                    <div class="reset-strength" id="passwordHelp" data-strength-meter>
                        <div class="reset-strength__track" aria-hidden="true">
                            <span data-strength-bar></span>
                        </div>
                        <p>Password strength: <strong data-strength-label>Weak</strong></p>
                    </div>

                    <ul class="reset-password-rules" aria-label="Password requirements">
                        <li data-password-rule="length"><i class="fa-solid fa-check" aria-hidden="true"></i>At least 8 characters</li>
                        <li data-password-rule="uppercase"><i class="fa-solid fa-check" aria-hidden="true"></i>One uppercase letter</li>
                        <li data-password-rule="number"><i class="fa-solid fa-check" aria-hidden="true"></i>One number</li>
                        <li data-password-rule="special"><i class="fa-solid fa-check" aria-hidden="true"></i>One special character</li>
                    </ul>
                </div>

                <div class="reset-auth-field" data-field="confirm">
                    <label for="newPasswordConfirm">Confirm Password</label>
                    <div class="reset-auth-input-wrap">
                        <input
                            id="newPasswordConfirm"
                            type="password"
                            name="password_confirm"
                            placeholder="Re-enter your new password"
                            autocomplete="new-password"
                            aria-describedby="confirmPasswordError"
                            data-confirm-input
                            required>
                        <button type="button" class="reset-auth-toggle" data-toggle-password aria-label="Show password">
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <p class="reset-auth-error" id="confirmPasswordError" data-field-error aria-live="polite"></p>
                </div>

                <button type="submit" class="reset-auth-submit" data-submit-button>
                    <span data-button-text>Reset Password</span>
                    <span class="reset-auth-spinner" aria-hidden="true"></span>
                </button>
                <span class="reset-auth-loading-line" aria-hidden="true"></span>
            </form>

            <div class="reset-auth-success-state" id="passwordChangedState" data-password-success-state hidden>
                <?php auth_reset_render_illustration("success"); ?>
                <div class="reset-auth-copy">
                    <h1>Password Changed!</h1>
                    <p>Your password has been updated successfully.</p>
                </div>
                <a class="reset-auth-submit" href="<?= auth_reset_e(app_url("index.php#authModal")) ?>">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    <span>Back to Login</span>
                </a>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . "/includes/footer.php"; ?>
