<?php
require_once __DIR__ . "/../includes/password_reset.php";

if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

$email = trim((string) ($argv[1] ?? ""));
$checks = [
    "mail_driver" => (string) MAIL_DRIVER,
    "smtp_host_configured" => !mailer_is_placeholder((string) SMTP_HOST),
    "smtp_username_configured" => !mailer_is_placeholder((string) SMTP_USERNAME),
    "smtp_password_configured" => !mailer_is_placeholder((string) SMTP_PASSWORD),
    "phpmailer_available" => mailer_load_phpmailer(),
    "app_url" => (string) APP_URL,
];

if ($email !== "") {
    try {
        $pdo = getDatabaseConnection();
        $checks["email_valid"] = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $checks["active_user_found"] = password_reset_find_active_user_by_email($pdo, $email) !== null;
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

if (!$checks["phpmailer_available"] && $checks["mail_driver"] === "smtp") {
    echo "action: run composer install so vendor/autoload.php provides PHPMailer" . PHP_EOL;
}
