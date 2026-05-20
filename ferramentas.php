<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$data = appData();
$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$flash = consumeFlash();
$methodsByTarget = [];
foreach ($data['methods'] as $method) {
    $methodsByTarget[$method['target']] = $method;
}

$toolGroups = [
    'Orientação e método' => ['comecar.php', 'plano.php', 'disciplinas.php', 'materia.php', 'revisao.php', 'trilho.php', 'dominio.php', 'mentor.php'],
    'Investigação e escrita' => ['pesquisa.php', 'teses.php', 'pecas.php', 'conceitos.php', 'library.php', 'acordaos.php'],
    'Treino e revisão' => ['exame.php', 'simulator.php', 'cases.php', 'flashcards.php', 'caderno.php'],
    'Sistema' => ['telemovel.php', 'billing.php', 'lexium-preview.php'],
];

$extraTools = [
    'billing.php' => [
        'name' => 'Planos',
        'meta' => 'Conta',
        'description' => 'Gerir plano Free ou Plus e limites de uso da IA.',
        'target' => 'billing.php',
    ],
    'telemovel.php' => [
        'name' => 'Telemóvel',
        'meta' => 'Acesso móvel',
        'description' => 'Abrir o LexStudy no telemóvel, instalar atalho e estudar fora do computador.',
        'target' => 'telemovel.php',
    ],
    'lexium-preview.php' => [
        'name' => 'Preview Lexium',
        'meta' => 'Design',
        'description' => 'Protótipo isolado da estrutura visual Auditório Judicial.',
        'target' => 'lexium-preview.php',
    ],
];
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ferramentas - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Centro de ferramentas da plataforma LexStudy.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-tools">
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
            <header class="topbar tools-hero">
                <div>
                    <span class="eyebrow">Ferramentas</span>
                    <h1>Todos os módulos, sem encher a navegação principal.</h1>
                    <p>O topo fica reservado ao essencial. Aqui ficam as ferramentas específicas para escrever, pesquisar, treinar e rever.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="index.php">Painel</a>
                    <a class="ghost-btn" href="relatorio.php">Relatório</a>
                    <a class="primary-btn" href="foco.php">Começar sessão</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            <?php endif; ?>

            <section class="tool-cluster-grid tools-directory">
                <?php foreach ($toolGroups as $groupName => $targets): ?>
                    <article class="tool-cluster">
                        <span class="eyebrow"><?= e($groupName) ?></span>
                        <div class="compact-method-list">
                            <?php foreach ($targets as $target): ?>
                                <?php
                                $method = $methodsByTarget[$target] ?? $extraTools[$target] ?? null;
                                if (!$method) {
                                    continue;
                                }
                                ?>
                                <a class="tool-link" href="<?= e($method['target']) ?>">
                                    <span><?= e($method['meta']) ?></span>
                                    <strong><?= e($method['name']) ?></strong>
                                    <small><?= e($method['description']) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
