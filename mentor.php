<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$studyProfile = getStudyProfile($userId);
$domain = getStudyDomainMap($userId, $initialState, $studyProfile);
$areas = mentorAreas();
$session = $_SESSION['mentor_session'] ?? null;
$report = is_array($session) && !empty($session['completed']) ? mentorReport($session) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mentor_action'])) {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        setFlash('Sessão expirada. Tenta novamente.', 'error');
        header('Location: mentor.php');
        exit;
    }

    try {
        $action = (string)$_POST['mentor_action'];
        if ($action === 'start') {
            $session = startMentorSession($userId, (string)($_POST['area_key'] ?? 'civil'));
            setFlash('Sessão do Mentor iniciada.', 'success');
        } elseif ($action === 'answer') {
            if (!is_array($session)) {
                throw new RuntimeException('Inicia uma sessão antes de responder.');
            }
            $session = evaluateMentorAnswer($userId, $session, (string)($_POST['mentor_answer'] ?? ''));
            setFlash(!empty($session['completed']) ? 'Sessão concluída. Relatório gerado.' : 'Resposta corrigida. Continua para consolidar.', 'success');
        } elseif ($action === 'reset') {
            unset($_SESSION['mentor_session']);
            setFlash('Sessão reiniciada.', 'success');
        }
    } catch (Throwable $e) {
        setFlash($e->getMessage(), 'error');
    }

    header('Location: mentor.php');
    exit;
}

$flash = consumeFlash();
$session = $_SESSION['mentor_session'] ?? null;
$report = is_array($session) && !empty($session['completed']) ? mentorReport($session) : null;
$turns = is_array($session) ? (array)($session['turns'] ?? []) : [];
$step = is_array($session) ? min((int)($session['step'] ?? 1), (int)($session['max_steps'] ?? 3)) : 1;
$maxSteps = is_array($session) ? (int)($session['max_steps'] ?? 3) : 3;
$progressPercent = is_array($session) ? clampScore((count($turns) / max(1, $maxSteps)) * 100) : 0;
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mentor Jurídico - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Mentor jurídico com sessões guiadas, correção e relatório de estudo.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-mentor">
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
                <small><?= e($domain['average']) ?>% domínio</small>
            </div>
        </aside>

        <main class="workspace">
            <header class="topbar mentor-hero">
                <div>
                    <span class="eyebrow">Mentor Jurídico</span>
                    <h1>Treina como se tivesses um professor ao lado.</h1>
                    <p>Uma sessão curta explica a matéria, apresenta um caso, corrige a tua resposta e manda os erros para revisão.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="sala.php">Sala</a>
                    <a class="ghost-btn" href="caderno.php">Caderno</a>
                    <a class="ghost-btn" href="dominio.php">Domínio</a>
                    <a class="primary-btn" href="assistant.php">Assistente livre</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            <?php endif; ?>

            <?php if (!is_array($session)): ?>
                <section class="mentor-start">
                    <div class="mentor-start-copy">
                        <span class="eyebrow">Escolhe uma área</span>
                        <h2>O Mentor não te dá só uma resposta. Obriga-te a raciocinar.</h2>
                        <p>Começa com um tema. A plataforma cria uma explicação curta, um caso prático e uma pergunta de exame. Depois corrige a tua resposta por critérios.</p>
                    </div>
                    <div class="mentor-start-grid">
                        <?php foreach ($areas as $key => $area): ?>
                            <form class="mentor-area-card" method="post">
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                <input type="hidden" name="mentor_action" value="start">
                                <input type="hidden" name="area_key" value="<?= e($key) ?>">
                                <span><?= e($area['area']) ?></span>
                                <h2><?= e(ucfirst($area['concept'])) ?></h2>
                                <p><?= e($area['case']) ?></p>
                                <button class="secondary-btn" type="submit">Iniciar sessão</button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php else: ?>
                <section class="mentor-layout">
                    <article class="mentor-session-card">
                        <div class="mentor-session-head">
                            <div>
                                <span class="eyebrow"><?= e($session['area'] ?? 'Geral') ?></span>
                                <h2><?= e($session['title'] ?? 'Sessão Mentor') ?></h2>
                            </div>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                <input type="hidden" name="mentor_action" value="reset">
                                <button class="ghost-btn" type="submit">Nova sessão</button>
                            </form>
                        </div>

                        <div class="mentor-progress">
                            <span>Passo <?= e($step) ?> de <?= e($maxSteps) ?></span>
                            <div class="meter"><span style="width: <?= e($progressPercent) ?>%"></span></div>
                        </div>

                        <div class="mentor-brief">
                            <div>
                                <span>Explicação curta</span>
                                <p><?= nl2br(e($session['lesson'] ?? '')) ?></p>
                            </div>
                            <div>
                                <span>Caso prático</span>
                                <p><?= e($session['case'] ?? '') ?></p>
                            </div>
                        </div>

                        <?php if (!$report): ?>
                            <form class="mentor-answer-form" method="post">
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                <input type="hidden" name="mentor_action" value="answer">
                                <label for="mentor_answer"><?= e($turns ? (($turns[array_key_last($turns)]['evaluation']['next_question'] ?? $session['question'] ?? 'Continua a resposta.')) : ($session['question'] ?? 'Responde ao caso.')) ?></label>
                                <textarea id="mentor_answer" name="mentor_answer" rows="7" placeholder="Escreve como num exame: factos, regra, aplicação e conclusão." required></textarea>
                                <div class="mentor-form-actions">
                                    <small>Enter cria nova linha. Usa o botão para enviar quando a resposta estiver pensada.</small>
                                    <button class="primary-btn" type="submit">Enviar para correção</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <article class="mentor-report">
                                <span class="eyebrow">Relatório final</span>
                                <h2><?= e($report['score']) ?>/20</h2>
                                <p><b>Conceito treinado:</b> <?= e($report['learned']) ?></p>
                                <p><b>Próximo passo:</b> <?= e($report['next']) ?></p>
                                <?php if (!empty($report['review'])): ?>
                                    <ul>
                                        <?php foreach ((array)$report['review'] as $item): ?>
                                            <li><?= e($item) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <div class="coach-actions">
                                    <a class="secondary-btn" href="caderno.php">Rever erros</a>
                                    <a class="secondary-btn" href="flashcards.php">Fixar conceito</a>
                                    <a class="primary-btn" href="dominio.php">Ver domínio</a>
                                </div>
                            </article>
                        <?php endif; ?>
                    </article>

                    <aside class="mentor-timeline">
                        <span class="eyebrow">Correções</span>
                        <?php if ($turns): ?>
                            <?php foreach (array_reverse($turns) as $turn): ?>
                                <?php $evaluation = (array)($turn['evaluation'] ?? []); ?>
                                <article class="mentor-turn">
                                    <strong><?= e((int)($evaluation['score_20'] ?? 0)) ?>/20</strong>
                                    <p><?= e($evaluation['feedback'] ?? 'Resposta corrigida.') ?></p>
                                    <?php if (!empty($evaluation['gaps'])): ?>
                                        <small>Falta: <?= e(implode('; ', (array)$evaluation['gaps'])) ?></small>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <article class="mentor-turn empty">
                                <strong>Ainda sem correção</strong>
                                <p>Responde ao primeiro caso para o Mentor avaliar o teu raciocínio.</p>
                            </article>
                        <?php endif; ?>
                    </aside>
                </section>
            <?php endif; ?>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
