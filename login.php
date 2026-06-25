<?php
require_once __DIR__ . "/includes/auth.php";

auth_start_session();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php#authModal");
    exit;
}

$errors = [];
$login = trim((string) ($_POST["username"] ?? ""));
$password = (string) ($_POST["password"] ?? "");

if ($login === "") {
    $errors[] = "Username hoac email khong duoc trong";
}

if ($password === "") {
    $errors[] = "Mat khau khong duoc trong";
}

if (empty($errors)) {
    try {
        $pdo = getDatabaseConnection();
        $user = auth_find_login_user($pdo, $login);

        if (!$user || !password_verify($password, (string) $user["password_hash"])) {
            $errors[] = "Thong tin dang nhap khong dung";
        } elseif ($user["status"] !== "active") {
            $errors[] = "Tai khoan dang bi khoa";
        } else {
            $stmt = $pdo->prepare("
                UPDATE users
                SET last_login_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([(int) $user["id"]]);

            auth_store_user_session($user);

            header("Location: index.php");
            exit;
        }
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        $errors[] = "Khong the dang nhap luc nay";
    }
}

$_SESSION["login_errors"] = $errors;
$_SESSION["login_old"] = [
    "username" => $login,
];

header("Location: index.php#authModal");
exit;

