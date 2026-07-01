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

if (!function_exists("app_config_value")) {
    function app_config_value(string $name, $default = "")
    {
        $value = getenv($name);
        if ($value !== false) {
            return $value;
        }

        if (array_key_exists($name, $_ENV)) {
            return $_ENV[$name];
        }

        if (array_key_exists($name, $_SERVER)) {
            return $_SERVER[$name];
        }

        return $default;
    }
}

$localConfig = __DIR__ . "/config.local.php";
if (is_file($localConfig)) {
    require_once $localConfig;
}

define("TMDB_API_KEY", "replace-with-your-tmdb-api-key");

define("DB_HOST", "localhost");
define("DB_PORT", 3306);
define("DB_NAME", "thauphim");
define("DB_USER", "thauphim_user");
define("DB_PASS", "replace-with-a-strong-password");
define("DB_CHARSET", "utf8mb4");

if (!defined("MAIL_DRIVER")) {
    define("MAIL_DRIVER", app_config_value("MAIL_DRIVER", "smtp"));
}

if (!defined("MAIL_FROM")) {
    define("MAIL_FROM", app_config_value("MAIL_FROM", "thauphim@fnbstore.store"));
}

if (!defined("MAIL_FROM_NAME")) {
    define("MAIL_FROM_NAME", app_config_value("MAIL_FROM_NAME", "ThauPhim"));
}

if (!defined("SMTP_HOST")) {
    define("SMTP_HOST", app_config_value("SMTP_HOST", "smtp.fnbstore.store"));
}

if (!defined("SMTP_PORT")) {
    define("SMTP_PORT", (int) app_config_value("SMTP_PORT", 465));
}

if (!defined("SMTP_ENCRYPTION")) {
    define("SMTP_ENCRYPTION", app_config_value("SMTP_ENCRYPTION", "ssl"));
}

if (!defined("SMTP_USERNAME")) {
    define("SMTP_USERNAME", app_config_value("SMTP_USERNAME", "thauphim@fnbstore.store"));
}

if (!defined("SMTP_PASSWORD")) {
    define("SMTP_PASSWORD", app_config_value("SMTP_PASSWORD", "replace-with-smtp-password"));
}

if (!defined("PASSWORD_RESET_TTL_MINUTES")) {
    define("PASSWORD_RESET_TTL_MINUTES", (int) app_config_value("PASSWORD_RESET_TTL_MINUTES", 60));
}

$TMDB_COUNTRIES = [
    ["code" => "VN", "name" => "Vietnam"],
    ["code" => "KR", "name" => "Korea"],
    ["code" => "CN", "name" => "China"],
    ["code" => "JP", "name" => "Japan"],
    ["code" => "US", "name" => "United States"],
];
