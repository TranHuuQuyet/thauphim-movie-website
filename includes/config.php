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

define("TMDB_API_KEY", "9b4592d22d37d5f7ac7a5f6514fbdc0b");

$requestHost = strtolower((string) ($_SERVER["HTTP_HOST"] ?? ""));
$requestHost = explode(":", $requestHost, 2)[0];
$isLocalEnvironment = PHP_SAPI === "cli"
    || in_array($requestHost, ["localhost", "127.0.0.1"], true);

define("DB_HOST", $isLocalEnvironment ? "127.0.0.1" : "localhost");
define("DB_PORT", $isLocalEnvironment ? 3307 : 3306);
define("DB_NAME", $isLocalEnvironment ? "thauphim" : "zrzyh261k3va_thauphim");
define("DB_USER", $isLocalEnvironment ? "root" : "zrzyh261k3va_client01");
define("DB_PASS", $isLocalEnvironment ? "" : "ri4^Pttghf#KG,n$");
define("DB_CHARSET", "utf8mb4");

define("MAIL_DRIVER", "smtp");

define("MAIL_FROM", "thauphim@fnbstore.store");
define("MAIL_FROM_NAME", "ThauPhim");

define("SMTP_HOST", "mail.fnbstore.store"); 
define("SMTP_PORT", 587);
define("SMTP_ENCRYPTION", "ssl");

define("SMTP_USERNAME", "thauphim@fnbstore.store");
define("SMTP_PASSWORD", "MẬT_KHẨU_EMAIL");

define("PASSWORD_RESET_TTL_MINUTES", 60);
$TMDB_COUNTRIES = [
    ["code" => "VN", "name" => "Việt Nam"],
    ["code" => "KR", "name" => "Hàn Quốc"],
    ["code" => "CN", "name" => "Trung Quốc"],
    ["code" => "JP", "name" => "Nhật Bản"],
    ["code" => "TH", "name" => "Thái Lan"],
    ["code" => "US", "name" => "Hoa Kỳ"],
    ["code" => "GB", "name" => "Anh"],
    ["code" => "FR", "name" => "Pháp"],
    ["code" => "IN", "name" => "Ấn Độ"],
    ["code" => "TW", "name" => "Đài Loan"],
    ["code" => "HK", "name" => "Hồng Kông"],
    ["code" => "CA", "name" => "Canada"],
    ["code" => "ES", "name" => "Tây Ban Nha"],
    ["code" => "DE", "name" => "Đức"],
    ["code" => "IT", "name" => "Ý"],
    ["code" => "AU", "name" => "Úc"],
    ["code" => "PH", "name" => "Philippines"],
    ["code" => "ID", "name" => "Indonesia"],
    ["code" => "BR", "name" => "Brazil"],
    ["code" => "MX", "name" => "Mexico"],
];