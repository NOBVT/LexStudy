<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$data = appData();
$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$flash = consumeFlash();
$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$studyProfile = getStudyProfile($userId);
$dailyPlan = recommendedDailyPlan($data, $studyProfile);
$domain = getStudyDomainMap($userId, $initialState, $studyProfile);
$report = weeklyStudyReport($userId, $initialState, $studyProfile);
$mistakesOpen = (int)($report['summary']['mistakes_open'] ?? 0);
$sessionLabel = (string)($studyProfile['session_time'] ?? '25 minutos');
$sessionMinutes = preg_match('/(\d+)/', $sessionLabel, $matches) ? (int)$matches[1] : 25;
$sessionMinutes = max(10, min(60, $sessionMinutes));
$weakest = $domain['weakest'] ?? null;
$focusTasks = array_slice($dailyPlan, 0, 3);
if ($weakest) {
    $focusTasks[] = [
        'label' => 'Ponto fraco',
        'title' => (string)$weakest['name'],
        'detail' => (string)$weakest['next'],
        'target' => (string)$weakest['target'],
    ];
}
if ($mistakesOpen > 0) {
    $focusTasks[] = [
        'label' => 'Revisão',
        'title' => 'Fechar um erro do caderno',
        'detail' => 'Antes de consumir matéria nova, corrige uma falha que já apareceu.',
        'target' => 'caderno.php',
    ];
}
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modo Foco - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Sessão de estudo focada com temporizador, tarefas e apontamentos.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-focus">
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
                <small><?= e($progress['current']) ?> / <?= e($progress['required']) ?> XP</small>
            </div>
        </aside>

        <main class="workspace" data-focus-root data-focus-minutes="<?= e($sessionMinutes) ?>">
            <header class="topbar focus-hero">
                <div>
                    <span class="eyebrow">Modo Foco</span>
                    <h1>Uma sessão, uma tarefa, zero dispersão.</h1>
                    <p>Usa esta página quando já sabes que tens de estudar, mas queres evitar saltar entre ferramentas sem método.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="sala.php">Sala</a>
                    <a class="ghost-btn" href="relatorio.php">Relatório</a>
                    <a class="ghost-btn" href="dominio.php">Domínio</a>
                    <a class="ghost-btn" href="assistant.php">Assistente</a>
                    <a class="primary-btn" href="<?= e($focusTasks[0]['target'] ?? 'sala.php') ?>">Abrir primeira tarefa</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            <?php endif; ?>

            <section class="focus-layout">
                <article class="focus-timer-card">
                    <span class="eyebrow">Temporizador</span>
                    <div class="timer-ring" data-timer-ring style="--timer-progress: 0%;">
                        <strong data-timer-display><?= e(str_pad((string)$sessionMinutes, 2, '0', STR_PAD_LEFT)) ?>:00</strong>
                        <small data-timer-status>Pronto para começar</small>
                    </div>
                    <div class="timer-presets" aria-label="Duração da sessão">
                        <button type="button" data-preset-minutes="12">12</button>
                        <button type="button" data-preset-minutes="25">25</button>
                        <button type="button" data-preset-minutes="45">45</button>
                    </div>
                    <div class="timer-actions">
                        <button class="primary-btn" type="button" data-timer-action="start">Iniciar</button>
                        <button class="secondary-btn" type="button" data-timer-action="pause">Pausar</button>
                        <button class="ghost-btn" type="button" data-timer-action="reset">Repor</button>
                    </div>
                </article>

                <article class="focus-session-card">
                    <div class="section-heading">
                        <span class="eyebrow">Protocolo da sessão</span>
                        <h2><?= e($sessionLabel) ?> com ordem.</h2>
                    </div>
                    <div class="focus-protocol">
                        <span><strong>1</strong> Escolher uma tarefa</span>
                        <span><strong>2</strong> Estudar sem trocar de ferramenta</span>
                        <span><strong>3</strong> Fechar com uma nota ou pergunta</span>
                    </div>
                    <div class="focus-signal">
                        <span>Foco sugerido</span>
                        <strong><?= e($weakest['name'] ?? ($studyProfile['focus'] ?? 'Fundamentos de Direito')) ?></strong>
                        <small><?= e($weakest['next'] ?? 'Começa por uma aula curta e fecha com uma pergunta concreta.') ?></small>
                    </div>
                </article>
            </section>

            <section class="focus-board">
                <article class="focus-panel">
                    <div class="section-heading split">
                        <div>
                            <span class="eyebrow">Tarefas da sessão</span>
                            <h2>Marca só o que concluíres agora.</h2>
                        </div>
                        <button class="secondary-btn" type="button" data-focus-clear>Limpar marcas</button>
                    </div>
                    <div class="focus-task-list">
                        <?php foreach ($focusTasks as $index => $task): ?>
                            <label class="focus-task">
                                <input type="checkbox" data-focus-task="<?= e($index) ?>">
                                <span>
                                    <small><?= e($task['label']) ?></small>
                                    <strong><?= e($task['title']) ?></strong>
                                    <em><?= e($task['detail']) ?></em>
                                </span>
                                <a href="<?= e($task['target']) ?>">Abrir</a>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="focus-panel notes">
                    <div class="section-heading">
                        <span class="eyebrow">Apontamentos rápidos</span>
                        <h2>Guarda o essencial, não transcrevas a aula.</h2>
                    </div>
                    <textarea data-focus-notes rows="11" placeholder="Ex.: Hoje percebi que primeiro separo factos, depois norma, depois aplicação. Dúvida: quando é que a exceção muda a conclusão?"></textarea>
                    <div class="focus-note-actions">
                        <button class="secondary-btn" type="button" data-copy-notes>Copiar notas</button>
                        <a class="primary-btn" href="assistant.php">Levar dúvida ao assistente</a>
                    </div>
                    <small data-notes-status>Guardado neste browser.</small>
                </article>
            </section>
        </main>
    </div>
    <script src="assets/foco.js"></script>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
