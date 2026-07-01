<?php
require_once __DIR__ . "/includes/password_reset.php";
require_once __DIR__ . "/includes/auth_ui.php";
require_once __DIR__ . "/includes/auth_reset_ui.php";

auth_start_session();

$pdo = getDatabaseConnection();
$currentUser = auth_current_user($pdo);

if ($currentUser !== null) {
    header("Location: " . app_url("pages/account.php"));
    exit;
}

$errors = [];
$notice = "";
$oldEmail = "";
$sentEmail = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $oldEmail = trim((string) ($_POST["email"] ?? ""));

    if ($oldEmail === "") {
        $errors[] = "Email is required";
    } elseif (!filter_var($oldEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address";
    }

    if (empty($errors)) {
        try {
            $sentEmail = $oldEmail;
            $resetRequest = password_reset_request($pdo, $oldEmail);

            if (
                defined("APP_DEBUG")
                && APP_DEBUG
                && !empty($resetRequest["user_found"])
                && empty($resetRequest["email_sent"])
            ) {
                $errors[] = "Password reset email could not be sent. Check SMTP configuration.";
            } else {
                $notice = "If the email exists, a password reset link has been sent.";
                $oldEmail = "";
            }
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $sentEmail = $oldEmail;
            $notice = "If the email exists, a password reset link has been sent.";
        }
    }
}

$showEmailState = $notice !== "";
$bodyClass = "reset-auth-body";
$pageStyles = ["assets/css/auth-reset.css"];
$pageScripts = ["assets/js/auth-reset.js"];
?>
<?php include __DIR__ . "/includes/header.php"; ?>

<main class="reset-auth-page" data-reset-flow>
    <?php auth_reset_render_topbar(); ?>
    <?php auth_reset_render_toast_region(); ?>

    <section class="reset-auth-card<?= $showEmailState ? " reset-auth-card--success" : "" ?>" aria-labelledby="resetFlowTitle">
        <?php if ($showEmailState): ?>
            <?php auth_reset_render_illustration("email"); ?>

            <div class="reset-auth-copy">
                <h1 id="resetFlowTitle">Check your email</h1>
                <p>
                    We've sent a password reset link to:
                    <strong><?= auth_reset_e($sentEmail) ?></strong>
                </p>
            </div>

            <a
                class="reset-auth-submit reset-auth-submit--inbox"
                href="#"
                target="_blank"
                rel="noopener"
                data-webmail-link
                data-email="<?= auth_reset_e($sentEmail) ?>">
                <span>Open Email Inbox</span>
                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
            </a>

            <form
                action="<?= auth_reset_e(app_url("forgot-password.php")) ?>"
                method="post"
                class="reset-resend-form"
                data-reset-form="resend"
                data-resend-cooldown="30"
                novalidate>
                <input type="hidden" name="email" value="<?= auth_reset_e($sentEmail) ?>">
                <p class="reset-auth-muted">Didn't receive it?</p>
                <button type="submit" class="reset-auth-link-button" data-submit-button>
                    <span data-button-text>Resend Email</span>
                    <span class="reset-auth-spinner" aria-hidden="true"></span>
                </button>
            </form>
        <?php else: ?>
            <?php auth_reset_render_illustration("forgot"); ?>

            <form
                action="<?= auth_reset_e(app_url("forgot-password.php")) ?>"
                method="post"
                class="reset-auth-form"
                data-reset-form="forgot"
                novalidate>
                <div class="reset-auth-copy">
                    <h1 id="resetFlowTitle">Forgot your password?</h1>
                    <p>Enter your email and we'll send you a password reset link.</p>
                </div>

                <?php auth_reset_render_errors($errors); ?>

                <div class="reset-auth-field" data-field="email">
                    <label for="resetEmail">Email</label>
                    <div class="reset-auth-input-wrap">
                        <input
                            id="resetEmail"
                            type="email"
                            name="email"
                            placeholder="e.g. username@example.com"
                            autocomplete="email"
                            value="<?= auth_reset_e($oldEmail) ?>"
                            aria-describedby="resetEmailError"
                            data-email-input
                            data-autofocus
                            required>
                        <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                    </div>
                    <p class="reset-auth-error" id="resetEmailError" data-field-error aria-live="polite"></p>
                </div>

                <button type="submit" class="reset-auth-submit" data-submit-button>
                    <span data-button-text>Send Reset Link</span>
                    <span class="reset-auth-spinner" aria-hidden="true"></span>
                </button>
                <span class="reset-auth-loading-line" aria-hidden="true"></span>

                <p class="reset-auth-back">
                    <a href="<?= auth_reset_e(app_url("index.php#authModal")) ?>" data-open-login>
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                        <span>Back to Login</span>
                    </a>
                </p>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . "/includes/footer.php"; ?>
