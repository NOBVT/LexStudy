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
define('DB_PORT', dbEnvValue('DB_PORT', '3306'));
define('DB_NAME', dbEnvValue('DB_NAME', 'lexstudy'));
define('DB_USER', dbEnvValue('DB_USER', 'root'));
define('DB_PASS', dbEnvValue('DB_PASS', ''));
define('DB_CHARSET', dbEnvValue('DB_CHARSET', 'utf8mb4'));
define('DB_SSL_MODE', strtolower(dbEnvValue('DB_SSL_MODE', '')));
define('DB_SSL_CA', dbEnvValue('DB_SSL_CA', '/etc/ssl/certs/ca-certificates.crt'));

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        if (DB_SSL_MODE !== '' && DB_SSL_MODE !== 'disabled') {
            if (defined('PDO::MYSQL_ATTR_SSL_CA') && DB_SSL_CA !== '') {
                $options[PDO::MYSQL_ATTR_SSL_CA] = DB_SSL_CA;
            }
            if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = DB_SSL_MODE === 'verify';
            }
        }

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
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
