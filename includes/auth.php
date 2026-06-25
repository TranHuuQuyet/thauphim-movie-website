<?php
require_once __DIR__ . "/db.php";

function auth_start_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function auth_store_user_session(array $user): void
{
    auth_start_session();

    $_SESSION["is_login"] = true;
    $_SESSION["user_id"] = (int) $user["id"];
    $_SESSION["username"] = (string) $user["username"];
    $_SESSION["email"] = (string) $user["email"];
    $_SESSION["role"] = (string) $user["role"];
    $_SESSION["membership"] = (string) $user["membership"];
    $_SESSION["status"] = (string) $user["status"];
}

function auth_clear_user_session(): void
{
    auth_start_session();

    unset(
        $_SESSION["is_login"],
        $_SESSION["user_id"],
        $_SESSION["username"],
        $_SESSION["email"],
        $_SESSION["role"],
        $_SESSION["membership"],
        $_SESSION["status"]
    );
}

function auth_find_login_user(PDO $pdo, string $login): ?array
{
    $stmt = $pdo->prepare("
        SELECT id, username, email, password_hash, role, membership, status
        FROM users
        WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)
        LIMIT 1
    ");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function auth_current_user(?PDO $pdo = null): ?array
{
    auth_start_session();

    if (empty($_SESSION["is_login"])) {
        return null;
    }

    $pdo = $pdo ?: getDatabaseConnection();
    $userId = isset($_SESSION["user_id"]) ? (int) $_SESSION["user_id"] : 0;
    $username = trim((string) ($_SESSION["username"] ?? ""));

    if ($userId > 0) {
        $stmt = $pdo->prepare("
            SELECT id, username, email, role, membership, status
            FROM users
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$userId]);
    } elseif ($username !== "") {
        $stmt = $pdo->prepare("
            SELECT id, username, email, role, membership, status
            FROM users
            WHERE LOWER(username) = LOWER(?)
            LIMIT 1
        ");
        $stmt->execute([$username]);
    } else {
        auth_clear_user_session();
        return null;
    }

    $user = $stmt->fetch();

    if (!$user || $user["status"] !== "active") {
        auth_clear_user_session();
        return null;
    }

    auth_store_user_session($user);

    return $user;
}

function auth_is_admin(?array $user): bool
{
    return $user !== null && ($user["role"] ?? "") === "admin";
}

function auth_can_watch_movie(array $movie, ?array $user): array
{
    if ($user !== null && ($user["status"] ?? "") !== "active") {
        return [
            "allowed" => false,
            "code" => "locked",
            "message" => "Tai khoan dang bi khoa.",
        ];
    }

    if (empty($movie["is_premium"])) {
        return [
            "allowed" => true,
            "code" => "free",
            "message" => "",
        ];
    }

    if ($user === null) {
        return [
            "allowed" => false,
            "code" => "login_required",
            "message" => "Vui long dang nhap de xem phim Premium.",
        ];
    }

    if (($user["membership"] ?? "free") !== "premium") {
        return [
            "allowed" => false,
            "code" => "premium_required",
            "message" => "Tai khoan Free chua the xem phim Premium.",
        ];
    }

    return [
        "allowed" => true,
        "code" => "premium",
        "message" => "",
    ];
}

