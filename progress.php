<?php

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}

$user = requireAuthUser();
if (!$user) {
    echo json_encode(['authenticated' => false]);
    exit;
}

if (!csrfIsValid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(419);
    echo json_encode(['error' => 'Token inválido.']);
    exit;
}

$db = tryDB();
if (!$db || !dbSchemaIsReady()) {
    http_response_code(503);
    echo json_encode(['error' => 'Base de dados indisponível.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Pedido inválido.']);
    exit;
}

$action = (string)($payload['action'] ?? '');
$details = is_array($payload['payload'] ?? null) ? $payload['payload'] : [];
$userId = (int)$user['id'];
$xp = 0;

try {
    if ($action === 'coach_feedback') {
        $xp = XP_CASE_MSG;
        if (!empty($details['mistake']) && is_array($details['mistake'])) {
            logStudyMistake($userId, $details['mistake']);
        }
    }

    if ($action === 'case_solved') {
        $caseId = max(1, (int)($details['case_id'] ?? 0));
        $role = ($details['role'] ?? 'defense') === 'attack' ? 'acusacao' : 'defesa';
        $check = $db->prepare('SELECT id FROM case_sessions WHERE user_id = ? AND case_id = ? AND role = ? AND completed = 1 LIMIT 1');
        $check->execute([$userId, $caseId, $role]);

        if (!$check->fetch()) {
            $xp = XP_CASE_SOLVED;
            $db->prepare('INSERT INTO case_sessions (user_id, case_id, role, messages, completed, xp_earned) VALUES (?, ?, ?, ?, 1, ?)')
                ->execute([$userId, $caseId, $role, '[]', $xp]);
            writeActivityLog($userId, 'case_solved', 'Caso prático resolvido', $xp);
        }
    }

    if ($action === 'card_mastered') {
        $cardId = max(1, (int)($details['card_id'] ?? 1));
        $xp = XP_CARD_REVIEW;
        $db->prepare(
            "INSERT INTO flashcard_progress (user_id, card_id, status, reviews, correct, next_review, last_reviewed)
             VALUES (?, ?, 'dominado', 1, 1, DATE_ADD(CURDATE(), INTERVAL 7 DAY), NOW())
             ON DUPLICATE KEY UPDATE status = 'dominado', reviews = reviews + 1, correct = correct + 1, next_review = DATE_ADD(CURDATE(), INTERVAL 7 DAY), last_reviewed = NOW()"
        )->execute([$userId, $cardId]);
        writeActivityLog($userId, 'cards_studied', 'Flashcard dominado', $xp);
    }

    if ($action === 'quiz_correct') {
        $quizIndex = max(0, (int)($details['quiz_index'] ?? 0));
        $quizId = max(0, (int)($details['quiz_id'] ?? $quizIndex));
        $xp = XP_QUIZ_CORRECT;
        $db->prepare('INSERT INTO quiz_attempts (user_id, area, questions_json, answers_json, score, total, percentage, xp_earned, time_taken) VALUES (?, ?, ?, ?, 1, 1, 100, ?, 0)')
            ->execute([$userId, 'Geral', json_encode([$quizId]), json_encode(['correct' => true]), $xp]);
        writeActivityLog($userId, 'quiz_done', 'Resposta correta no quiz', $xp);
    }

    if ($action === 'quiz_wrong' && !empty($details['mistake']) && is_array($details['mistake'])) {
        logStudyMistake($userId, $details['mistake']);
    }

    if ($xp > 0) {
        $xpStmt = $db->prepare('SELECT xp FROM users WHERE id = ?');
        $xpStmt->execute([$userId]);
        $currentXp = (int)($xpStmt->fetch()['xp'] ?? 0);
        $db->prepare('UPDATE users SET xp = xp + ?, level = ? WHERE id = ?')
            ->execute([$xp, getLevel($currentXp + $xp), $userId]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Não foi possível guardar o progresso.']);
    exit;
}

echo json_encode([
    'authenticated' => true,
    'state' => getUserStudyState($userId),
], JSON_UNESCAPED_UNICODE);
