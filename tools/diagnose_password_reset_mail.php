<?php
require_once __DIR__ . "/../includes/password_reset.php";

if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

$email = "";
$sendResetEmail = false;
$showResetUrl = false;
foreach (array_slice($argv, 1) as $argument) {
    if ($argument === "--send") {
        $sendResetEmail = true;
        continue;
    }

    if ($argument === "--show-url") {
        $showResetUrl = true;
        continue;
    }

    if ($email === "") {
        $email = trim((string) $argument);
    }
}

$smtpPort = (int) SMTP_PORT;
$smtpEncryption = strtolower(trim((string) SMTP_ENCRYPTION));
$checks = [
    "mail_driver" => (string) MAIL_DRIVER,
    "mail_from" => (string) MAIL_FROM,
    "mail_from_name" => (string) MAIL_FROM_NAME,
    "smtp_host" => (string) SMTP_HOST,
    "smtp_port" => $smtpPort,
    "smtp_encryption" => $smtpEncryption !== "" ? $smtpEncryption : "(none)",
    "smtp_username" => (string) SMTP_USERNAME,
    "smtp_host_configured" => !mailer_is_placeholder((string) SMTP_HOST),
    "smtp_username_configured" => !mailer_is_placeholder((string) SMTP_USERNAME),
    "smtp_password_configured" => !mailer_is_placeholder((string) SMTP_PASSWORD),
    "phpmailer_available" => mailer_load_phpmailer(),
    "app_url" => (string) APP_URL,
    "send_reset_email" => $sendResetEmail,
    "show_reset_url" => $showResetUrl,
];

if ($email !== "") {
    try {
        $pdo = getDatabaseConnection();
        $checks["email_valid"] = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $activeUser = $checks["email_valid"] ? password_reset_find_active_user_by_email($pdo, $email) : null;
        $checks["active_user_found"] = $activeUser !== null;

        if ($sendResetEmail && $activeUser !== null) {
            if ($showResetUrl) {
                $tokenData = password_reset_create_token($pdo, (int) $activeUser["id"]);
                $resetUrl = password_reset_url((string) $tokenData["token"]);
                $checks["reset_user_found"] = true;
                $checks["reset_email_sent"] = password_reset_send_email($activeUser, $resetUrl);
                $checks["reset_url"] = $resetUrl;
                $checks["reset_expires_at"] = (string) $tokenData["expires_at"];
            } else {
                $result = password_reset_request($pdo, $email);
                $checks["reset_user_found"] = !empty($result["user_found"]);
                $checks["reset_email_sent"] = !empty($result["email_sent"]);
            }

            if (!$checks["reset_email_sent"] && mailer_last_error() !== "") {
                $checks["mailer_error"] = mailer_last_error();
            }
        }
    } catch (Throwable $exception) {
        $checks["database_error"] = $exception->getMessage();
    }
}

foreach ($checks as $name => $value) {
    if (is_bool($value)) {
        $value = $value ? "yes" : "no";
    }

    echo $name . ": " . $value . PHP_EOL;
}

if (!$checks["smtp_password_configured"]) {
    echo "action: set SMTP_PASSWORD in the environment or includes/config.local.php" . PHP_EOL;
}

if ($sendResetEmail && empty($checks["active_user_found"])) {
    echo "action: use an email that exists in users.email and has status active" . PHP_EOL;
}

if ($sendResetEmail && isset($checks["reset_email_sent"]) && !$checks["reset_email_sent"]) {
    echo "action: check the mailer_error above and the hosting PHP error log" . PHP_EOL;
}

if ($checks["mail_driver"] === "smtp" && $smtpPort === 587 && $smtpEncryption === "ssl") {
    echo "action: SMTP_PORT 587 should use SMTP_ENCRYPTION tls or empty, not ssl" . PHP_EOL;
}

if (!$checks["phpmailer_available"] && $checks["mail_driver"] === "smtp") {
    echo "action: run composer install so vendor/autoload.php provides PHPMailer" . PHP_EOL;
}
