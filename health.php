<?php

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$checks = [
    'app' => APP_NAME,
    'version' => APP_VERSION,
    'environment' => APP_ENV,
    'php' => PHP_VERSION,
    'database_connected' => dbIsAvailable(),
    'schema_ready' => dbSchemaIsReady(),
    'curl' => function_exists('curl_init'),
    'mbstring' => function_exists('mb_strlen'),
    'fileinfo' => function_exists('mime_content_type'),
    'gemini_configured' => GEMINI_API_KEY !== '',
    'stripe_configured' => stripeBillingIsConfigured(),
];

$required = [
    $checks['database_connected'],
    $checks['schema_ready'],
    $checks['curl'],
    $checks['mbstring'],
    $checks['fileinfo'],
];

$ok = !in_array(false, $required, true);
http_response_code($ok ? 200 : 503);

echo json_encode([
    'ok' => $ok,
    'checks' => $checks,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
