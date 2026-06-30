<?php
require_once __DIR__ . "/db.php";

function upcoming_notification_poster(?string $poster, ?string $posterPath): string
{
    $poster = trim((string) $poster);
    if ($poster !== "") {
        if (preg_match('/^https?:\/\//i', $poster) || str_starts_with($poster, "/")) {
            return $poster;
        }

        return "/" . ltrim($poster, "/");
    }

    $posterPath = trim((string) $posterPath);
    if ($posterPath !== "") {
        return "https://image.tmdb.org/t/p/w185" . $posterPath;
    }

    return "/assets/images/poster_movie.jpg";
}

function get_upcoming_notifications(?int $userId = null): array
{
    try {
        $pdo = getDatabaseConnection();
        $params = [];
        $readSelect = "NULL AS read_at";
        $readJoin = "";

        if ($userId !== null && $userId > 0) {
            $readSelect = "notification_reads.read_at";
            $readJoin = "
            LEFT JOIN notification_reads
                ON notification_reads.schedule_id = schedules.id
                AND notification_reads.user_id = :user_id
            ";
            $params["user_id"] = $userId;
        }

        $stmt = $pdo->prepare("
            SELECT
                schedules.id,
                schedules.movie_id,
                schedules.release_date,
                schedules.show_time,
                schedules.note,
                schedules.is_published,
                movies.title,
                movies.poster,
                movies.poster_path,
                {$readSelect}
            FROM schedules
            INNER JOIN movies ON schedules.movie_id = movies.id
            {$readJoin}
            WHERE schedules.release_date >= CURDATE()
            ORDER BY schedules.release_date ASC, schedules.show_time IS NULL ASC, schedules.show_time ASC, schedules.id ASC
            LIMIT 20
        ");
        $stmt->execute($params);

        return array_map(
            static function (array $row): array {
                $showTime = (string) ($row["show_time"] ?? "");

                return [
                    "id" => (int) $row["id"],
                    "movie_id" => (int) $row["movie_id"],
                    "title" => (string) $row["title"],
                    "poster" => upcoming_notification_poster($row["poster"] ?? null, $row["poster_path"] ?? null),
                    "show_date" => (string) $row["release_date"],
                    "show_time" => $showTime !== "" ? substr($showTime, 0, 5) : "",
                    "note" => (string) ($row["note"] ?? ""),
                    "status" => !empty($row["is_published"]) ? "published" : "draft",
                    "is_read" => !empty($row["read_at"]),
                ];
            },
            $stmt->fetchAll()
        );
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        return [];
    }
}

function get_published_upcoming_notifications(?int $userId = null): array
{
    $notifications = array_filter(
        get_upcoming_notifications($userId),
        static fn(array $notification): bool => ($notification["status"] ?? "") === "published"
    );

    usort(
        $notifications,
        static function (array $first, array $second): int {
            $firstDateTime = ($first["show_date"] ?? "") . " " . ($first["show_time"] ?? "");
            $secondDateTime = ($second["show_date"] ?? "") . " " . ($second["show_time"] ?? "");

            return strcmp($firstDateTime, $secondDateTime);
        }
    );

    return array_values($notifications);
}

