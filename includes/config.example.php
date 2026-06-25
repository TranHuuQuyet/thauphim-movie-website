<?php
if (!defined("APP_BASE_PATH")) {
    define("APP_BASE_PATH", "/");
}

define("TMDB_API_KEY", "replace-with-your-tmdb-api-key");

define("DB_HOST", "localhost");
define("DB_PORT", 3306);
define("DB_NAME", "thauphim");
define("DB_USER", "thauphim_user");
define("DB_PASS", "replace-with-a-strong-password");
define("DB_CHARSET", "utf8mb4");

$TMDB_COUNTRIES = [
    ["code" => "VN", "name" => "Vietnam"],
    ["code" => "KR", "name" => "Korea"],
    ["code" => "CN", "name" => "China"],
    ["code" => "JP", "name" => "Japan"],
    ["code" => "US", "name" => "United States"],
];
