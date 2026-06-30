<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

$pdo = getDatabaseConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'POST') {
    apiError('Method khong duoc ho tro', 405);
}

$user = apiRequireUser($pdo);
$payload = apiReadJson();
$scheduleIds = $payload['schedule_ids'] ?? [];

if (!is_array($scheduleIds)) {
    apiError('Danh sach thong bao khong hop le', 400);
}

$scheduleIds = array_values(array_unique(array_filter(
    array_map(static fn(mixed $id): int => (int) $id, $scheduleIds),
    static fn(int $id): bool => $id > 0
)));

if (empty($scheduleIds)) {
    apiSuccess(['marked_read' => 0]);
}

$placeholders = implode(',', array_fill(0, count($scheduleIds), '?'));
$stmt = $pdo->prepare("
    SELECT id
    FROM schedules
    WHERE id IN ({$placeholders})
      AND is_published = 1
      AND release_date >= CURDATE()
");
$stmt->execute($scheduleIds);
$validScheduleIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

if (empty($validScheduleIds)) {
    apiSuccess(['marked_read' => 0]);
}

$insertStmt = $pdo->prepare("
    INSERT INTO notification_reads (user_id, schedule_id, read_at)
    VALUES (?, ?, NOW())
    ON DUPLICATE KEY UPDATE read_at = NOW()
");

foreach ($validScheduleIds as $scheduleId) {
    $insertStmt->execute([(int) $user['id'], $scheduleId]);
}

apiSuccess(['marked_read' => count($validScheduleIds)]);
