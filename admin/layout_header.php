<?php
require_once __DIR__ . '/_helpers.php';

$pageTitle = $pageTitle ?? 'ThauPhim Admin';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= admin_e($pageTitle) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= admin_e(app_url('assets/css/admin.css')) ?>">
</head>
<body>
<header class="admin-header">
    <a class="admin-brand" href="<?= admin_e(admin_url('dashboard.php')) ?>">
        <img class="admin-brand__logo" src="<?= admin_e(app_url('assets/images/favicon.png')) ?>" alt="">
        <span>Thau<strong>Phim</strong> Admin</span>
    </a>
    <div class="admin-header__user">
        <span><?= admin_e($adminUser['username'] ?? 'admin') ?></span>
        <a href="<?= admin_e(app_url('logout.php')) ?>">Logout</a>
    </div>
</header>
<div class="admin-container">
