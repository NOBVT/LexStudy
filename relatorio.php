<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$flash = consumeFlash();
$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$studyProfile = getStudyProfile($userId);
$report = weeklyStudyReport($userId, $initialState, $studyProfile);
$summary = $report['summary'];
$assistantUsage = $report['usage']['assistant_today'];
$teacherUsage = $report['usage']['teacher_today'];
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatório Semanal - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Relatório semanal de estudo jurídico com progresso, riscos e plano de estudo.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-report">
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

        <main class="workspace">
            <header class="topbar report-hero">
                <div>
                    <span class="eyebrow">Relatório Semanal</span>
                    <h1>Decide a próxima semana com dados.</h1>
                    <p>Este painel junta aulas, IA, erros, casos e domínio para não estudares por impulso.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="foco.php">Foco</a>
                    <a class="ghost-btn" href="sala.php">Sala</a>
                    <a class="ghost-btn" href="dominio.php">Domínio</a>
                    <a class="ghost-btn" href="caderno.php">Caderno</a>
                    <a class="ghost-btn" href="billing.php">Planos</a>
                    <a class="primary-btn" href="<?= e($report['weakest']['target'] ?? 'sala.php') ?>">Treinar foco</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            <?php endif; ?>

            <?php if (!$currentUser): ?>
                <div class="notice warning">
                    Estás em modo local. O relatório semanal fica mais preciso quando entras numa conta, porque a base de dados guarda datas reais.
                </div>
            <?php endif; ?>

            <section class="weekly-command">
                <article class="weekly-score">
                    <span class="eyebrow"><?= e($report['range_label']) ?></span>
                    <strong><?= e($report['activity_score']) ?>%</strong>
                    <p>Índice de consistência</p>
                    <div class="meter"><span style="width: <?= e($report['activity_score']) ?>%"></span></div>
                </article>
                <article class="weekly-focus">
                    <span class="eyebrow">Competência mais fraca</span>
                    <h2><?= e($report['weakest']['name'] ?? 'Orientação') ?></h2>
                    <p><?= e($report['weakest']['next'] ?? 'Concluir uma aula introdutória e rever o diagnóstico.') ?></p>
                    <a class="notice-link" href="<?= e($report['weakest']['target'] ?? 'sala.php') ?>">Abrir treino recomendado</a>
                </article>
                <article class="weekly-domain">
                    <span class="eyebrow">Domínio global</span>
                    <strong><?= e($report['domain_average']) ?>%</strong>
                    <p><?= e($report['domain_level']) ?></p>
                    <a class="notice-link" href="dominio.php">Ver mapa completo</a>
                </article>
            </section>

            <section class="weekly-grid">
                <article>
                    <span>Aulas</span>
                    <strong><?= e($summary['lessons']) ?></strong>
                    <small>concluídas esta semana</small>
                </article>
                <article>
                    <span>IA</span>
                    <strong><?= e($summary['assistant_messages'] + $summary['teacher_questions']) ?></strong>
                    <small>perguntas feitas</small>
                </article>
                <article>
                    <span>Treino</span>
                    <strong><?= e($summary['mentor_sessions'] + $summary['cases'] + $summary['quiz']) ?></strong>
                    <small>sessões ativas</small>
                </article>
                <article>
                    <span>Revisão</span>
                    <strong><?= e($summary['cards'] + $summary['mistakes_resolved']) ?></strong>
                    <small>cartas ou erros revistos</small>
                </article>
            </section>

            <section class="report-layout">
                <article class="report-panel">
                    <div class="section-heading">
                        <span class="eyebrow">Leitura honesta</span>
                        <h2>O que está a funcionar</h2>
                    </div>
                    <ul class="report-list">
                        <?php foreach ($report['wins'] as $item): ?>
                            <li><?= e($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </article>

                <article class="report-panel risk">
                    <div class="section-heading">
                        <span class="eyebrow">Riscos</span>
                        <h2>O que pode travar o estudo</h2>
                    </div>
                    <ul class="report-list">
                        <?php foreach ($report['risks'] as $item): ?>
                            <li><?= e($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            </section>

            <section class="section-block">
                <div class="section-heading split">
                    <div>
                        <span class="eyebrow">Plano da próxima semana</span>
                        <h2>Três tarefas, sem complicar.</h2>
                    </div>
                    <small>Baseado no teu domínio, erros e uso recente.</small>
                </div>
                <div class="report-plan">
                    <?php foreach ($report['next_week'] as $step): ?>
                        <a class="plan-step" href="<?= e($step['target']) ?>">
                            <span><?= e($step['label']) ?></span>
                            <strong><?= e($step['title']) ?></strong>
                            <small><?= e($step['detail']) ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="report-layout compact">
                <article class="report-panel">
                    <div class="section-heading">
                        <span class="eyebrow">Uso diário da IA</span>
                        <h2>Limites e margem</h2>
                    </div>
                    <div class="usage-row">
                        <div>
                            <strong>Assistente</strong>
                            <small><?= e($assistantUsage['used']) ?> usadas, <?= e($assistantUsage['remaining']) ?> disponíveis hoje</small>
                        </div>
                        <div class="meter"><span style="width: <?= e($assistantUsage['percent']) ?>%"></span></div>
                    </div>
                    <div class="usage-row">
                        <div>
                            <strong>Professor IA</strong>
                            <small><?= e($teacherUsage['used']) ?> usadas, <?= e($teacherUsage['remaining']) ?> disponíveis hoje</small>
                        </div>
                        <div class="meter"><span style="width: <?= e($teacherUsage['percent']) ?>%"></span></div>
                    </div>
                </article>

                <article class="report-panel">
                    <div class="section-heading">
                        <span class="eyebrow">Estado do caderno</span>
                        <h2>Erros ainda abertos</h2>
                    </div>
                    <div class="report-kpi-line">
                        <span>Novos</span>
                        <strong><?= e($summary['mistakes_new']) ?></strong>
                    </div>
                    <div class="report-kpi-line">
                        <span>Abertos</span>
                        <strong><?= e($summary['mistakes_open']) ?></strong>
                    </div>
                    <div class="report-kpi-line">
                        <span>Resolvidos esta semana</span>
                        <strong><?= e($summary['mistakes_resolved']) ?></strong>
                    </div>
                    <a class="secondary-btn" href="caderno.php">Abrir caderno</a>
                </article>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
