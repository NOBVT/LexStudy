<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: billing.php');
    exit;
}

if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
    setFlash('Sessão expirada. Tenta novamente.', 'error');
    header('Location: billing.php');
    exit;
}

$currentUser = currentUser();
if (!$currentUser) {
    setFlash('Tens de criar conta antes de subscrever.', 'error');
    header('Location: billing.php');
    exit;
}

try {
    $url = createStripeCheckoutSession((int)$currentUser['id']);
    header('Location: ' . $url);
    exit;
} catch (Throwable $e) {
    setFlash($e->getMessage(), 'error');
    header('Location: billing.php');
    exit;
}
