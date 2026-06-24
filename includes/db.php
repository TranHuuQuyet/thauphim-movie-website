<?php

require_once __DIR__ . '/config.php';

function getDatabaseConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
$dsn = sprintf(
    'mysql:host=%s;port=3307;dbname=%s;charset=%s',
    DB_HOST,
    DB_NAME,
    DB_CHARSET
);

        $pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return $pdo;

    } catch (PDOException $e) {

        die(
            '<h2>PDO ERROR</h2>' .
            '<p><strong>DSN:</strong> ' . $dsn . '</p>' .
            '<p><strong>User:</strong> ' . DB_USER . '</p>' .
            '<p><strong>Message:</strong> ' . $e->getMessage() . '</p>'
        );

    }
}