<?php

define('APP_NAME', 'LexStudy');
define('APP_VERSION', '1.0.0');

function localEnvValues(): array
{
    static $values = null;
    if ($values !== null) {
        return $values;
    }

    $values = [];
    $path = __DIR__ . '/.env';
    if (!is_readable($path)) {
        return $values;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines)) {
        return $values;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '') {
            continue;
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $values[$key] = $value;
    }

    return $values;
}

function envValue(string $key, string $default = ''): string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        $local = localEnvValues();
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $local[$key] ?? $default;
    }

    return is_string($value) ? trim($value) : $default;
}

define('APP_URL', envValue('APP_URL', 'http://localhost/Spector'));
define('APP_ENV', envValue('APP_ENV', 'local'));

if (APP_ENV === 'production') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
}

define('ANTHROPIC_API_KEY', envValue('ANTHROPIC_API_KEY'));
define('ANTHROPIC_MODEL', envValue('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'));
define('ANTHROPIC_MAX_TOKENS', 1500);

define('GEMINI_API_KEY', envValue('GEMINI_API_KEY'));
define('GEMINI_MODEL', envValue('GEMINI_MODEL', 'gemini-2.5-flash'));
define('AI_MAX_SOURCE_CHARS', 45000);

define('STRIPE_SECRET_KEY', envValue('STRIPE_SECRET_KEY'));
define('STRIPE_PRICE_PLUS_MONTHLY', envValue('STRIPE_PRICE_PLUS_MONTHLY'));
define('STRIPE_WEBHOOK_SECRET', envValue('STRIPE_WEBHOOK_SECRET'));
define('FREE_ASSISTANT_MESSAGES_DAILY', 10);
define('PLUS_ASSISTANT_MESSAGES_DAILY', 200);

// XP por atividade
define('XP_CASE_SOLVED',   150);
define('XP_CASE_MSG',       30);
define('XP_QUIZ_CORRECT',   25);
define('XP_QUIZ_PERFECT',  100);
define('XP_CARD_REVIEW',    10);
define('XP_LOGIN_DAILY',    20);

// Nível = XP necessário (índice = nível - 1)
define('LEVELS_XP', [0, 500, 1200, 2500, 4500, 7500, 12000, 18000, 26000, 36000]);
define('LEVELS_TITLE', [
    'Caloiro', 'Estagiário', 'Procurador Júnior', 'Procurador',
    'Advogado', 'Advogado Sénior', 'Doutor em Direito',
    'Juiz', 'Desembargador', 'Supremo Magistrado'
]);

define('SESSION_LIFETIME', 86400 * 30); // 30 dias

date_default_timezone_set('Europe/Lisbon');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(SESSION_LIFETIME);
    session_start();
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
