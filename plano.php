<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$data = appData();
$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$profile = getStudyProfile($userId);
$domain = getStudyDomainMap($userId, $initialState, $profile);
$flash = consumeFlash();
$planInput = normalizeWeeklyPlanInput($_SESSION['weekly_plan_input'] ?? [], $profile);
$weeklyPlan = buildSmartWeeklyPlan($userId, $planInput, $profile, $domain);
$coachNote = $_SESSION['weekly_plan_coach'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['weekly_action'])) {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        setFlash('Sessão expirada. Tenta novamente.', 'error');
        header('Location: plano.php');
        exit;
    }

    try {
        $planInput = normalizeWeeklyPlanInput($_POST, $profile);
        $weeklyPlan = buildSmartWeeklyPlan($userId, $planInput, $profile, $domain);
        $_SESSION['weekly_plan_input'] = $planInput;
        $_SESSION['weekly_plan'] = $weeklyPlan;

        if (!empty($_POST['ai_coach'])) {
            $coachNote = weeklyPlanCoach($userId, $weeklyPlan);
            $_SESSION['weekly_plan_coach'] = $coachNote;
        } else {
            $coachNote = null;
            unset($_SESSION['weekly_plan_coach']);
        }

        setFlash('Plano semanal atualizado.', 'success');
    } catch (Throwable $e) {
        setFlash($e->getMessage(), 'error');
    }
}
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plano Semanal - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Plano semanal inteligente para estudar Direito com aulas, casos, revisão e IA.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-weekly-plan">
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

        <main class="workspace">
            <header class="topbar weekly-hero">
                <div>
                    <span class="eyebrow">Plano Semanal Inteligente</span>
                    <h1>Transforma horas soltas numa semana de estudo real.</h1>
                    <p>Define tempo, objetivo e área. O LexStudy distribui aulas, casos, revisão e memória ativa sem te mandar apenas “ler mais”.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="comecar.php">Começar do Zero</a>
                    <a class="ghost-btn" href="revisao.php">Revisão</a>
                    <a class="primary-btn" href="#plano-gerado">Ver plano</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            <?php endif; ?>

            <section class="weekly-layout">
                <form method="post" class="weekly-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="weekly_action" value="generate">
                    <span class="eyebrow">Configuração</span>
                    <h2>Como vai ser a tua semana?</h2>
                    <div class="weekly-form-grid">
                        <label>
                            <span>Horas disponíveis</span>
                            <input type="number" name="hours" min="2" max="20" value="<?= e($planInput['hours']) ?>">
                        </label>
                        <label>
                            <span>Dias de estudo</span>
                            <input type="number" name="days" min="2" max="7" value="<?= e($planInput['days']) ?>">
                        </label>
                        <label>
                            <span>Objetivo</span>
                            <select name="goal">
                                <?php foreach (['orientacao' => 'Orientação', 'casos' => 'Casos práticos', 'memoria' => 'Memória', 'escrita' => 'Escrita jurídica', 'exames' => 'Exames'] as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= $planInput['goal'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            <span>Área</span>
                            <select name="area">
                                <?php foreach (['base' => 'Fundamentos', 'civil' => 'Civil', 'penal' => 'Penal', 'constitucional' => 'Constitucional', 'trabalho' => 'Trabalho'] as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= $planInput['area'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            <span>Ritmo</span>
                            <select name="rhythm">
                                <?php foreach (['leve' => 'Leve', 'equilibrado' => 'Equilibrado', 'intenso' => 'Intenso'] as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= $planInput['rhythm'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <label class="weekly-ai-toggle">
                        <input type="checkbox" name="ai_coach" value="1" <?= $coachNote ? 'checked' : '' ?>>
                        <span>Pedir crítica da IA ao plano</span>
                    </label>
                    <button class="primary-btn" type="submit">Gerar plano</button>
                </form>

                <aside class="weekly-summary">
                    <span class="eyebrow">Diagnóstico</span>
                    <h2><?= e($weeklyPlan['title']) ?></h2>
                    <p><?= e($weeklyPlan['summary']) ?></p>
                    <div class="weekly-metrics">
                        <article><span>Tempo</span><strong><?= e($weeklyPlan['input']['hours']) ?>h</strong></article>
                        <article><span>Sessões</span><strong><?= e(count($weeklyPlan['days'])) ?></strong></article>
                        <article><span>Sessão média</span><strong><?= e($weeklyPlan['duration']) ?>m</strong></article>
                    </div>
                    <small>Ponto fraco observado: <?= e($weeklyPlan['weakest']) ?></small>
                </aside>
            </section>

            <?php if ($coachNote): ?>
                <section class="weekly-coach">
                    <span class="eyebrow"><?= e($coachNote['ai_mode'] === 'gemini' ? 'Crítica da IA' : 'Crítica local') ?></span>
                    <h2><?= e($coachNote['coach_note']) ?></h2>
                    <p><strong>Ajuste:</strong> <?= e($coachNote['adjustment']) ?></p>
                    <ul class="clean-list">
                        <?php foreach ($coachNote['risks'] as $risk): ?><li><?= e($risk) ?></li><?php endforeach; ?>
                    </ul>
                    <p><strong>Primeira ação:</strong> <?= e($coachNote['first_action']) ?></p>
                </section>
            <?php endif; ?>

            <section id="plano-gerado" class="weekly-plan-grid">
                <?php foreach ($weeklyPlan['days'] as $session): ?>
                    <article class="weekly-day-card">
                        <span><?= e($session['day']) ?> · <?= e($session['kind']) ?></span>
                        <h2><?= e($session['title']) ?></h2>
                        <p><?= e($session['reason']) ?></p>
                        <div>
                            <strong><?= e($session['duration']) ?> min</strong>
                            <small>Produto final: <?= e($session['output']) ?></small>
                        </div>
                        <a class="secondary-btn" href="<?= e($session['target']) ?>">Abrir módulo</a>
                    </article>
                <?php endforeach; ?>
            </section>

            <section class="weekly-rules">
                <span class="eyebrow">Regras de execução</span>
                <div class="weekly-rule-grid">
                    <?php foreach ($weeklyPlan['rules'] as $rule): ?>
                        <article><?= e($rule) ?></article>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
