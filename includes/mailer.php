<?php
require_once __DIR__ . "/config.php";

function mailer_config(string $name, $default = "")
{
    return defined($name) ? constant($name) : $default;
}

function mailer_is_placeholder(string $value): bool
{
    $value = trim($value);

    return $value === "" || str_starts_with($value, "replace-with-");
}

function mailer_load_phpmailer(): bool
{
    if (class_exists("\\PHPMailer\\PHPMailer\\PHPMailer")) {
        return true;
    }

    $autoload = dirname(__DIR__) . "/vendor/autoload.php";
    if (is_file($autoload)) {
        require_once $autoload;
    }

    return class_exists("\\PHPMailer\\PHPMailer\\PHPMailer");
}

function mailer_send(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody): bool
{
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        error_log("Cannot send email to invalid address.");
        return false;
    }

    $driver = strtolower(trim((string) mailer_config("MAIL_DRIVER", "mail")));

    if ($driver === "smtp") {
        return mailer_send_smtp($toEmail, $toName, $subject, $htmlBody, $textBody);
    }

    if ($driver === "mail") {
        return mailer_send_mail($toEmail, $toName, $subject, $htmlBody, $textBody);
    }

    error_log("Unsupported MAIL_DRIVER: " . $driver);
    return false;
}

function mailer_send_smtp(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody): bool
{
    $host = trim((string) mailer_config("SMTP_HOST"));
    $username = trim((string) mailer_config("SMTP_USERNAME"));
    $password = (string) mailer_config("SMTP_PASSWORD");

    if (mailer_is_placeholder($host) || mailer_is_placeholder($username) || mailer_is_placeholder($password)) {
        error_log("SMTP mail is not fully configured. Update SMTP_HOST, SMTP_USERNAME, and SMTP_PASSWORD.");
        return false;
    }

    if (!mailer_load_phpmailer()) {
        error_log("MAIL_DRIVER=smtp requires PHPMailer. Install PHPMailer or set MAIL_DRIVER to mail if hosting supports PHP mail().");
        return false;
    }

    $phpMailerClass = "\\PHPMailer\\PHPMailer\\PHPMailer";
    $mail = new $phpMailerClass(true);

    try {
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = (int) mailer_config("SMTP_PORT", 465);
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->CharSet = "UTF-8";

        $encryption = strtolower(trim((string) mailer_config("SMTP_ENCRYPTION", "ssl")));
        if ($encryption !== "") {
            $mail->SMTPSecure = $encryption;
        }

        $fromEmail = (string) mailer_config("MAIL_FROM", $username);
        $fromName = (string) mailer_config("MAIL_FROM_NAME", "ThauPhim");

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail, $toName !== "" ? $toName : $toEmail);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody;

        return $mail->send();
    } catch (Throwable $exception) {
        error_log("SMTP mail failed: " . $exception->getMessage());
        return false;
    }
}

function mailer_send_mail(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody): bool
{
    $fromEmail = (string) mailer_config("MAIL_FROM", "");
    $fromName = (string) mailer_config("MAIL_FROM_NAME", "ThauPhim");

    if (mailer_is_placeholder($fromEmail) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        error_log("PHP mail() is not configured with a valid MAIL_FROM address.");
        return false;
    }

    $encodedSubject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
    $headers = [
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=UTF-8",
        "From: " . mailer_encode_header($fromName) . " <" . $fromEmail . ">",
        "Reply-To: " . $fromEmail,
    ];

    $sent = @mail($toEmail, $encodedSubject, $htmlBody, implode("\r\n", $headers));
    if (!$sent) {
        error_log("PHP mail() failed while sending password reset email.");
    }

    return $sent;
}

function mailer_encode_header(string $value): string
{
    if (preg_match('/[^\x20-\x7E]/', $value)) {
        return "=?UTF-8?B?" . base64_encode($value) . "?=";
    }

    return str_replace(["\r", "\n"], "", $value);
}
