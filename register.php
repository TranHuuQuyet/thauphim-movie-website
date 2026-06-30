<?php
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/auth_ui.php";

auth_start_session();

$pdo = getDatabaseConnection();
$currentUser = auth_current_user($pdo);

if ($currentUser !== null) {
    header("Location: pages/account.php");
    exit;
}

$registerErrors = [];
$registerOld = [
    "username" => "",
    "email" => "",
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim((string) ($_POST["username"] ?? ""));
    $email = trim((string) ($_POST["email"] ?? ""));
    $password = (string) ($_POST["password"] ?? "");
    $passwordConfirm = (string) ($_POST["password_confirm"] ?? "");

    $registerOld = [
        "username" => $username,
        "email" => $email,
    ];

    if ($username === "") {
        $registerErrors[] = "Ten dang nhap khong duoc trong";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $registerErrors[] = "Ten dang nhap can tu 3 den 50 ky tu";
    } elseif (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
        $registerErrors[] = "Ten dang nhap chi duoc gom chu, so va dau gach duoi";
    }

    if ($email === "") {
        $registerErrors[] = "Email khong duoc trong";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerErrors[] = "Email khong hop le";
    }

    if ($password === "") {
        $registerErrors[] = "Mat khau khong duoc trong";
    } elseif (strlen($password) < 6) {
        $registerErrors[] = "Mat khau can toi thieu 6 ky tu";
    }

    if ($password !== $passwordConfirm) {
        $registerErrors[] = "Nhap lai mat khau khong khop";
    }

    if (empty($registerErrors)) {
        try {
            $stmt = $pdo->prepare("
                SELECT username, email
                FROM users
                WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)
                LIMIT 1
            ");
            $stmt->execute([$username, $email]);
            $existingUser = $stmt->fetch();

            if ($existingUser) {
                if (strtolower((string) $existingUser["username"]) === strtolower($username)) {
                    $registerErrors[] = "Ten dang nhap da ton tai";
                }

                if (strtolower((string) $existingUser["email"]) === strtolower($email)) {
                    $registerErrors[] = "Email da ton tai";
                }
            }
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $registerErrors[] = "Khong the kiem tra tai khoan luc nay";
        }
    }

    if (empty($registerErrors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, role, membership, status)
                VALUES (?, ?, ?, 'user', 'free', 'active')
            ");
            $stmt->execute([
                $username,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
            ]);

            $userId = (int) $pdo->lastInsertId();
            $stmt = $pdo->prepare("
                SELECT id, username, email, role, membership, status
                FROM users
                WHERE id = ?
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if ($user) {
                auth_store_user_session($user);
                header("Location: pages/account.php");
                exit;
            }

            $registerErrors[] = "Khong the tao phien dang nhap";
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $registerErrors[] = "Khong the dang ky luc nay";
        }
    }
}
?>
<?php include __DIR__ . "/includes/header.php"; ?>

<main class="page-shell auth-page">
    <?php
    auth_ui_render_surface([
        "context" => "page",
        "mode" => "register",
        "id_suffix" => "page",
        "register_errors" => $registerErrors,
        "register_old" => $registerOld,
        "pdo" => $pdo,
    ]);
    ?>
</main>

<?php include __DIR__ . "/includes/footer.php"; ?>
