<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$studyProfile = getStudyProfile($userId);
$missions = studyMissionBoard($userId, $initialState, $studyProfile);
$nextMission = $missions['next'];
$flash = consumeFlash();
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Missões Jurídicas - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Missões diárias e semanais do LexStudy para transformar estudo jurídico em rotina concreta.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-missions">
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
                <small><?= e($missions['done_today']) ?> / <?= e($missions['total_today']) ?> missões hoje</small>
            </div>
        </aside>

        <main class="workspace">
            <header class="topbar missions-hero">
                <div>
                    <span class="eyebrow">Missões Jurídicas</span>
                    <h1>O estudo fica mais simples quando o dia tem alvos claros.</h1>
                    <p>Este painel junta aulas, revisão, prática, memória e IA numa rotina curta. A meta é estudar com direção, não abrir ferramentas ao acaso.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="revisao.php">Revisão</a>
                    <a class="ghost-btn" href="sala.php">Sala</a>
                    <a class="ghost-btn" href="caderno.php">Erros</a>
                    <a class="primary-btn" href="<?= e($nextMission['target']) ?>">Próxima missão</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            <?php endif; ?>

            <?php if (!$currentUser): ?>
                <div class="notice warning">
                    Entra numa conta para as missões usarem progresso real. Sem conta, o painel mostra a lógica base.
                </div>
            <?php endif; ?>

            <section class="mission-command" aria-label="Estado das missões">
                <article class="mission-score">
                    <span class="eyebrow"><?= e($missions['phase']) ?></span>
                    <strong><?= e($missions['today_percent']) ?>%</strong>
                    <p><?= e($missions['done_today']) ?> de <?= e($missions['total_today']) ?> missões feitas hoje</p>
                    <div class="meter"><span style="width: <?= e($missions['today_percent']) ?>%"></span></div>
                </article>

                <article class="mission-next">
                    <span class="eyebrow">Próxima ação</span>
                    <h2><?= e($nextMission['title']) ?></h2>
                    <p><?= e($nextMission['detail']) ?></p>
                    <a class="notice-link" href="<?= e($nextMission['target']) ?>">Abrir módulo</a>
                </article>

                <article class="mission-signal">
                    <span class="eyebrow">Ponto fraco</span>
                    <h2><?= e($missions['weakest']['name'] ?? 'Orientação') ?></h2>
                    <p><?= e($missions['weakest']['next'] ?? 'Concluir uma aula introdutória e rever o diagnóstico.') ?></p>
                    <a class="notice-link" href="<?= e($missions['weakest']['target'] ?? 'dominio.php') ?>">Treinar competência</a>
                </article>
            </section>

            <section class="mission-layout">
                <article class="mission-panel daily">
                    <div class="section-heading split">
                        <div>
                            <span class="eyebrow">Hoje</span>
                            <h2>Cinco missões curtas.</h2>
                        </div>
                        <small><?= e($missions['open_mistakes']) ?> erros abertos</small>
                    </div>
                    <div class="mission-list">
                        <?php foreach ($missions['daily'] as $mission): ?>
                            <a class="mission-item <?= $mission['done'] ? 'is-done' : '' ?>" href="<?= e($mission['target']) ?>">
                                <span><?= e($mission['label']) ?></span>
                                <strong><?= e($mission['title']) ?></strong>
                                <small><?= e($mission['detail']) ?></small>
                                <em><?= e($mission['current']) ?> / <?= e($mission['goal']) ?> · <?= e($mission['xp']) ?> XP</em>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </article>

                <aside class="mission-panel ritual">
                    <span class="eyebrow">Ritual de 20 minutos</span>
                    <h2>Quando não souberes o que fazer, segue isto.</h2>
                    <div class="ritual-steps">
                        <?php foreach ($missions['ritual'] as $step): ?>
                            <div>
                                <span><?= e($step['time']) ?></span>
                                <strong><?= e($step['title']) ?></strong>
                                <small><?= e($step['detail']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </aside>
            </section>

            <section class="section-block">
                <div class="section-heading split">
                    <div>
                        <span class="eyebrow">Semana</span>
                        <h2>Objetivos mínimos para evoluir sem confusão.</h2>
                    </div>
                    <a class="secondary-btn" href="relatorio.php">Ver relatório</a>
                </div>
                <div class="weekly-mission-grid">
                    <?php foreach ($missions['weekly'] as $mission): ?>
                        <a class="weekly-mission" href="<?= e($mission['target']) ?>">
                            <span><?= e($mission['label']) ?></span>
                            <strong><?= e($mission['title']) ?></strong>
                            <small><?= e($mission['detail']) ?></small>
                            <div class="meter"><span style="width: <?= e($mission['percent']) ?>%"></span></div>
                            <em><?= e($mission['current']) ?> / <?= e($mission['goal']) ?></em>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
