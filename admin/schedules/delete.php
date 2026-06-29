<?php
session_start();
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../includes/db.php";

$_auth_needed = true;
require_once __DIR__ . "/../auth_check.php";

try {
    $pdo = getDatabaseConnection();

    $schedule_id = (int)filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if (!$schedule_id) {
        header("Location: /admin/movies/index.php?error=Invalid schedule ID");
        exit;
    }

    // Get schedule and its movie
    $stmt = $pdo->prepare("
        SELECT s.id, m.id as movie_id, m.title
        FROM schedules s
        JOIN movies m ON m.id = s.movie_id
        WHERE s.id = :id
    ");
    $stmt->execute(['id' => $schedule_id]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        header("Location: /admin/movies/index.php?error=Schedule not found");
        exit;
    }

    // Delete schedule
    $delete_stmt = $pdo->prepare("DELETE FROM schedules WHERE id = :id");
    $delete_stmt->execute(['id' => $schedule_id]);

    header("Location: /admin/schedules/index.php?movie_id=" . $schedule['movie_id'] . "&success=Schedule deleted");
    exit;

} catch (Exception $e) {
    header("Location: /admin/movies/index.php?error=" . urlencode($e->getMessage()));
    exit;
}
