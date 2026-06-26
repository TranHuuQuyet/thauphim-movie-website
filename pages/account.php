<?php
require_once __DIR__ . "/../includes/auth.php";

auth_start_session();

$pdo = getDatabaseConnection();
$currentUser = auth_current_user($pdo);

if ($currentUser === null) {
    header("Location: /index.php#authModal");
    exit;
}

$currentUserId = (int) $currentUser["id"];
$allowedTabs = ["overview", "favorites", "history"];
$tab = (string) ($_GET["tab"] ?? "overview");

if (!in_array($tab, $allowedTabs, true)) {
    $tab = "overview";
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function accountAssetPath($path, $fallback)
{
    if (empty($path)) {
        return $fallback;
    }

    if (preg_match('/^https?:\/\//i', (string) $path) || str_starts_with((string) $path, "data:")) {
        return (string) $path;
    }

    $cleanPath = ltrim((string) $path, "/");
    $fullPath = __DIR__ . "/../" . $cleanPath;

    if (!file_exists($fullPath)) {
        return $fallback;
    }

    return "/" . $cleanPath;
}

$stmt = $pdo->prepare("
    SELECT
        (SELECT COUNT(*) FROM favorites WHERE user_id = ?) AS favorite_count,
        (SELECT COUNT(*) FROM watch_history WHERE user_id = ?) AS history_count,
        (SELECT COUNT(*) FROM comments WHERE user_id = ?) AS comment_count,
        (SELECT COUNT(*) FROM ratings WHERE user_id = ?) AS rating_count
");
$stmt->execute([$currentUserId, $currentUserId, $currentUserId, $currentUserId]);
$stats = $stmt->fetch() ?: [
    "favorite_count" => 0,
    "history_count" => 0,
    "comment_count" => 0,
    "rating_count" => 0,
];

$stmt = $pdo->prepare("
    SELECT movies.*, countries.name AS country_name, favorites.created_at AS favorited_at
    FROM favorites
    INNER JOIN movies ON favorites.movie_id = movies.id
    LEFT JOIN countries ON movies.country_id = countries.id
    WHERE favorites.user_id = ?
    ORDER BY favorites.created_at DESC
");
$stmt->execute([$currentUserId]);
$favorites = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT
        watch_history.*,
        movies.title AS movie_title,
        movies.poster,
        movies.type,
        movies.release_year,
        movies.quality,
        episodes.episode_number,
        episodes.title AS episode_title
    FROM watch_history
    INNER JOIN movies ON watch_history.movie_id = movies.id
    INNER JOIN episodes ON watch_history.episode_id = episodes.id
    WHERE watch_history.user_id = ?
    ORDER BY watch_history.watched_at DESC
    LIMIT 50
");
$stmt->execute([$currentUserId]);
$historyItems = $stmt->fetchAll();

include __DIR__ . "/../includes/header.php";
?>

<link rel="stylesheet" href="/assets/css/user.css">

<main class="page-shell account-page">
    <section class="account-hero">
        <div>
            <span class="account-kicker">Tài khoản</span>
            <h1><?= e($currentUser["username"]) ?></h1>
            <p><?= e($currentUser["email"]) ?></p>
        </div>
        <div class="account-badges">
            <span><?= e(ucfirst((string) $currentUser["membership"])) ?></span>
            <span><?= e(ucfirst((string) $currentUser["role"])) ?></span>
        </div>
    </section>

    <nav class="account-tabs" aria-label="Tài khoản">
        <a class="<?= $tab === "overview" ? "active" : "" ?>" href="account.php">Tổng quan</a>
        <a class="<?= $tab === "favorites" ? "active" : "" ?>" href="account.php?tab=favorites">Yêu thích</a>
        <a class="<?= $tab === "history" ? "active" : "" ?>" href="account.php?tab=history">Lịch sử xem</a>
    </nav>

    <?php if ($tab === "overview"): ?>
        <section class="account-stats" aria-label="Thống kê tài khoản">
            <article>
                <span>Yêu thích</span>
                <strong><?= (int) $stats["favorite_count"] ?></strong>
            </article>
            <article>
                <span>Lịch sử xem</span>
                <strong><?= (int) $stats["history_count"] ?></strong>
            </article>
            <article>
                <span>Bình luận</span>
                <strong><?= (int) $stats["comment_count"] ?></strong>
            </article>
            <article>
                <span>Đánh giá</span>
                <strong><?= (int) $stats["rating_count"] ?></strong>
            </article>
        </section>

        <section class="account-section">
            <div class="account-section-head">
                <h2>Yêu thích gần đây</h2>
                <a href="account.php?tab=favorites">Xem tất cả</a>
            </div>
            <?php $previewFavorites = array_slice($favorites, 0, 6); ?>
            <?php if (empty($previewFavorites)): ?>
                <p class="account-empty">Chưa có phim yêu thích.</p>
            <?php else: ?>
                <div class="account-movie-grid">
                    <?php foreach ($previewFavorites as $movie): ?>
                        <a class="account-movie-card" href="movie-detail.php?id=<?= (int) $movie["id"] ?>">
                            <img src="<?= e(accountAssetPath($movie["poster"], "/assets/images/poster_movie.jpg")) ?>" alt="<?= e($movie["title"]) ?>">
                            <strong><?= e($movie["title"]) ?></strong>
                            <span><?= e(($movie["release_year"] ?? "N/A") . " · " . ($movie["quality"] ?? "HD")) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php elseif ($tab === "favorites"): ?>
        <section class="account-section">
            <div class="account-section-head">
                <h2>Phim yêu thích</h2>
                <span><?= count($favorites) ?> phim</span>
            </div>
            <?php if (empty($favorites)): ?>
                <p class="account-empty">Chưa có phim yêu thích.</p>
            <?php else: ?>
                <div class="account-movie-grid">
                    <?php foreach ($favorites as $movie): ?>
                        <a class="account-movie-card" href="movie-detail.php?id=<?= (int) $movie["id"] ?>">
                            <img src="<?= e(accountAssetPath($movie["poster"], "/assets/images/poster_movie.jpg")) ?>" alt="<?= e($movie["title"]) ?>">
                            <strong><?= e($movie["title"]) ?></strong>
                            <span><?= e(($movie["release_year"] ?? "N/A") . " · " . ($movie["quality"] ?? "HD")) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <section class="account-section">
            <div class="account-section-head">
                <h2>Lịch sử xem</h2>
                <span><?= count($historyItems) ?> lượt xem</span>
            </div>
            <?php if (empty($historyItems)): ?>
                <p class="account-empty">Chưa có lịch sử xem.</p>
            <?php else: ?>
                <div class="account-history-list">
                    <?php foreach ($historyItems as $item): ?>
                        <?php
                        $episodeLabel = $item["type"] === "series"
                            ? "Tập " . (int) $item["episode_number"]
                            : ($item["episode_title"] ?: "Full Movie");
                        $watchedAt = !empty($item["watched_at"]) ? strtotime($item["watched_at"]) : false;
                        ?>
                        <a class="account-history-item"
                            href="watch.php?movie_id=<?= (int) $item["movie_id"] ?>&episode_id=<?= (int) $item["episode_id"] ?>">
                            <img src="<?= e(accountAssetPath($item["poster"], "/assets/images/poster_movie.jpg")) ?>" alt="<?= e($item["movie_title"]) ?>">
                            <span>
                                <strong><?= e($item["movie_title"]) ?></strong>
                                <small><?= e($episodeLabel) ?> · <?= (int) $item["progress_seconds"] ?>s</small>
                                <small><?= $watchedAt ? e(date("d/m/Y H:i", $watchedAt)) : "" ?></small>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>

<?php include __DIR__ . "/../includes/footer.php"; ?>
