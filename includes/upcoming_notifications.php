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

function get_upcoming_notifications(): array
{
    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->query("
            SELECT
                schedules.id,
                schedules.movie_id,
                schedules.release_date,
                schedules.note,
                schedules.is_published,
                movies.title,
                movies.poster,
                movies.poster_path
            FROM schedules
            INNER JOIN movies ON schedules.movie_id = movies.id
            WHERE schedules.release_date >= CURDATE()
            ORDER BY schedules.release_date ASC, schedules.id ASC
            LIMIT 20
        ");

        return array_map(
            static function (array $row): array {
                return [
                    "id" => (int) $row["id"],
                    "movie_id" => (int) $row["movie_id"],
                    "title" => (string) $row["title"],
                    "poster" => upcoming_notification_poster($row["poster"] ?? null, $row["poster_path"] ?? null),
                    "show_date" => (string) $row["release_date"],
                    "show_time" => "",
                    "note" => (string) ($row["note"] ?? ""),
                    "status" => !empty($row["is_published"]) ? "published" : "draft",
                ];
            },
            $stmt->fetchAll()
        );
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        return [];
    }
}

function get_published_upcoming_notifications(): array
{
    $notifications = array_filter(
        get_upcoming_notifications(),
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

