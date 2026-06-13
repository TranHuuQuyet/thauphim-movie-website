<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ThauPhim</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/reponsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="index.php" aria-label="ThauPhim trang chủ">
                <span class="brand-icon" aria-hidden="true">
                    <i class="fa-solid fa-clapperboard"></i>
                </span>
                <span class="brand-copy">
                    <span class="brand-title">ThauPhim</span>
                    <span class="brand-tagline">Phim hay cả thau</span>
                </span>
            </a>

            <button class="menu-toggle" type="button" aria-label="Mở menu" aria-controls="primary-menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="header-actions" id="primary-menu">
                <nav class="main-nav" aria-label="Điều hướng chính">
                    <a href="pages/browse.php?genre=all">Chủ đề</a>
                    <a href="pages/browse.php">Duyệt tìm</a>
                    <a href="pages/browse.php?type=movie">Phim lẻ</a>
                    <a href="pages/browse.php?type=series">Phim bộ</a>
                    <a href="pages/country.php">Quốc gia</a>
                    <a href="pages/actor.php">Diễn viên</a>
                    <a href="pages/schedule.php">Lịch chiếu</a>
                </nav>

                <form class="search-form" action="pages/browse.php" method="get" role="search">
                    <label class="sr-only" for="header-search">Tìm phim</label>
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input id="header-search" name="q" type="search" placeholder="Tìm phim..." autocomplete="off">
                </form>

                <a class="member-button" href="login.php">Thành viên</a>
            </div>
        </div>
    </header>
