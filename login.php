<?php
session_start();
    $username_db = "Admin";
    $password_db = password_hash("abc123",PASSWORD_DEFAULT);

    $errors=[];
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

if ($username === "") {
    $errors[] = "Username không được trống";
} elseif ($username !== $username_db) {
    $errors[] = "Username không đúng";
}
if ($password === "") {
    $errors[] = "Mật khẩu không được trống";
} elseif (!password_verify($password, $password_db)) {
    $errors[] = "Mật khẩu không đúng";
}
if (!empty($errors)) {
    $_SESSION["login_errors"] = $errors;
    header("Location: index.php");
    exit;
}
$_SESSION["username"] = $username_db;
$_SESSION["is_login"] = true;

header("Location: index.php");
exit;
?>