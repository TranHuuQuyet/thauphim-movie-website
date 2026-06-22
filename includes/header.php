<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ThauPhim</title>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');
    </style>
    <link rel="icon" type="image/png" sizes="512x512" href="assets/images/favicon-tab.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/reponsive.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="index.php" aria-label="ThauPhim trang chủ">
                <span class="brand-icon" aria-hidden="true">
                    <img src="assets/images/favicon.png" alt="">
                </span>
                <span class="brand-copy">
                    <span class="brand-title">Thau<strong>Phim</strong></span>
                    <span class="brand-tagline">Phim hay cả thau</span>
                </span>
            </a>

            <button class="menu-toggle" type="button" aria-label="Mở menu" aria-controls="primary-menu"
                aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="header-actions" id="primary-menu">
                <nav class="main-nav" aria-label="Điều hướng chính">
                    <a href="pages/browse.php?genre=all">Chủ đề</a>
                    <a href="pages/browse.php">Bộ lọc</a>
                    <a href="pages/browse.php?type=movie">Phim lẻ</a>
                    <a href="pages/browse.php?type=series">Phim bộ</a>
                    <a href="pages/country.php">Quốc gia</a>
                    <a href="pages/actor.php">Diễn viên</a>
                    <a href="pages/schedule.php">Lịch chiếu</a>
                </nav>

                <form class="search-form" action="pages/browse.php" method="get" role="search">
                    <label class="sr-only" for="header-search">Tìm phim,diễn viên...</label>
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input id="header-search" name="q" type="search" placeholder="Tìm phim..." autocomplete="off">
                </form>

                <?php if(isset($_SESSION["is_login"])): ?>
                <a class="member-button" href="logout.php">
                    <?= $_SESSION["username"] ?> | Đăng xuất
                </a>
                <?php else: ?>
                <a class="member-button" href="#" id="openLogin">
                    Đăng nhập
                </a>
                <?php endif; ?>
                <button id="themeToggle" class="theme-toggle">
                    <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960">
                        <path
                            d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Z" />
                    </svg>

                    <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960">
                        <path
                            d="M565-395q35-35 35-85t-35-85q-35-35-85-35t-85 35q-35 35-35 85t35 85q35 35 85 35t85-35Zm-226.5 56.5Q280-397 280-480t58.5-141.5Q397-680 480-680t141.5 58.5Q680-563 680-480t-58.5 141.5Q563-280 480-280t-141.5-58.5ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Z" />
                    </svg>
                </button>
            </div>
        </div>
    </header>