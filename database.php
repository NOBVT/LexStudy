<?php

function dbEnvValue(string $key, string $default = ''): string
{
    if (function_exists('envValue')) {
        return envValue($key, $default);
    }

    $value = getenv($key);
    if ($value === false || $value === '') {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }

    return is_string($value) ? trim($value) : $default;
}

define('DB_HOST', dbEnvValue('DB_HOST', 'localhost'));
define('DB_NAME', dbEnvValue('DB_NAME', 'lexstudy'));
define('DB_USER', dbEnvValue('DB_USER', 'root'));
define('DB_PASS', dbEnvValue('DB_PASS', ''));
define('DB_CHARSET', dbEnvValue('DB_CHARSET', 'utf8mb4'));

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Erro de ligação à base de dados: ' . $e->getMessage(), 0, $e);
        }
    }
    return $pdo;
}

function tryDB(): ?PDO
{
    try {
        return getDB();
    } catch (Throwable) {
        return null;
    }
}

function dbIsAvailable(): bool
{
    return tryDB() instanceof PDO;
}

function dbSchemaIsReady(): bool
{
    $db = tryDB();
    if (!$db) {
        return false;
    }

    try {
        $stmt = $db->query("SHOW TABLES LIKE 'users'");
        return (bool)$stmt->fetchColumn();
    } catch (Throwable) {
        return false;
    }
}
