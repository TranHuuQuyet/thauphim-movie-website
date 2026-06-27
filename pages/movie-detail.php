<?php include __DIR__ . '/../includes/header.php'; ?>

<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = getDatabaseConnection();

$movieId = isset($_GET["id"]) ? (int) $_GET["id"] : 1;

$currentUser = auth_current_user($pdo);
$isLoggedIn = $currentUser !== null;
$currentUserId = $currentUser ? (int) $currentUser["id"] : null;
$currentUserName = $currentUser["username"] ?? "Bạn";

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

    $cleanPath = ltrim($path, "/");
    $fullPath = __DIR__ . "/../" . $cleanPath;

    if (!file_exists($fullPath)) {
        return $fallback;
    }

    return "../" . $cleanPath;
}

function statusText($status)
{
    return match ($status) {
        "coming_soon" => "Chưa ra mắt",
        "ongoing" => "Đang chiếu",
        "completed" => "Đã hoàn thành",
        default => "Đang cập nhật"
    };
}

function youtubeEmbedUrl($url, $query = "")
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

    return "https://www.youtube.com/embed/" . $videoId . $query;
}

$stmt = $pdo->prepare("
    SELECT 
        movies.*,
        countries.name AS country_name,
        (
            SELECT schedules.release_date
            FROM schedules
            WHERE schedules.movie_id = movies.id
            ORDER BY schedules.release_date ASC
            LIMIT 1
        ) AS release_date
    FROM movies
    LEFT JOIN countries ON movies.country_id = countries.id
    WHERE movies.id = ?
    LIMIT 1
");
$stmt->execute([$movieId]);
$movie = $stmt->fetch();

if (!$movie) {
    echo "<main class='page-shell detail-page'><p>Không tìm thấy phim.</p></main>";
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$watchAccess = auth_can_watch_movie($movie, $currentUser);

$stmt = $pdo->prepare("
    SELECT genres.name
    FROM movie_genres
    INNER JOIN genres ON movie_genres.genre_id = genres.id
    WHERE movie_genres.movie_id = ?
");
$stmt->execute([$movieId]);
$genres = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT actors.name, actors.avatar
    FROM movie_actors
    INNER JOIN actors ON movie_actors.actor_id = actors.id
    WHERE movie_actors.movie_id = ?
");
$stmt->execute([$movieId]);
$actors = $stmt->fetchAll();

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

$firstEpisode = $episodes[0] ?? null;
$continueWatching = null;

if ($currentUserId !== null) {
    $stmt = $pdo->prepare("
        SELECT 
            watch_history.*,
            episodes.episode_number,
            episodes.title AS episode_title
        FROM watch_history
        INNER JOIN episodes ON watch_history.episode_id = episodes.id
        WHERE watch_history.user_id = ?
          AND watch_history.movie_id = ?
            AND episodes.is_published = 1
            AND episodes.youtube_url IS NOT NULL
            AND episodes.youtube_url <> ''
        ORDER BY watch_history.watched_at DESC
        LIMIT 1
    ");
    $stmt->execute([
        $currentUserId,
        $movieId
    ]);
    $continueWatching = $stmt->fetch();
}

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
    LIMIT 6
");
$stmt->execute([$movieId, $movieId]);
$relatedMovies = $stmt->fetchAll();

if (empty($relatedMovies)) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM movies
        WHERE id != ?
        ORDER BY views DESC
        LIMIT 6
    ");
    $stmt->execute([$movieId]);
    $relatedMovies = $stmt->fetchAll();
}

$typeLabel = $movie["type"] === "series" ? "Phim bộ" : "Phim lẻ";
$premiumLabel = !empty($movie["is_premium"]) ? "Premium" : "Free";

$isFavorite = false;
$userRating = null;

if ($currentUserId !== null) {
    $stmt = $pdo->prepare("
        SELECT id
        FROM favorites
        WHERE user_id = ?
          AND movie_id = ?
        LIMIT 1
    ");
    $stmt->execute([$currentUserId, $movieId]);
    $isFavorite = (bool) $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT rating
        FROM ratings
        WHERE user_id = ?
          AND movie_id = ?
        LIMIT 1
    ");
    $stmt->execute([$currentUserId, $movieId]);
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

$releaseLabel = !empty($movie["release_date"])
    ? date("d/m/Y", strtotime($movie["release_date"]))
    : ($movie["release_year"] ?? "N/A");

$statusClass = match ($movie["status"]) {
    "coming_soon" => "status-warning",
    "ongoing" => "status-success",
    "completed" => "status-success",
    default => "status-success"
};

$heroVideoUrl = !empty($firstEpisode["youtube_url"])
    ? youtubeEmbedUrl($firstEpisode["youtube_url"], "?autoplay=1&mute=1&controls=0&rel=0") : "";
?>

<link rel="stylesheet" href="/assets/css/movie-detail.css">

<main class="page-shell detail-page">
    <section class="hero detail-hero">
        <div class="video-bg">
            <?php if ($heroVideoUrl !== ""): ?>
                <iframe src="<?= e($heroVideoUrl) ?>" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            <?php else: ?>
                <img src="<?= e(assetPath($movie["backdrop"] ?: $movie["poster"], "/assets/images/poster_movie.jpg")) ?>"
                    alt="<?= e($movie["title"]) ?>">
            <?php endif; ?>
        </div>

        <div class="hero-overlay"></div>
    </section>

    <section class="detail-body">
        <section id="movie-info" class="detail-sidebar">
            <div class="detail-info-card">
                <div class="detail-poster">
                    <img src="<?= e(assetPath($movie["poster"], "/assets/images/poster_movie.jpg")) ?>"
                        alt="<?= e($movie["title"]) ?>">
                </div>

                <div class="detail-info-text">
                    <h1><?= e($movie["title"]) ?></h1>
                    <p class="movie-name">
                        <?= e(($movie["country_name"] ?? "Đang cập nhật") . " • " . ($movie["release_year"] ?? "N/A") . " • " . $typeLabel) ?>
                    </p>

                    <a href="#movie-info" class="detail-info-link">Thông tin phim</a>
                </div>

                <div class="detail-info-meta">
                    <div class="detail-badges">
                        <span class="detail-badge age">T13</span>
                        <span class="detail-badge"><?= e($releaseLabel) ?></span>
                        <span class="detail-badge"><?= e($movie["release_year"] ?? "N/A") ?></span>
                        <span class="detail-badge"><?= e($movie["quality"] ?? "HD") ?></span>
                        <span class="detail-badge"><?= e($premiumLabel) ?></span>
                    </div>

                    <div class="detail-tags">
                        <?php foreach ($genres as $genre): ?>
                            <span>#<?= e($genre["name"]) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="detail-status <?= e($statusClass) ?>">
                        <span><?= e(statusText($movie["status"])) ?></span>
                    </div>

                    <div class="detail-desc-block">
                        <h3>Giới thiệu</h3>
                        <p><?= e($movie["description"] ?: ($movie["overview"] ?? "Nội dung phim đang được cập nhật.")) ?>
                        </p>
                    </div>

                    <div class="detail-info-list">
                        <p><strong>Quốc gia:</strong> <?= e($movie["country_name"] ?? "Đang cập nhật") ?></p>
                        <p><strong>Năm:</strong> <?= e($movie["release_year"] ?? "Đang cập nhật") ?></p>
                        <p><strong>Tên gốc:</strong> <?= e($movie["original_title"] ?? "Đang cập nhật") ?></p>
                        <p><strong>Thời lượng:</strong>
                            <?= !empty($movie["runtime"]) ? e($movie["runtime"] . " phút") : "Đang cập nhật" ?></p>
                        <p><strong>Điểm TMDB:</strong>
                            <?= isset($movie["vote_average"]) ? e($movie["vote_average"]) : "Đang cập nhật" ?></p>
                        <p><strong>Loại phim:</strong> <?= e($typeLabel) ?></p>
                        <p><strong>Diễn viên:</strong>
                            <?= e(implode(", ", array_column($actors, "name")) ?: "Đang cập nhật") ?></p>
                    </div>
                </div>
            </div>
        </section>

        <section class="detail-main-content">
            <section class="detail-actions">

                <?php if ($firstEpisode): ?>
                    <a class="play-btn" id="watchNowBtn"
                        href="watch.php?movie_id=<?= $movieId ?>&episode_id=<?= $firstEpisode["id"] ?>">
                        ▶ Xem Ngay
                    </a>
                <?php else: ?>
                    <button class="play-btn" type="button" disabled>Chưa có tập</button>
                <?php endif; ?>

                <?php if ($continueWatching): ?>
                    <a class="continue-watch-btn"
                        href="watch.php?movie_id=<?= (int) $movieId ?>&episode_id=<?= (int) $continueWatching["episode_id"] ?>">
                        ↻ Tiếp tục xem
                        <?= $movie["type"] === "series" ? "- Tập " . e($continueWatching["episode_number"]) : e($continueWatching["episode_title"]) ?>
                    </a>
                <?php endif; ?>

                <button class="detail-action-btn <?= $isFavorite ? "is-favorite" : "" ?>" id="favoriteBtn" type="button"
                    data-favorite-toggle data-movie-id="<?= $movieId ?>"
                    aria-pressed="<?= $isFavorite ? "true" : "false" ?>">
                    <?= $isFavorite ? "♥" : "♡" ?> Yêu thích
                </button>
                <?php if ($isLoggedIn): ?>
                    <a class="detail-action-btn" id="addListBtn" href="account.php?tab=favorites">＋ Danh sách</a>
                <?php else: ?>
                    <button class="detail-action-btn" id="addListBtn" type="button" data-open-login>＋ Danh sách</button>
                <?php endif; ?>
                <button class="detail-action-btn" id="shareBtn" type="button">↗ Chia sẻ</button>
                <button class="detail-action-btn" id="commentBtn" type="button">💬 Bình luận</button>
            </section>

            <section class="detail-tabs">
                <button class="detail-tab active" type="button" data-target="#episode-section">Tập phim</button>
                <button class="detail-tab" type="button" data-target="#gallery-section">Gallery</button>
                <button class="detail-tab" type="button" data-target="#ost-section">OST</button>
                <button class="detail-tab" type="button" data-target="#actors-section">Diễn viên</button>
                <button class="detail-tab" type="button" data-target="#related-section">Đề xuất</button>
            </section>

            <section class="movie-section episode-section detail-tab-panel active" id="episode-section">
                <div class="episode-head">
                    <h2>☰ Phần 1</h2>

                    <div class="episode-options">
                        <span>Phụ đề</span>
                        <span>4K</span>
                    </div>
                </div>

                <div class="episode-list" id="episodeList">
                    <?php if (empty($episodes)): ?>
                        <p class="detail-empty">Chưa có tập phim.</p>
                    <?php else: ?>
                        <?php foreach ($episodes as $episode): ?>
                            <?php $episodeTitle = $movie["type"] === "series" ? "Tập " . e($episode["episode_number"]) : e($episode["title"]); ?>
                            <?php if ($watchAccess["allowed"]): ?>
                                <a class="episode-btn" href="watch.php?movie_id=<?= $movieId ?>&episode_id=<?= $episode["id"] ?>">
                                    ▶ <?= $episodeTitle ?>
                                </a>
                            <?php elseif ($watchAccess["code"] === "login_required"): ?>
                                <a class="episode-btn episode-btn--locked" href="#authModal" data-open-login>
                                    🔒 <?= $episodeTitle ?>
                                </a>
                            <?php else: ?>
                                <span class="episode-btn episode-btn--locked">
                                    🔒 <?= $episodeTitle ?>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section class="movie-section gallery-section detail-tab-panel" id="gallery-section">
                <div class="section-head">
                    <h2>Gallery</h2>
                </div>

                <div class="detail-placeholder">Đang cập nhật</div>
            </section>

            <section class="movie-section ost-section detail-tab-panel" id="ost-section">
                <div class="section-head">
                    <h2>OST</h2>
                </div>

                <div class="detail-placeholder">Đang cập nhật</div>
            </section>

            <section class="movie-section actors-section detail-tab-panel" id="actors-section">
                <div class="section-head">
                    <h2>Diễn viên</h2>
                </div>

                <div class="detail-placeholder" id="actorsPanel">
                    <?= e(implode(", ", array_column($actors, "name")) ?: "Đang cập nhật") ?>
                </div>
            </section>

            <section class="movie-section related-section detail-tab-panel" id="related-section">
                <div class="section-head">
                    <h2>Có thể bạn sẽ thích</h2>
                    <a href="#" class="view-more">Xem thêm</a>
                </div>

                <div class="related-movie-grid" id="relatedMovies">
                    <?php if (empty($relatedMovies)): ?>
                        <p class="detail-empty">Chưa có phim đề xuất phù hợp.</p>
                    <?php else: ?>
                        <?php foreach ($relatedMovies as $related): ?>
                            <a class="related-movie-card" href="movie-detail.php?id=<?= $related["id"] ?>">
                                <img src="<?= e(assetPath($related["poster"], "/assets/images/poster_movie.jpg")) ?>"
                                    alt="<?= e($related["title"]) ?>">
                                <h3><?= e($related["title"]) ?></h3>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section class="movie-section comment-section">
                <div class="section-head">
                    <h2>Bình luận</h2>
                </div>

                <?php if (!$isLoggedIn): ?>
                    <p class="comment-login-note">
                        Vui lòng <a href="/login.php">đăng nhập</a> để bình luận.
                    </p>

                    <div class="comment-box">
                        <textarea placeholder="Viết bình luận" disabled></textarea>
                        <div class="comment-actions">
                            <span>0 / 1000</span>
                            <button type="button" disabled>Gửi</button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="comment-box">
                        <textarea id="commentInput" placeholder="Viết bình luận"></textarea>
                        <div class="comment-actions">
                            <span id="commentCount">0 / 1000</span>
                            <button id="sendCommentBtn" type="button">Gửi</button>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="comment-empty <?= !empty($comments) ? "has-comments" : "" ?>" id="commentList">
                    <?php if (empty($comments)): ?>
                        Chưa có bình luận nào
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <?php
                            $commentTime = !empty($comment["created_at"]) ? strtotime($comment["created_at"]) : false;
                            $canDeleteComment = $currentUserId !== null && (
                                $currentUserId === (int) $comment["user_id"] ||
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

            <section class="movie-section rating-section" id="rating-section">
                <div class="section-head">
                    <h2>Đánh giá phim</h2>
                </div>

                <div class="rating-box">
                    <div class="rating-summary">
                        <strong
                            id="ratingAverage"><?= $ratingCount > 0 ? e(number_format($ratingAverage, 1) . " / 5") : "Chưa có đánh giá" ?></strong>
                        <span id="ratingTotal"><?= (int) $ratingCount ?> lượt đánh giá</span>
                    </div>

                    <?php if (!$isLoggedIn): ?>
                        <p class="rating-login-note">
                            Vui lòng <a href="/login.php">đăng nhập</a> để đánh giá.
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
                            <?= $userRating !== null ? "Bạn đã đánh giá " . (int) $userRating . " sao." : "Hãy chọn sao để đánh giá." ?>
                        </p>
                    <?php endif; ?>
                </div>
            </section>
        </section>
    </section>
</main>

<script src="/assets/js/main.js"></script>
<script src="/assets/js/movie-detail.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>