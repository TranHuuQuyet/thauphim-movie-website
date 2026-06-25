<?php
session_start();

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../includes/db.php";

$pdo = getDatabaseConnection();

function sendJson($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION["is_login"]) || empty($_SESSION["username"])) {
    sendJson([
        "success" => false,
        "message" => "Chưa đăng nhập"
    ], 401);
}

$input = json_decode(file_get_contents("php://input"), true);

$movieId = isset($input["movie_id"]) ? (int) $input["movie_id"] : 0;
$episodeId = isset($input["episode_id"]) ? (int) $input["episode_id"] : 0;
$progressSeconds = isset($input["progress_seconds"]) ? (int) $input["progress_seconds"] : 0;

if ($movieId <= 0 || $episodeId <= 0) {
    sendJson([
        "success" => false,
        "message" => "Thiếu movie_id hoặc episode_id"
    ], 400);
}

$stmt = $pdo->prepare("
    SELECT id
    FROM episodes
    WHERE id = ? AND movie_id = ?
    LIMIT 1
");
$stmt->execute([$episodeId, $movieId]);
$episode = $stmt->fetch();

if (!$episode) {
    sendJson([
        "success" => false,
        "message" => "Tập phim không hợp lệ"
    ], 400);
}

$progressSeconds = max(0, $progressSeconds);

$stmt = $pdo->prepare("
    SELECT id
    FROM users
    WHERE LOWER(username) = LOWER(?)
    LIMIT 1
");
$stmt->execute([$_SESSION["username"]]);
$user = $stmt->fetch();

if (!$user) {
    sendJson([
        "success" => false,
        "message" => "Không tìm thấy user"
    ], 404);
}   

$userId = (int) $user["id"];

$stmt = $pdo->prepare("
    INSERT INTO watch_history (user_id, movie_id, episode_id, progress_seconds, watched_at)
    VALUES (?, ?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE
        movie_id = VALUES(movie_id),
        progress_seconds = VALUES(progress_seconds),
        watched_at = NOW()
");

$stmt->execute([
    $userId,
    $movieId,
    $episodeId,
    $progressSeconds
]);

sendJson([
    "success" => true,
    "message" => "Đã lưu lịch sử xem"
]);