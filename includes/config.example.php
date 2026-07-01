<?php
if (!defined("APP_BASE_PATH")) {
    $projectDirectory = basename(dirname(__DIR__));
    $scriptName = str_replace("\\", "/", (string) ($_SERVER["SCRIPT_NAME"] ?? ""));
    $projectBasePath = "/" . trim($projectDirectory, "/") . "/";

    define(
        "APP_BASE_PATH",
        str_starts_with($scriptName, $projectBasePath) ? $projectBasePath : "/"
    );
}

if (!defined("APP_DEBUG")) {
    define("APP_DEBUG", false);
}

if (!defined("APP_URL")) {
    define("APP_URL", "https://fnbstore.store/");
}

if (!function_exists("app_url")) {
    function app_url(string $path = ""): string
    {
        return APP_BASE_PATH . ltrim($path, "/");
    }
}

if (!function_exists("app_absolute_url")) {
    function app_absolute_url(string $path = ""): string
    {
        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        return rtrim(APP_URL, "/") . "/" . ltrim($path, "/");
    }
}

define("TMDB_API_KEY", "replace-with-your-tmdb-api-key");

define("DB_HOST", "localhost");
define("DB_PORT", 3306);
define("DB_NAME", "thauphim");
define("DB_USER", "thauphim_user");
define("DB_PASS", "replace-with-a-strong-password");
define("DB_CHARSET", "utf8mb4");

define("MAIL_DRIVER", "smtp");
define("MAIL_FROM", "thauphim@fnbstore.store");
define("MAIL_FROM_NAME", "ThauPhim");
define("SMTP_HOST", "smtp.fnbstore.store");
define("SMTP_PORT", 465);
define("SMTP_ENCRYPTION", "ssl");
define("SMTP_USERNAME", "thauphim@fnbstore.store");
define("SMTP_PASSWORD", "replace-with-smtp-password");
define("PASSWORD_RESET_TTL_MINUTES", 60);

$TMDB_COUNTRIES = [
    ["code" => "VN", "name" => "Vietnam"],
    ["code" => "KR", "name" => "Korea"],
    ["code" => "CN", "name" => "China"],
    ["code" => "JP", "name" => "Japan"],
    ["code" => "US", "name" => "United States"],
];
