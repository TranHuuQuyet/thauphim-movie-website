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
$notificationUserId = $isMember ? (int) ($currentUser["id"] ?? 0) : null;
$upcomingNotifications = get_published_upcoming_notifications($notificationUserId);
$notificationTotalCount = count($upcomingNotifications);
$notificationUnreadCount = count(array_filter(
    $upcomingNotifications,
    static fn(array $notification): bool => empty($notification["is_read"])
));
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
    <link rel="icon" type="image/png" sizes="512x512" href="<?= htmlspecialchars(app_url('assets/images/favicon-tab.png'), ENT_QUOTES, 'UTF-8') ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/auth.css'), ENT_QUOTES, 'UTF-8') ?>">
    <script src="<?= htmlspecialchars(app_url('assets/js/auth_login.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <script src="<?= htmlspecialchars(app_url('assets/js/theme.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <script src="<?= htmlspecialchars(app_url('assets/js/notifications.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <script src="<?= htmlspecialchars(app_url('assets/js/account-menu.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="<?= htmlspecialchars(app_url('index.php#hero'), ENT_QUOTES, 'UTF-8') ?>" aria-label="ThauPhim trang chủ">
                <span class="brand-icon" aria-hidden="true">
                    <img src="<?= htmlspecialchars(app_url('assets/images/favicon.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
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
                    <a href="<?= htmlspecialchars(app_url('pages/genres.php'), ENT_QUOTES, 'UTF-8') ?>" class="menu-link">Chủ đề</a>
                    <a href="<?= htmlspecialchars(app_url('pages/browse.php?q='), ENT_QUOTES, 'UTF-8') ?>">Bộ lọc</a>
                    <a href="<?= htmlspecialchars(app_url('index.php#single-movies'), ENT_QUOTES, 'UTF-8') ?>">Phim lẻ</a>
                    <a href="<?= htmlspecialchars(app_url('index.php#series-movies'), ENT_QUOTES, 'UTF-8') ?>">Phim bộ</a>
                    
                    <details class="nav-dropdown">
                        <summary>
                            Quốc gia
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </summary>
                        <div class="nav-dropdown-menu">
                            <?php foreach ($tmdbCountries as $country): ?>
                            <a href="<?= htmlspecialchars(app_url('pages/country.php?code=' . urlencode($country['code'])), ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($country['name'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </details>
                    <a href="<?= htmlspecialchars(app_url('pages/actor.php'), ENT_QUOTES, 'UTF-8') ?>">Diễn viên</a>
                </nav>

                <form class="search-form" action="<?= htmlspecialchars(app_url('pages/browse.php'), ENT_QUOTES, 'UTF-8') ?>" method="get" role="search">
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

                    <div class="notification-wrapper" data-notification-root
                        data-notification-mark-read-url="<?= htmlspecialchars(app_url('api/notifications.php'), ENT_QUOTES, 'UTF-8') ?>">
                        <button id="notificationToggle" class="notification-toggle" type="button"
                            aria-label="Thông báo phim sắp chiếu" aria-haspopup="dialog" aria-expanded="false"
                            aria-controls="notificationPanel" data-notification-toggle>
                            <i class="fa-regular fa-bell" aria-hidden="true"></i>
                            <?php if ($isMember && $notificationUnreadCount > 0): ?>
                            <span class="notification-badge" aria-label="<?= $notificationUnreadCount ?> thông báo chưa đọc"
                                data-notification-badge>
                                <?= $notificationUnreadCount > 9 ? "9+" : $notificationUnreadCount ?>
                            </span>
                            <?php endif; ?>
                        </button>

                        <div class="notification-panel" id="notificationPanel" role="dialog"
                            aria-label="Thông báo phim sắp chiếu" hidden data-notification-panel>
                            <div class="notification-panel__head">
                                <strong>Phim sắp chiếu</strong>
                                <?php if ($isMember): ?>
                                <span data-notification-count-label><?= $notificationUnreadCount ?> chưa đọc</span>
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
                            <?php elseif ($notificationTotalCount === 0): ?>
                            <div class="notification-state">
                                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                <p>Chưa có phim sắp chiếu được công bố.</p>
                            </div>
                            <?php else: ?>
                            <div class="notification-list">
                                <?php foreach ($upcomingNotifications as $notification): ?>
                                <?php
                                $title = (string) ($notification["title"] ?? "Phim sắp chiếu");
                                $poster = (string) ($notification["poster"] ?? app_url("assets/images/poster_movie.jpg"));
                                $showDate = (string) ($notification["show_date"] ?? "");
                                $showTime = (string) ($notification["show_time"] ?? "");
                                $scheduleId = (int) ($notification["id"] ?? 0);
                                $isRead = !empty($notification["is_read"]);
                                $dateObject = date_create($showDate);
                                $displayDate = $dateObject ? date_format($dateObject, "d/m/Y") : "Đang cập nhật";
                                $detailUrl = app_url("pages/movie-detail.php?id=" . urlencode((string) ($notification["movie_id"] ?? "")));
                                ?>
                                <a class="notification-item<?= $isRead ? "" : " notification-item--unread" ?>"
                                    data-notification-item data-schedule-id="<?= $scheduleId ?>"
                                    href="<?= htmlspecialchars($detailUrl, ENT_QUOTES, "UTF-8") ?>">
                                    <span class="notification-item__poster">
                                        <img src="<?= htmlspecialchars($poster, ENT_QUOTES, "UTF-8") ?>"
                                            alt="Poster phim <?= htmlspecialchars($title, ENT_QUOTES, "UTF-8") ?>"
                                            loading="lazy">
                                    </span>
                                    <span class="notification-item__body">
                                        <span class="notification-item__title-row">
                                            <strong><?= htmlspecialchars($title, ENT_QUOTES, "UTF-8") ?></strong>
                                            <?php if (!$isRead): ?>
                                            <span class="notification-item__dot" aria-label="Chưa đọc"></span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="notification-item__meta">
                                            <span>
                                                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                                <?= htmlspecialchars($displayDate, ENT_QUOTES, "UTF-8") ?>
                                            </span>
                                            <?php if ($showTime !== ""): ?>
                                            <span>
                                                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                                <?= htmlspecialchars($showTime, ENT_QUOTES, "UTF-8") ?>
                                            </span>
                                            <?php endif; ?>
                                        </span>
                                        <?php if (!empty($notification["note"])): ?>
                                        <span class="notification-item__note">
                                            <?= htmlspecialchars((string) $notification["note"], ENT_QUOTES, "UTF-8") ?>
                                        </span>
                                        <?php endif; ?>
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
                            <a class="account-menu-item" href="<?= htmlspecialchars(app_url('pages/account.php'), ENT_QUOTES, 'UTF-8') ?>" role="menuitem">
                                <i class="fa-solid fa-user" aria-hidden="true"></i>
                                <span>Tài khoản</span>
                            </a>
                            <a class="account-menu-item" href="<?= htmlspecialchars(app_url('pages/account.php?tab=favorites'), ENT_QUOTES, 'UTF-8') ?>" role="menuitem">
                                <i class="fa-solid fa-heart" aria-hidden="true"></i>
                                <span>Phim yêu thích</span>
                            </a>
                            <a class="account-menu-item" href="<?= htmlspecialchars(app_url('pages/account.php?tab=history'), ENT_QUOTES, 'UTF-8') ?>" role="menuitem">
                                <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                                <span>Lịch sử xem</span>
                            </a>
                            <a class="account-menu-item account-menu-item--logout" href="<?= htmlspecialchars(app_url('logout.php'), ENT_QUOTES, 'UTF-8') ?>" role="menuitem">
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

<style>
    header, .header, #header, .navbar {
        position: relative; 
        z-index: 99999 !important;
    }

    .menu-toggle {
        position: relative;
        z-index: 100000 !important;
        cursor: pointer;
    }

    .mobile-menu, .nav-menu, .sidebar-menu, .main-navigation {
        z-index: 999999 !important;
    }
</style>