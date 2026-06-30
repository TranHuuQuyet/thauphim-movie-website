<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/upcoming_notifications.php";

$tmdbCountries = $TMDB_COUNTRIES ?? [];
$currentUser = auth_current_user();
$isMember = $currentUser !== null;
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
    <link rel="icon" type="image/png" sizes="512x512" href="/assets/images/favicon-tab.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
    <script src="/assets/js/auth_login.js" defer></script>
    <script src="/assets/js/theme.js" defer></script>
    <script src="/assets/js/notifications.js" defer></script>
    <script src="/assets/js/account-menu.js" defer></script>
    <script src="/assets/js/theme.js" defer></script>
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="/index.php#hero" aria-label="ThauPhim trang chủ">
                <span class="brand-icon" aria-hidden="true">
                    <img src="/assets/images/favicon.png" alt="">
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
                    <a href="/pages/genres.php" class="menu-link">Chủ đề</a>
                    <a href="/index.php#featured">Bộ lọc</a>
                    <a href="/pages/upcoming.php">Sắp chiếu</a>
                    <a href="/index.php#single-movies">Phim lẻ</a>
                    <a href="/index.php#series-movies">Phim bộ</a>
                    
                    <details class="nav-dropdown">
                        <summary>
                            Quốc gia
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </summary>
                        <div class="nav-dropdown-menu">
                            <?php foreach ($tmdbCountries as $country): ?>
                            <a href="/pages/country.php?code=<?= urlencode($country['code']) ?>">
                                <?= htmlspecialchars($country['name'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </details>
                    <a href="/pages/actor.php">Diễn viên</a>
                </nav>

                <form class="search-form" action="/index.php#featured" method="get" role="search" data-home-search>
                    <label class="sr-only" for="header-search">Tìm phim, diễn viên</label>
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input id="header-search" name="q" type="search" placeholder="Tìm phim..." autocomplete="off"
                        value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8') : '' ?>">
                </form>

                <div class="header-icon-actions">
                    <button id="themeToggle" class="theme-toggle" type="button" aria-label="Chuyển giao diện sáng tối">
                        <i class="fa-solid fa-moon icon-moon" aria-hidden="true"></i>
                        <i class="fa-solid fa-sun icon-sun" aria-hidden="true"></i>
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
                                    <a href="#authModal" data-open-login>Đăng nhập</a>
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
                                $title = (string) ($notification["title"] ?? "Phim sắp chiếu");
                                $poster = (string) ($notification["poster"] ?? "/assets/images/poster_movie.jpg");
                                $showDate = (string) ($notification["show_date"] ?? "");
                                $showTime = (string) ($notification["show_time"] ?? "");
                                $dateObject = date_create($showDate);
                                $displayDate = $dateObject ? date_format($dateObject, "d/m/Y") : "Đang cập nhật";
                                $displayTime = $showTime !== "" ? $showTime : "Cập nhật";
                                $detailUrl = "/pages/movie-detail.php?id=" . urlencode((string) ($notification["movie_id"] ?? ""));
                                ?>
                                <a class="notification-item"
                                    href="<?= htmlspecialchars($detailUrl, ENT_QUOTES, "UTF-8") ?>">
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
                                                <?= htmlspecialchars($displayTime, ENT_QUOTES, "UTF-8") ?>
                                            </span>
                                        </span>
                                    </span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($isMember): ?>
                    <div class="account-wrapper" data-account-root>
                        <button id="accountToggle" class="account-toggle" type="button" aria-label="Tài khoản"
                            aria-haspopup="menu" aria-expanded="false" aria-controls="accountPanel" data-account-toggle>
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
                        </button>

                        <div class="account-panel" id="accountPanel" role="menu" aria-label="Tài khoản" hidden
                            data-account-panel>
                            <a class="account-menu-item" href="/pages/account.php" role="menuitem">
                                <i class="fa-solid fa-user" aria-hidden="true"></i>
                                <span>Tài khoản</span>
                            </a>
                            <a class="account-menu-item" href="/pages/account.php?tab=favorites" role="menuitem">
                                <i class="fa-solid fa-heart" aria-hidden="true"></i>
                                <span>Phim yêu thích</span>
                            </a>
                            <a class="account-menu-item" href="/pages/account.php?tab=history" role="menuitem">
                                <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                                <span>Lịch sử xem</span>
                            </a>
                            <a class="account-menu-item account-menu-item--logout" href="/logout.php" role="menuitem">
                                <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                                <span>Đăng xuất</span>
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <a class="member-button" href="#authModal" id="openLogin" data-open-login>
                        Đăng nhập
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <?php include __DIR__ . "/auth_login.php"; ?>