<?php

require_once __DIR__ . '/config.php';

$payload = file_get_contents('php://input') ?: '';
$signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if (STRIPE_WEBHOOK_SECRET === '') {
    http_response_code(503);
    echo 'Webhook secret not configured';
    exit;
}

if (!stripeSignatureIsValid($payload, $signature)) {
    http_response_code(400);
    echo 'Invalid signature';
    exit;
}

$event = json_decode($payload, true);
if (!is_array($event)) {
    http_response_code(400);
    echo 'Invalid payload';
    exit;
}

try {
    handleStripeWebhookEvent($event);
    http_response_code(200);
    echo 'ok';
} catch (Throwable) {
    http_response_code(500);
    echo 'Webhook handling failed';
}
