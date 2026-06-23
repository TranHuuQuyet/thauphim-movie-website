<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/upcoming_notifications.php";

$tmdbCountries = $TMDB_COUNTRIES ?? [];
$isMember = !empty($_SESSION["is_login"]);
$upcomingNotifications = get_published_upcoming_notifications();
$notificationCount = count($upcomingNotifications);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ThauPhim</title>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');
    </style>
    <link rel="icon" type="image/png" sizes="512x512" href="/thauphim-movie-website/assets/images/favicon-tab.png">

    <link rel="stylesheet" href="/thauphim-movie-website/assets/css/style.css">
    <link rel="stylesheet" href="/thauphim-movie-website/assets/css/reponsive.css">
    <link rel="stylesheet" href="/thauphim-movie-website/assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css">
    <script src="/thauphim-movie-website/assets/js/auth_login.js" defer></script>
    <script src="/thauphim-movie-website/assets/js/notifications.js" defer></script>
    <script src="/thauphim-movie-website/assets/js/account-menu.js" defer></script>
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="/thauphim-movie-website/index.php" aria-label="ThauPhim trang chủ">
                <span class="brand-icon" aria-hidden="true">
                    <img src="/thauphim-movie-website/assets/images/favicon.png" alt="">
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
                    <a href="/thauphim-movie-website/pages/browse.php?genre=all">Chủ đề</a>
                    <a href="/thauphim-movie-website/pages/browse.php">Bộ lọc</a>
                    <a href="/thauphim-movie-website/pages/browse.php?type=movie">Phim lẻ</a>
                    <a href="/thauphim-movie-website/pages/browse.php?type=series">Phim bộ</a>
                    <details class="nav-dropdown">
                        <summary>
                            Quốc gia
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </summary>
                        <div class="nav-dropdown-menu">
                            <?php foreach ($tmdbCountries as $country): ?>
                            <a
                                href="/thauphim-movie-website/pages/country.php?code=<?= urlencode($country['code']) ?>">
                                <?= htmlspecialchars($country['name'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </details>
                    <a href="/thauphim-movie-website/pages/actor.php">Diễn viên</a>
                </nav>

                <form class="search-form" action="/thauphim-movie-website/pages/browse.php" method="get" role="search">
                    <label class="sr-only" for="header-search">Tìm phim,diễn viên...</label>
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input id="header-search" name="q" type="search" placeholder="Tìm phim..." autocomplete="off">
                </form>

                <div class="header-icon-actions">
                    <button id="themeToggle" class="theme-toggle" type="button" aria-label="Chuyển giao diện sáng tối">
                        <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960"
                            aria-hidden="true">
                            <path
                                d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Z" />
                        </svg>

                        <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960"
                            aria-hidden="true">
                            <path
                                d="M565-395q35-35 35-85t-35-85q-35-35-85-35t-85 35q-35 35-35 85t35 85q35 35 85 35t85-35Zm-226.5 56.5Q280-397 280-480t58.5-141.5Q397-680 480-680t141.5 58.5Q680-563 680-480t-58.5 141.5Q563-280 480-280t-141.5-58.5ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Z" />
                        </svg>
                    </button>

                    <div class="notification-wrapper" data-notification-root>
                        <button id="notificationToggle" class="notification-toggle" type="button"
                            aria-label="Thông báo phim sắp chiếu" aria-haspopup="dialog" aria-expanded="false"
                            aria-controls="notificationPanel" data-notification-toggle>
                            <i class="fa-regular fa-bell" aria-hidden="true"></i>
                            <?php if ($isMember && $notificationCount > 0): ?>
                            <span class="notification-badge" aria-label="<?= $notificationCount ?> thông báo">
                                <?= $notificationCount > 9 ? "9+" : $notificationCount ?>
                            </span>
                            <?php endif; ?>
                        </button>

                        <div class="notification-panel" id="notificationPanel" role="dialog"
                            aria-label="Thông báo phim sắp chiếu" hidden data-notification-panel>
                            <div class="notification-panel__head">
                                <strong>Phim sắp chiếu</strong>
                                <?php if ($isMember): ?>
                                <span><?= $notificationCount ?> thông báo</span>
                                <?php else: ?>
                                <span>Yêu cầu đăng nhập</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!$isMember): ?>
                            <div class="notification-state notification-state--login">
                                <i class="fa-regular fa-bell" aria-hidden="true"></i>
                                <p>
                                    <a href="#" data-open-login>Đăng nhập</a>
                                    để xem thông báo.
                                </p>
                            </div>
                            <?php elseif ($notificationCount === 0): ?>
                            <div class="notification-state">
                                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                <p>Chưa có phim sắp chiếu được công bố.</p>
                            </div>
                            <?php else: ?>
                            <div class="notification-list">
                                <?php foreach ($upcomingNotifications as $notification): ?>
                                <?php
                                $movieId = (string) ($notification["movie_id"] ?? "");
                                $title = (string) ($notification["title"] ?? "Phim sắp chiếu");
                                $poster = (string) ($notification["poster"] ?? "/thauphim-movie-website/assets/images/poster_movie.jpg");
                                $showDate = (string) ($notification["show_date"] ?? "");
                                $showTime = (string) ($notification["show_time"] ?? "");
                                $dateObject = date_create($showDate);
                                $displayDate = $dateObject ? date_format($dateObject, "d/m/Y") : "Đang cập nhật";
                                $detailUrl = "/thauphim-movie-website/pages/movie-detail.php?id=" . rawurlencode($movieId);
                                ?>
                                <a class="notification-item" href="<?= htmlspecialchars($detailUrl, ENT_QUOTES, "UTF-8") ?>">
                                    <span class="notification-item__poster">
                                        <img src="<?= htmlspecialchars($poster, ENT_QUOTES, "UTF-8") ?>"
                                            alt="Poster phim <?= htmlspecialchars($title, ENT_QUOTES, "UTF-8") ?>"
                                            loading="lazy">
                                    </span>
                                    <span class="notification-item__body">
                                        <strong><?= htmlspecialchars($title, ENT_QUOTES, "UTF-8") ?></strong>
                                        <span class="notification-item__meta">
                                            <span>
                                                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                                <?= htmlspecialchars($displayDate, ENT_QUOTES, "UTF-8") ?>
                                            </span>
                                            <span>
                                                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                                <?= htmlspecialchars($showTime, ENT_QUOTES, "UTF-8") ?>
                                            </span>
                                        </span>
                                    </span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if($isMember): ?>
                    <div class="account-wrapper" data-account-root>
                        <button id="accountToggle" class="account-toggle" type="button" aria-label="Tài khoản"
                            aria-haspopup="menu" aria-expanded="false" aria-controls="accountPanel"
                            data-account-toggle>
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
                        </button>

                        <div class="account-panel" id="accountPanel" role="menu" aria-label="Tài khoản" hidden
                            data-account-panel>
                            <button class="account-menu-item" type="button" role="menuitem">
                                <i class="fa-solid fa-heart" aria-hidden="true"></i>
                                <span>Phim yêu thích</span>
                            </button>
                            <button class="account-menu-item" type="button" role="menuitem">
                                <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                                <span>Lịch sử xem</span>
                            </button>
                            <a class="account-menu-item account-menu-item--logout"
                                href="/thauphim-movie-website/logout.php" role="menuitem">
                                <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                                <span>Đăng xuất</span>
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <a class="member-button" href="#" id="openLogin" data-open-login>
                        Đăng nhập
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <?php include __DIR__ . "/auth_login.php"; ?>
