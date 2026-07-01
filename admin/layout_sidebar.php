<?php
require_once __DIR__ . '/_helpers.php';

$navItems = [
    ['href' => 'dashboard.php', 'icon' => 'fa-house', 'label' => 'Dashboard'],
    ['href' => 'movies/index.php', 'icon' => 'fa-film', 'label' => 'Movies'],
    ['href' => 'episodes/index.php', 'icon' => 'fa-tv', 'label' => 'Episodes'],
    ['href' => 'schedules/index.php', 'icon' => 'fa-calendar-days', 'label' => 'Lịch chiếu'],
    ['href' => 'genres/index.php', 'icon' => 'fa-tags', 'label' => 'Genres'],
    ['href' => 'countries/index.php', 'icon' => 'fa-earth-asia', 'label' => 'Countries'],
    ['href' => 'actors/index.php', 'icon' => 'fa-masks-theater', 'label' => 'Actors'],
    ['href' => 'users/index.php', 'icon' => 'fa-users', 'label' => 'Users'],
    ['href' => 'comments/index.php', 'icon' => 'fa-comments', 'label' => 'Comments'],
    ['href' => 'ratings/index.php', 'icon' => 'fa-star', 'label' => 'Ratings'],
    ['href' => 'watch-errors/watch-errors.php', 'icon' => 'fa-triangle-exclamation', 'label' => 'Watch Errors'],
];
?>
<aside class="sidebar">
    <ul>
        <?php foreach ($navItems as $item): ?>
            <li>
                <a href="<?= admin_e(admin_url($item['href'])) ?>">
                    <i class="fa-solid <?= admin_e($item['icon']) ?>"></i>
                    <?= admin_e($item['label']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</aside>