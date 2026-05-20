<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$data = appData();
$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$studyProfile = getStudyProfile($userId);
$domain = getStudyDomainMap($userId, $initialState, $studyProfile);
$weakest = $domain['weakest'];
$evidence = $domain['evidence'];
$flash = consumeFlash();
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mapa de Domínio - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Mapa profissional de competências jurídicas para orientar o estudo de Direito.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-domain">
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
            <header class="topbar domain-hero">
                <div>
                    <span class="eyebrow">Mapa de Domínio Jurídico</span>
                    <h1>Transforma ferramentas em competências.</h1>
                    <p>Uma plataforma profissional não mede só cliques. Mede se estás a construir as capacidades certas para Direito.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="sala.php">Sala</a>
                    <a class="ghost-btn" href="foco.php">Foco</a>
                    <a class="ghost-btn" href="index.php#diagnostico">Diagnóstico</a>
                    <a class="ghost-btn" href="relatorio.php">Relatório</a>
                    <a class="ghost-btn" href="caderno.php">Caderno</a>
                    <a class="ghost-btn" href="mentor.php">Mentor</a>
                    <a class="primary-btn" href="<?= e($weakest['target'] ?? 'trilho.php') ?>">Atacar ponto fraco</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            <?php endif; ?>

            <section class="domain-summary">
                <article class="domain-score">
                    <span class="eyebrow">Maturidade global</span>
                    <strong><?= e($domain['average']) ?>%</strong>
                    <p><?= e($domain['level']) ?></p>
                    <div class="meter"><span style="width: <?= e($domain['average']) ?>%"></span></div>
                </article>
                <article class="domain-next">
                    <span class="eyebrow">Ponto a atacar</span>
                    <h2><?= e($weakest['name'] ?? 'Orientação') ?></h2>
                    <p><?= e($weakest['next'] ?? 'Começa pelo diagnóstico e segue uma rotina curta.') ?></p>
                    <a class="notice-link" href="<?= e($weakest['target'] ?? 'index.php#diagnostico') ?>">Abrir tarefa recomendada</a>
                </article>
                <article class="domain-protocol">
                    <span class="eyebrow">Lógica do sistema</span>
                    <div class="protocol-line">
                        <span>Compreender</span>
                        <span>Aplicar</span>
                        <span>Escrever</span>
                        <span>Rever</span>
                    </div>
                </article>
            </section>

            <section class="section-block">
                <div class="section-heading">
                    <span class="eyebrow">Competências nucleares</span>
                    <h2>O curso fica menos confuso quando separas capacidades.</h2>
                </div>
                <div class="competency-grid">
                    <?php foreach ($domain['items'] as $item): ?>
                        <article class="competency-card">
                            <div class="competency-head">
                                <span><?= e($item['level']) ?></span>
                                <strong><?= e($item['score']) ?>%</strong>
                            </div>
                            <h2><?= e($item['name']) ?></h2>
                            <p><?= e($item['promise']) ?></p>
                            <div class="meter"><span style="width: <?= e($item['score']) ?>%"></span></div>
                            <small><?= e($item['next']) ?></small>
                            <a class="secondary-btn" href="<?= e($item['target']) ?>">Treinar</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="domain-evidence">
                <article>
                    <span>Conceitos pesquisados</span>
                    <strong><?= e($evidence['concepts']) ?></strong>
                </article>
                <article>
                    <span>Aulas concluídas</span>
                    <strong><?= e($evidence['completed_lessons']) ?></strong>
                </article>
                <article>
                    <span>Acórdãos/fontes</span>
                    <strong><?= e($evidence['judgments'] + $evidence['sources']) ?></strong>
                </article>
                <article>
                    <span>Casos resolvidos</span>
                    <strong><?= e($evidence['case_sessions']) ?></strong>
                </article>
                <article>
                    <span>Peças/cartas</span>
                    <strong><?= e($evidence['drafts'] + $evidence['reviewed_cards']) ?></strong>
                </article>
                <article>
                    <span>Erros ativos</span>
                    <strong><?= e($evidence['open_mistakes']) ?></strong>
                </article>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
