<?php

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'ok' => true,
    'service' => 'LexStudy',
    'php' => PHP_VERSION,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
