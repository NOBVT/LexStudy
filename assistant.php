<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();
ensureLearningTables();

$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$dbReady = dbSchemaIsReady();
$flash = consumeFlash();
$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$geminiStatus = geminiRuntimeStatus();
$subscription = currentUserSubscription($userId);
$assistantUsage = aiUsageSummary($userId, 'assistant_message');
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assistant_action'])) {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        setFlash('Sessão expirada. Recarrega a página.', 'error');
        header('Location: assistant.php');
        exit;
    }

    $action = (string)$_POST['assistant_action'];

    try {
        if ($action === 'new_thread') {
            $thread = createLegalAssistantThread($userId);
            header('Location: assistant.php?chat=' . (int)$thread['id']);
            exit;
        }

        if ($action === 'delete_thread') {
            deleteLegalAssistantThread($userId, max(1, (int)($_POST['thread_id'] ?? 0)));
            header('Location: assistant.php');
            exit;
        }

        if ($action === 'send_message') {
            $threadId = (int)($_POST['thread_id'] ?? 0);
            if ($threadId <= 0) {
                $thread = createLegalAssistantThread($userId);
                $threadId = (int)$thread['id'];
            }
            $thread = sendLegalAssistantMessage($userId, $threadId, (string)($_POST['message'] ?? ''));
            header('Location: assistant.php?chat=' . (int)$thread['id'] . '#latest');
            exit;
        }
    } catch (Throwable $e) {
        setFlash($e->getMessage(), 'error');
        header('Location: assistant.php');
        exit;
    }
}

$threads = getLegalAssistantThreads($userId);
if (!$threads) {
    $created = createLegalAssistantThread($userId);
    $threads = getLegalAssistantThreads($userId);
    $selectedThreadId = (int)$created['id'];
} else {
    $selectedThreadId = isset($_GET['chat']) ? (int)$_GET['chat'] : (int)$threads[0]['id'];
}

$activeThread = getLegalAssistantThread($userId, $selectedThreadId);
if (!$activeThread && $threads) {
    $selectedThreadId = (int)$threads[0]['id'];
    $activeThread = getLegalAssistantThread($userId, $selectedThreadId);
}

$messages = $activeThread['messages'] ?? [];
$styleNotes = trim((string)($activeThread['style_notes'] ?? ''));
$globalStyleNotes = getLegalAssistantProfile($userId);
$quickPrompts = [
    'Explica este tema como se eu estivesse a preparar um exame.',
    'Cria uma resposta jurídica com factos, normas, aplicação e conclusão.',
    'Que perguntas devo fazer para resolver este caso como advogado?',
];
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assistente Jurídico - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Chat jurídico com histórico para estudar Direito, estruturar casos e preparar respostas.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="assistant-page">
    <div class="app-shell">
        <aside class="sidebar" aria-label="Dock principal">
            <a class="brand" href="index.php" aria-label="LexStudy">
                <span class="brand-mark"><img src="assets/lexstudy-mark.svg" alt=""></span>
                <span>
                    <strong>LexStudy</strong>
                    <small>Direito aplicado</small>
                </span>
            </a>

            <nav class="nav-list">
                <a href="index.php" data-short="P">Painel</a>
                <a href="sala.php" data-short="S">Sala</a>
                <a href="foco.php" data-short="FO">Foco</a>
                <a href="assistant.php" data-short="AI">Assistente</a>
                <a href="relatorio.php" data-short="R">Relatório</a>
                <a href="ferramentas.php" data-short="FX">Ferramentas</a>
            </nav>

            <div class="profile-panel">
                <span class="eyebrow">Nível atual</span>
                <strong><?= e(getLevelTitle($level)) ?></strong>
                <div class="meter"><span style="width: <?= e($progress['percent']) ?>%"></span></div>
                <small><?= e($initialState['xp']) ?> XP</small>
            </div>
        </aside>

        <main class="workspace assistant-workspace">
            <header class="assistant-topbar">
                <div>
                    <span class="eyebrow">Inteligência Lex</span>
                    <h1>Assistente jurídico</h1>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="billing.php"><?= e($subscription['is_plus'] ? 'Plus ativo' : 'Plano Free') ?></a>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="assistant_action" value="new_thread">
                        <button class="primary-btn" type="submit">Nova conversa</button>
                    </form>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            <?php endif; ?>

            <?php if (!$dbReady): ?>
                <div class="notice warning">
                    A base de dados não está pronta. O histórico funciona só nesta sessão do browser.
                </div>
            <?php elseif (!$currentUser): ?>
                <div class="notice warning">
                    Sem conta, o histórico fica guardado só nesta sessão. Entra no painel para guardar conversas permanentemente.
                </div>
            <?php endif; ?>

            <?php if (!$geminiStatus['configured']): ?>
                <div class="notice warning">
                    A inteligência artificial avançada ainda não está ativa. O assistente continua em modo local.
                </div>
            <?php elseif (!$geminiStatus['curl']): ?>
                <div class="notice error">
                    A inteligência artificial está configurada, mas a extensão cURL do PHP não está ativa no XAMPP.
                </div>
            <?php elseif ($geminiStatus['last_error'] !== ''): ?>
                <div class="notice error">
                    A inteligência artificial devolveu um erro: <?= e($geminiStatus['last_error']) ?>
                </div>
            <?php endif; ?>

            <div class="usage-strip">
                <span>Mensagens hoje: <?= e($assistantUsage['used']) ?> / <?= e($assistantUsage['limit']) ?></span>
                <div class="meter"><span style="width: <?= e($assistantUsage['percent']) ?>%"></span></div>
                <?php if (!$subscription['is_plus']): ?>
                    <a class="notice-link" href="billing.php">Aumentar limite</a>
                <?php endif; ?>
            </div>

            <section class="assistant-layout" aria-label="Assistente jurídico com histórico">
                <aside class="assistant-rail">
                    <div class="assistant-rail-head">
                        <div>
                            <span class="eyebrow">Histórico</span>
                            <h2>Conversas</h2>
                        </div>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="assistant_action" value="new_thread">
                            <button class="rail-new-btn" type="submit" aria-label="Nova conversa">+</button>
                        </form>
                    </div>

                    <div class="thread-list">
                        <?php foreach ($threads as $thread): ?>
                            <article class="thread-card <?= (int)$thread['id'] === $selectedThreadId ? 'is-active' : '' ?>">
                                <a href="assistant.php?chat=<?= e($thread['id']) ?>">
                                    <span><?= e($thread['area'] ?: 'Geral') ?></span>
                                    <strong><?= e($thread['title']) ?></strong>
                                    <small><?= e((int)($thread['message_count'] ?? 0)) ?> mensagens</small>
                                </a>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="assistant_action" value="delete_thread">
                                    <input type="hidden" name="thread_id" value="<?= e($thread['id']) ?>">
                                    <button class="ghost-btn thread-delete" type="submit" aria-label="Apagar conversa">Apagar</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </aside>

                <section class="assistant-chat-panel">
                    <div class="assistant-chat-header">
                        <div>
                            <span class="eyebrow"><?= e($activeThread['area'] ?? 'Geral') ?></span>
                            <h2><?= e($activeThread['title'] ?? 'Nova conversa') ?></h2>
                        </div>
                        <span class="assistant-status"><?= e($geminiStatus['configured'] && $geminiStatus['curl'] ? 'Inteligência ativa' : 'Modo local') ?></span>
                    </div>

                    <div class="assistant-thread" aria-label="Mensagens da conversa">
                        <?php if (!$messages): ?>
                            <div class="assistant-empty-state">
                                <span class="assistant-empty-mark"><img src="assets/lexstudy-mark.svg" alt=""></span>
                                <strong>Como posso ajudar no teu estudo de Direito?</strong>
                                <p>Faz uma pergunta, cola um caso prático, pede uma estrutura de resposta ou prepara uma peça jurídica.</p>
                                <div class="quick-prompts is-centered" aria-label="Perguntas rápidas">
                                    <?php foreach ($quickPrompts as $prompt): ?>
                                        <form method="post">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                            <input type="hidden" name="assistant_action" value="send_message">
                                            <input type="hidden" name="thread_id" value="<?= e($selectedThreadId) ?>">
                                            <button class="secondary-btn" name="message" value="<?= e($prompt) ?>" type="submit"><?= e($prompt) ?></button>
                                        </form>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $index => $message): ?>
                                <article class="assistant-message <?= e(($message['role'] ?? '') === 'user' ? 'is-user' : 'is-assistant') ?>" <?= $index === array_key_last($messages) ? 'id="latest"' : '' ?>>
                                    <strong><?= e(($message['role'] ?? '') === 'user' ? 'Tu' : 'Assistente Jurídico') ?></strong>
                                    <p><?= nl2br(e((string)($message['content'] ?? ''))) ?></p>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form method="post" class="assistant-composer" data-assistant-composer>
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="assistant_action" value="send_message">
                        <input type="hidden" name="thread_id" value="<?= e($selectedThreadId) ?>">
                        <label class="assistant-input-shell">
                            <span>Pergunta ao assistente</span>
                            <textarea name="message" rows="1" placeholder="Pergunta qualquer coisa de Direito..." data-assistant-input></textarea>
                            <button class="send-orb" type="submit" aria-label="Enviar mensagem">↑</button>
                        </label>
                        <?php if ($messages): ?>
                        <div class="quick-prompts" aria-label="Perguntas rápidas">
                            <?php foreach ($quickPrompts as $prompt): ?>
                                <button class="secondary-btn" name="message" value="<?= e($prompt) ?>" type="submit"><?= e($prompt) ?></button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </form>
                </section>
            </section>
        </main>
    </div>
    <script src="assets/assistant.js"></script>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
