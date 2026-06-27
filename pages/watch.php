<?php include __DIR__ . '/../includes/header.php'; ?>

<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = getDatabaseConnection();
$currentUser = auth_current_user($pdo);
$currentUserDbId = $currentUser ? (int) $currentUser["id"] : null;

$movieId = isset($_GET["movie_id"]) ? (int) $_GET["movie_id"] : 0;
$episodeId = isset($_GET["episode_id"]) ? (int) $_GET["episode_id"] : 0;

if ($movieId <= 0 && $episodeId > 0) {
    $stmt = $pdo->prepare("
        SELECT movie_id
        FROM episodes
        WHERE id = ?
            AND is_published = 1
            AND youtube_url IS NOT NULL
            AND youtube_url <> ''
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

$watchAccess = auth_can_watch_movie($movie, $currentUser);

if (!$watchAccess["allowed"]) {
    ?>
    <link rel="stylesheet" href="/assets/css/watch.css">
    <main class="page-shell watch-page">
        <section class="watch-container">
            <section class="watch-access-card">
                <h1>Không thể phát phim</h1>
                <p><?= e($watchAccess["message"]) ?></p>
                <div class="watch-access-actions">
                    <a href="movie-detail.php?id=<?= $movieId ?>">Quay lại chi tiết</a>
                    <?php if ($watchAccess["code"] === "login_required"): ?>
                        <a href="#authModal" data-open-login>Đăng nhập</a>
                    <?php elseif ($currentUser !== null): ?>
                        <a href="account.php">Tài khoản</a>
                    <?php endif; ?>
                </div>
            </section>
        </section>
    </main>
    <?php
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM episodes
    WHERE movie_id = ?
        AND is_published = 1
        AND youtube_url IS NOT NULL
        AND youtube_url <> ''
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

if (empty($currentEpisode["youtube_url"])) {
    echo "<main class='page-shell watch-page'><p>Tập phim này hiện chưa có.</p></main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
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

$isFavorite = false;
$userRating = null;

if ($currentUserDbId !== null) {
    $stmt = $pdo->prepare("
        SELECT id
        FROM favorites
        WHERE user_id = ? AND movie_id = ?
        LIMIT 1
    ");
    $stmt->execute([$currentUserDbId, $movieId]);
    $isFavorite = (bool) $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT rating
        FROM ratings
        WHERE user_id = ? AND movie_id = ?
        LIMIT 1
    ");
    $stmt->execute([$currentUserDbId, $movieId]);
    $ratingValue = $stmt->fetchColumn();
    $userRating = $ratingValue !== false ? (int) $ratingValue : null;
}

$stmt = $pdo->prepare("
    SELECT comments.*, users.username
    FROM comments
    INNER JOIN users ON comments.user_id = users.id
    WHERE comments.movie_id = ?
      AND comments.status = 'visible'
    ORDER BY comments.created_at DESC
    LIMIT 50
");
$stmt->execute([$movieId]);
$comments = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT AVG(rating) AS rating_average, COUNT(*) AS rating_count
    FROM ratings
    WHERE movie_id = ?
");
$stmt->execute([$movieId]);
$ratingSummary = $stmt->fetch() ?: ["rating_average" => 0, "rating_count" => 0];
$ratingAverage = round((float) ($ratingSummary["rating_average"] ?? 0), 2);
$ratingCount = (int) ($ratingSummary["rating_count"] ?? 0);

$currentEpisodeLabel = episodeLabel($movie["type"], $currentEpisode);
$iframeSrc = youtubeEmbedUrl($currentEpisode["youtube_url"], $startSeconds);
?>

<link rel="stylesheet" href="/assets/css/watch.css">

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
                <button class="<?= $isFavorite ? "is-favorite" : "" ?>" type="button" data-favorite-toggle
                    data-movie-id="<?= $movieId ?>" aria-pressed="<?= $isFavorite ? "true" : "false" ?>">
                    <?= $isFavorite ? "♥" : "♡" ?> Yêu thích
                </button>
                <a href="account.php?tab=favorites">＋ Danh sách</a>
                <button type="button" id="shareBtn">↗ Chia sẻ</button>
                <button type="button" disabled>⚑ Báo lỗi</button>
            </div>

            <div class="watch-episode-nav">
                <?php if ($prevEpisode): ?>
                    <a href="watch.php?movie_id=<?= (int) $movieId ?>&episode_id=<?= (int) $prevEpisode["id"] ?>">
                        ← Tập trước
                    </a>
                <?php endif; ?>

                <?php if ($nextEpisode): ?>
                    <a href="watch.php?movie_id=<?= (int) $movieId ?>&episode_id=<?= (int) $nextEpisode["id"] ?>">
                        Tập sau →
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <section class="watch-layout">
            <section class="watch-main">
                <section class="watch-movie-info">
                    <img src="<?= e(assetPath($movie["poster"], "/assets/images/poster_movie.jpg")) ?>"
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
                                href="watch.php?movie_id=<?= (int) $movieId ?>&episode_id=<?= (int) $episode["id"] ?>">
                                ▶ <?= e($label) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="watch-comment-box">
                    <h2> Bình luận</h2>

                    <?php if ($currentUserDbId === null): ?>
                        <div class="watch-comment-input">
                            <textarea placeholder="Viết bình luận" disabled></textarea>
                            <div>
                                <span>0 / 1000</span>
                                <button type="button" disabled>Gửi</button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="watch-comment-input">
                            <textarea id="commentInput" placeholder="Viết bình luận"></textarea>
                            <div>
                                <span id="commentCount">0 / 1000</span>
                                <button id="sendCommentBtn" type="button">Gửi</button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="watch-empty-comment <?= !empty($comments) ? "has-comments" : "" ?>" id="commentList">
                        <?php if (empty($comments)): ?>
                            Chưa có bình luận nào
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <?php
                                $commentTime = !empty($comment["created_at"]) ? strtotime($comment["created_at"]) : false;
                                $canDeleteComment = $currentUserDbId !== null && (
                                    $currentUserDbId === (int) $comment["user_id"] ||
                                    (($currentUser["role"] ?? "") === "admin")
                                );
                                ?>
                                <article class="comment-item" data-comment-id="<?= (int) $comment["id"] ?>">
                                    <div class="comment-item-head">
                                        <div class="comment-author">
                                            <strong><?= e($comment["username"]) ?></strong>
                                            <span><?= $commentTime ? e(date("d/m/Y H:i", $commentTime)) : "" ?></span>
                                        </div>
                                        <?php if ($canDeleteComment): ?>
                                            <button class="delete-comment-btn" type="button"
                                                data-delete-comment="<?= (int) $comment["id"] ?>">Xóa</button>
                                        <?php endif; ?>
                                    </div>
                                    <p><?= e($comment["content"]) ?></p>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </section>

            <aside class="watch-sidebar">
                <section class="watch-rating-box">
                    <h2>Đánh giá phim</h2>
                    <div class="watch-rating-content">
                        <div class="rating-summary">
                            <strong
                                id="ratingAverage"><?= $ratingCount > 0 ? e(number_format($ratingAverage, 1) . " / 5") : "Chưa có đánh giá" ?></strong>
                            <span id="ratingTotal"><?= (int) $ratingCount ?> lượt đánh giá</span>
                        </div>

                        <?php if ($currentUserDbId === null): ?>
                            <p class="rating-login-note">
                                Vui lòng <a href="../login.php">đăng nhập</a> để đánh giá.
                            </p>
                        <?php else: ?>
                            <div class="rating-stars" id="ratingStars">
                                <?php for ($star = 1; $star <= 5; $star++): ?>
                                    <button type="button" data-rating="<?= $star ?>"
                                        class="<?= $userRating !== null && $star <= $userRating ? "active" : "" ?>">
                                        <?= $userRating !== null && $star <= $userRating ? "★" : "☆" ?>
                                    </button>
                                <?php endfor; ?>
                            </div>

                            <p class="rating-message" id="ratingMessage">
                                <?= $userRating !== null ? "Bạn đã đánh giá " . (int) $userRating . " sao." : "Chọn số sao để đánh giá phim." ?>
                            </p>
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
                                    <img src="<?= e(assetPath($actor["avatar"], "/assets/images/favicon.png")) ?>"
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
                            <img src="<?= e(assetPath($related["poster"], "/assets/images/poster_movie.jpg")) ?>"
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
        isLoggedIn: <?= $currentUserDbId !== null ? "true" : "false" ?>,
    };

    window.movieInteractionData = {
        movieId: <?= json_encode($movieId) ?>,
        isLoggedIn: <?= $currentUserDbId !== null ? "true" : "false" ?>,
        endpointsBase: "/api/",
        loginUrl: "/index.php#authModal"
    };
</script>

<script src="/assets/js/main.js"></script>
<script src="/assets/js/watch.js"></script>
<script src="/assets/js/movie-interactions.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>