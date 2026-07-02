<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/mailer.php";

function password_reset_ttl_minutes(): int
{
    $minutes = defined("PASSWORD_RESET_TTL_MINUTES") ? (int) PASSWORD_RESET_TTL_MINUTES : 60;

    return max(5, $minutes);
}

function password_reset_hash_token(string $token): string
{
    return hash("sha256", $token);
}

function password_reset_find_active_user_by_email(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare("
        SELECT id, username, email, status
        FROM users
        WHERE LOWER(email) = LOWER(?)
          AND status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function password_reset_create_token(PDO $pdo, int $userId): array
{
    $token = bin2hex(random_bytes(32));
    $tokenHash = password_reset_hash_token($token);
    $ttlMinutes = password_reset_ttl_minutes();

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->query("SELECT DATE_ADD(NOW(), INTERVAL " . $ttlMinutes . " MINUTE) AS expires_at");
        $expiresAt = (string) $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            UPDATE password_resets
            SET used_at = NOW()
            WHERE user_id = ?
              AND used_at IS NULL
        ");
        $stmt->execute([$userId]);

        $stmt = $pdo->prepare("
            INSERT INTO password_resets (user_id, token_hash, expires_at)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $tokenHash, $expiresAt]);

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }

    return [
        "token" => $token,
        "expires_at" => $expiresAt,
    ];
}

function password_reset_url(string $token): string
{
    return app_absolute_url("reset-password.php?token=" . rawurlencode($token));
}

function password_reset_request(PDO $pdo, string $email): array
{
    $user = password_reset_find_active_user_by_email($pdo, $email);

    if ($user === null) {
        return [
            "user_found" => false,
            "email_sent" => false,
        ];
    }

    $tokenData = password_reset_create_token($pdo, (int) $user["id"]);
    $resetUrl = password_reset_url((string) $tokenData["token"]);
    $emailSent = password_reset_send_email($user, $resetUrl);

    if (!$emailSent) {
        error_log("Password reset email was not sent for user id " . (int) $user["id"] . ".");
    }

    return [
        "user_found" => true,
        "email_sent" => $emailSent,
    ];
}

function password_reset_send_email(array $user, string $resetUrl): bool
{
    $username = trim((string) ($user["username"] ?? ""));
    $email = trim((string) ($user["email"] ?? ""));
    $displayName = $username !== "" ? $username : $email;
    $ttl = password_reset_ttl_minutes();
    $escapedName = htmlspecialchars($displayName, ENT_QUOTES, "UTF-8");
    $escapedUrl = htmlspecialchars($resetUrl, ENT_QUOTES, "UTF-8");
    $fromName = defined("MAIL_FROM_NAME") ? (string) MAIL_FROM_NAME : "ThauPhim";
    $subject = "Đặt lại mật khẩu " . $fromName;

    $htmlBody = "
        <div style=\"font-family:Arial,sans-serif;line-height:1.6;color:#1f2937\">
            <h2>Đặt lại mật khẩu</h2>
            <p>Xin chào " . $escapedName . ",</p>
            <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản ThauPhim của bạn.</p>
            <p>
                <a href=\"" . $escapedUrl . "\" style=\"display:inline-block;padding:12px 18px;background:#111827;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:700\">
                    Đặt lại mật khẩu
                </a>
            </p>
            <p>Link này sẽ hết hạn sau " . $ttl . " phút và chỉ dùng được một lần.</p>
            <p>Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này.</p>
            <p style=\"font-size:13px;color:#6b7280\">Nếu nút trên không hoạt động, hãy copy link này vào trình duyệt:<br>" . $escapedUrl . "</p>
        </div>
    ";

    $textBody = "Xin chào " . $displayName . ",\n\n"
        . "Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản ThauPhim của bạn.\n"
        . "Mở link sau để đặt lại mật khẩu: " . $resetUrl . "\n\n"
        . "Link này sẽ hết hạn sau " . $ttl . " phút và chỉ dùng được một lần.\n"
        . "Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này.";

    return mailer_send($email, $displayName, $subject, $htmlBody, $textBody);
}

function password_reset_find_valid_token(PDO $pdo, string $token): ?array
{
    $token = trim($token);
    if ($token === "") {
        return null;
    }

    $stmt = $pdo->prepare("
        SELECT pr.id, pr.user_id, pr.expires_at, u.username, u.email
        FROM password_resets pr
        INNER JOIN users u ON u.id = pr.user_id
        WHERE pr.token_hash = ?
          AND pr.used_at IS NULL
          AND pr.expires_at > NOW()
          AND u.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([password_reset_hash_token($token)]);
    $reset = $stmt->fetch();

    return $reset ?: null;
}

function password_reset_consume(PDO $pdo, string $token, string $password): bool
{
    $tokenHash = password_reset_hash_token(trim($token));

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("
            SELECT pr.id, pr.user_id
            FROM password_resets pr
            INNER JOIN users u ON u.id = pr.user_id
            WHERE pr.token_hash = ?
              AND pr.used_at IS NULL
              AND pr.expires_at > NOW()
              AND u.status = 'active'
            LIMIT 1
            FOR UPDATE
        ");
        $stmt->execute([$tokenHash]);
        $reset = $stmt->fetch();

        if (!$reset) {
            $pdo->rollBack();
            return false;
        }

        $userId = (int) $reset["user_id"];

        $stmt = $pdo->prepare("
            UPDATE users
            SET password_hash = ?
            WHERE id = ?
        ");
        $stmt->execute([
            password_hash($password, PASSWORD_DEFAULT),
            $userId,
        ]);

        $stmt = $pdo->prepare("
            UPDATE password_resets
            SET used_at = NOW()
            WHERE user_id = ?
              AND used_at IS NULL
        ");
        $stmt->execute([$userId]);

        $pdo->commit();
        return true;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log($exception->getMessage());
        return false;
    }
}
