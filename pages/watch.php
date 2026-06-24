<?php include __DIR__ . '/../includes/header.php'; ?>

<?php
require_once __DIR__ . '/../includes/db.php';

$pdo = getDatabaseConnection();

$movieId = isset($_GET["movie_id"]) ? (int) $_GET["movie_id"] : 0;
$episodeId = isset($_GET["episode_id"]) ? (int) $_GET["episode_id"] : 0;

if ($movieId <= 0 && $episodeId > 0) {
    $stmt = $pdo->prepare("
        SELECT movie_id
        FROM episodes
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$episodeId]);
    $episodeMovie = $stmt->fetch();

    if ($episodeMovie) {
        $movieId = (int) $episodeMovie["movie_id"];
    }
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function assetPath($path, $fallback)
{
    if (empty($path)) {
        return $fallback;
    }

    $cleanPath = ltrim($path, "/");
    $fullPath = __DIR__ . "/../" . $cleanPath;

    if (!file_exists($fullPath)) {
        return $fallback;
    }

    return "../" . $cleanPath;
}

function episodeLabel($movieType, $episode)
{
    if ($movieType === "series") {
        return "Tập " . $episode["episode_number"];
    }

    return $episode["title"] ?: "Full Movie";
}

function youtubeEmbedUrl($url, $startSeconds = 0)
{
    if (empty($url)) {
        return "";
    }

    $videoId = "";

    if (str_contains($url, "/embed/")) {
        $parts = explode("/embed/", $url);
        $videoId = explode("?", $parts[1])[0] ?? "";
    } elseif (preg_match('/v=([^&]+)/', $url, $matches)) {
        $videoId = $matches[1];
    } elseif (preg_match('/youtu\.be\/([^?]+)/', $url, $matches)) {
        $videoId = $matches[1];
    }

    if ($videoId === "") {
        return $url;
    }

    $startSeconds = max(0, (int) $startSeconds);

    return "https://www.youtube.com/embed/" . $videoId . "?enablejsapi=1&autoplay=1&mute=0&playsinline=1&rel=0&start=" . $startSeconds;
}
function getCurrentUserId($pdo)
{
    if (empty($_SESSION["is_login"]) || empty($_SESSION["username"])) {
        return null;
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM users
        WHERE LOWER(username) = LOWER(?)
        LIMIT 1
    ");
    $stmt->execute([$_SESSION["username"]]);
    $user = $stmt->fetch();

    return $user ? (int) $user["id"] : null;
}

if ($movieId <= 0) {
    echo "<main class='page-shell watch-page'><p>Thiếu thông tin phim.</p></main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("
    SELECT movies.*, countries.name AS country_name
    FROM movies
    LEFT JOIN countries ON movies.country_id = countries.id
    WHERE movies.id = ?
    LIMIT 1
");
$stmt->execute([$movieId]);
$movie = $stmt->fetch();

if (!$movie) {
    echo "<main class='page-shell watch-page'><p>Không tìm thấy phim.</p></main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM episodes
    WHERE movie_id = ?
    ORDER BY episode_number ASC
");
$stmt->execute([$movieId]);
$episodes = $stmt->fetchAll();

if (empty($episodes)) {
    echo "<main class='page-shell watch-page'><p>Phim này chưa có tập để xem.</p></main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
}

if ($episodeId <= 0) {
    $episodeId = (int) $episodes[0]["id"];
}

$currentEpisode = null;

foreach ($episodes as $episode) {
    if ((int) $episode["id"] === $episodeId) {
        $currentEpisode = $episode;
        break;
    }
}

if (!$currentEpisode) {
    $currentEpisode = $episodes[0];
    $episodeId = (int) $currentEpisode["id"];
}

$currentEpisodeIndex = 0;

foreach ($episodes as $index => $episode) {
    if ((int) $episode["id"] === (int) $episodeId) {
        $currentEpisodeIndex = $index;
        break;
    }
}

$prevEpisode = $episodes[$currentEpisodeIndex - 1] ?? null;
$nextEpisode = $episodes[$currentEpisodeIndex + 1] ?? null;

$stmt = $pdo->prepare("
    SELECT actors.name, actors.avatar
    FROM movie_actors
    INNER JOIN actors ON movie_actors.actor_id = actors.id
    WHERE movie_actors.movie_id = ?
");
$stmt->execute([$movieId]);
$actors = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT DISTINCT movies.*
    FROM movies
    INNER JOIN movie_genres ON movies.id = movie_genres.movie_id
    WHERE movies.id != ?
      AND movie_genres.genre_id IN (
          SELECT genre_id
          FROM movie_genres
          WHERE movie_id = ?
      )
    ORDER BY movies.views DESC
    LIMIT 5
");
$stmt->execute([$movieId, $movieId]);
$relatedMovies = $stmt->fetchAll();

if (empty($relatedMovies)) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM movies
        WHERE id != ?
        ORDER BY views DESC
        LIMIT 5
    ");
    $stmt->execute([$movieId]);
    $relatedMovies = $stmt->fetchAll();
}

$currentUserDbId = getCurrentUserId($pdo);
$startSeconds = 0;

if ($currentUserDbId) {
    $stmt = $pdo->prepare("
        SELECT progress_seconds
        FROM watch_history
        WHERE user_id = ? AND episode_id = ?
        LIMIT 1
    ");
    $stmt->execute([$currentUserDbId, $episodeId]);
    $history = $stmt->fetch();

    if ($history) {
        $startSeconds = (int) $history["progress_seconds"];
    }

    $stmt = $pdo->prepare("
        INSERT INTO watch_history (user_id, movie_id, episode_id, progress_seconds, watched_at)
        VALUES (?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            movie_id = VALUES(movie_id),
            watched_at = NOW()
    ");
    $stmt->execute([
        $currentUserDbId,
        $movieId,
        $episodeId,
        $startSeconds
    ]);
}

$currentEpisodeLabel = episodeLabel($movie["type"], $currentEpisode);
$iframeSrc = youtubeEmbedUrl($currentEpisode["youtube_url"], $startSeconds);
?>

<link rel="stylesheet" href="../assets/css/watch.css">

<main class="page-shell watch-page">
    <section class="watch-container">
        <a class="watch-back" href="movie-detail.php?id=<?= $movieId ?>">Quay lại</a>

        <section class="watch-player-card">
            <div class="watch-title-row">
                <div>
                    <h1><?= e($movie["title"]) ?></h1>
                    <p>Phần 1 · <?= e($currentEpisodeLabel) ?></p>
                </div>
            </div>

            <div class="watch-player">
                <iframe id="watchPlayer" src="<?= e($iframeSrc) ?>"
                    title="<?= e($movie["title"] . " - " . $currentEpisodeLabel) ?>"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>

            <div class="watch-actions">
                <button type="button" data-ui-placeholder>♡ Yêu thích</button>
                <button type="button" data-ui-placeholder>＋ Thêm vào</button>
                <button type="button" id="shareBtn">↗ Chia sẻ</button>
                <button type="button" data-ui-placeholder>⚑ Báo lỗi</button>
            </div>

            <div class="watch-episode-nav">
                <?php if ($prevEpisode): ?>
                    <a href="watch.php?movie_id=<?= $movieId ?>&episode_id=<?= $prevEpisode["id"] ?>">
                        ← Tập trước
                    </a>
                <?php endif; ?>

                <?php if ($nextEpisode): ?>
                    <a href="watch.php?movie_id=<?= $movieId ?>&episode_id=<?= $nextEpisode["id"] ?>">
                        Tập sau →
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <section class="watch-layout">
            <section class="watch-main">
                <section class="watch-movie-info">
                    <img src="<?= e(assetPath($movie["poster"], "../assets/images/poster_movie.jpg")) ?>"
                        alt="<?= e($movie["title"]) ?>">

                    <div>
                        <h2>
                            <?= e($movie["title"]) ?>
                        </h2>
                        <p class="watch-sub-title">
                            <?= e($movie["country_name"] ?? "Đang cập nhật") ?>
                            ·
                            <?= e($movie["release_year"] ?? "N/A") ?>
                            ·
                            <?= $movie["type"] === "series" ? "Phim bộ" : "Phim lẻ" ?>
                        </p>

                        <div class="watch-badges">
                            <span>T13</span>
                            <span>
                                <?= e($movie["quality"] ?? "HD") ?>
                            </span>
                            <span>
                                <?= !empty($movie["is_premium"]) ? "Premium" : "Free" ?>
                            </span>
                        </div>

                        <p class="watch-description">
                            <?= e($movie["description"] ?? "Nội dung phim đang được cập nhật.") ?>
                        </p>

                        <a class="watch-info-link" href="movie-detail.php?id=<?= $movieId ?>">
                            Thông tin phim ›
                        </a>
                    </div>
                </section>

                <section class="watch-episodes" id="watchEpisodeList">
                    <div class="watch-section-head">
                        <h2>☰ Phần 1</h2>
                        <span>Phụ đề</span>
                    </div>

                    <div class="watch-episode-list">
                        <?php foreach ($episodes as $episode): ?>
                            <?php
                            $isActive = (int) $episode["id"] === $episodeId;
                            $label = episodeLabel($movie["type"], $episode);
                            ?>
                            <a class="watch-episode-btn <?= $isActive ? "active" : "" ?>"
                                href="watch.php?movie_id=<?= $movieId ?>&episode_id=<?= $episode["id"] ?>"> ▶
                                <?= e($label) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="watch-comment-box">
                    <h2> Bình luận</h2>

                    <div class="watch-comment-input">
                        <textarea placeholder="Viết bình luận"></textarea>
                        <div>
                            <span>0 / 1000</span>
                            <button type="button" data-ui-placeholder>Gửi</button>
                        </div>
                    </div>

                    <div class="watch-empty-comment">
                        Chưa có bình luận nào
                    </div>
                </section>
            </section>

            <aside class="watch-sidebar">
                <section class="watch-rating-box">
                    <h2>Đánh giá phim</h2>
                    <div class="watch-rating-content">
                        <div class="rating-summary">
                            <strong id="ratingAverage">Chưa có đánh giá</strong>
                            <span id="ratingTotal">0 lượt đánh giá</span>
                        </div>

                        <?php if (empty($_SESSION["is_login"])): ?>
                            <p class="rating-login-note">
                                Vui lòng <a href="../login.php">đăng nhập</a> để đánh giá.
                            </p>
                        <?php else: ?>
                            <div class="rating-stars" id="ratingStars">
                                <button type="button" data-rating="1">☆</button>
                                <button type="button" data-rating="2">☆</button>
                                <button type="button" data-rating="3">☆</button>
                                <button type="button" data-rating="4">☆</button>
                                <button type="button" data-rating="5">☆</button>
                            </div>

                            <p class="rating-message" id="ratingMessage">Chọn số sao để đánh giá phim.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="watch-actors">
                    <h2>Diễn viên</h2>

                    <?php if (empty($actors)): ?>
                        <p class="watch-empty-text">Đang cập nhật</p>
                    <?php else: ?>
                        <div class="watch-actor-grid">
                            <?php foreach ($actors as $actor): ?>
                                <div class="watch-actor-item">
                                    <img src="<?= e(assetPath($actor["avatar"], "../assets/images/avatar-default.jpg")) ?>"
                                        alt="<?= e($actor["name"]) ?>">
                                    <span>
                                        <?= e($actor["name"]) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="watch-related">
                    <h2>Đề xuất cho bạn</h2>

                    <?php foreach ($relatedMovies as $related): ?>
                        <a class="watch-related-item" href="movie-detail.php?id=<?= $related["id"] ?>">
                            <img src="<?= e(assetPath($related["poster"], "../assets/images/poster_movie.jpg")) ?>"
                                alt="<?= e($related["title"]) ?>">

                            <div>
                                <strong>
                                    <?= e($related["title"]) ?>
                                </strong>
                                <span>
                                    <?= e($related["release_year"] ?? "N/A") ?> ·
                                    <?= e($related["quality"] ?? "HD") ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </section>
            </aside>
        </section>
    </section>
</main>

<script>
    window.watchHistoryData = {
        movieId: <?= json_encode($movieId) ?>,
        episodeId: <?= json_encode($episodeId) ?>,
        progressSeconds: <?= json_encode($startSeconds) ?>,
        isLoggedIn: <?= !empty($_SESSION["is_login"]) ? "true" : "false" ?>
    };
</script>

<script src="../assets/js/main.js"></script>
<script src="../assets/js/watch.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>