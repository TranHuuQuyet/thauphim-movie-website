<?php

function get_upcoming_notifications(): array
{
    return [
        [
            "id" => 1,
            "movie_id" => 101,
            "title" => "Spider Man: Brand New Day",
            "poster" => "/thauphim-movie-website/assets/images/poster_movie.jpg",
            "show_date" => "2026-07-31",
            "show_time" => "20:00",
            "status" => "published",
        ],
        [
            "id" => 2,
            "movie_id" => 102,
            "title" => "Avengers: Doomsday",
            "poster" => "/thauphim-movie-website/assets/images/pic1.webp",
            "show_date" => "2026-12-18",
            "show_time" => "19:30",
            "status" => "published",
        ],
        [
            "id" => 3,
            "movie_id" => 103,
            "title" => "Fantastic Four",
            "poster" => "/thauphim-movie-website/assets/images/poster_movie.jpg",
            "show_date" => "2026-08-08",
            "show_time" => "18:45",
            "status" => "draft",
        ],
    ];
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
