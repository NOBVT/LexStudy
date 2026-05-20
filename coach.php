<?php

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Pedido inválido.']);
    exit;
}

$case = findCaseById((int)($payload['case_id'] ?? 0));
$role = ($payload['role'] ?? 'defense') === 'attack' ? 'attack' : 'defense';
$argument = trim((string)($payload['argument'] ?? ''));

if (!$case || mb_strlen($argument, 'UTF-8') < 15) {
    http_response_code(422);
    echo json_encode(['error' => 'Escolhe um caso e escreve uma tese com conteúdo suficiente.']);
    exit;
}

$system = 'És um treinador de estudo jurídico em Portugal. Avalia a argumentação do estudante de forma educativa, direta e prática. Não dês aconselhamento jurídico definitivo. Foca estrutura, prova, fragilidades, contra-argumentos e próximos passos de estudo.';
$roleLabel = $role === 'attack' ? 'acusação/autor' : 'defesa/réu';
$prompt = "Caso: {$case['title']}\nÁrea: {$case['area']}\nFactos: " . implode('; ', $case['facts']) . "\nReferências de estudo: " . implode('; ', $case['legal_refs']) . "\nPapel do estudante: {$roleLabel}\nTese do estudante:\n{$argument}\n\nResponde em português europeu, com 4 blocos curtos: força da tese, falha principal, contra-argumento provável e próximo exercício.";

$aiResponse = callAnthropicAPI([
    ['role' => 'user', 'content' => $prompt],
], $system);

if ($aiResponse !== null) {
    echo json_encode([
        'mode' => 'ai',
        'html' => '<strong>Análise por IA</strong><p>' . nl2br(e($aiResponse)) . '</p>',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'mode' => 'local',
    'html' => localCoachAnalysis($case, $role, $argument),
], JSON_UNESCAPED_UNICODE);
