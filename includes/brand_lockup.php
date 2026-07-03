<?php
$brandClass = trim((string) ($brandClass ?? 'brand'));
$brandHref = (string) ($brandHref ?? app_url('index.php#hero'));
?>
<a class="<?= htmlspecialchars($brandClass, ENT_QUOTES, 'UTF-8') ?>"
    href="<?= htmlspecialchars($brandHref, ENT_QUOTES, 'UTF-8') ?>"
    aria-label="ThauPhim trang chủ">
    <span class="brand-icon" aria-hidden="true">
        <img src="<?= htmlspecialchars(app_url('assets/images/favicon.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
    </span>
    <span class="brand-copy">
        <span class="brand-title">Thau<strong>Phim</strong></span>
        <span class="brand-tagline">Phim hay cả thau</span>
    </span>
</a>
