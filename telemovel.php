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

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/Spector/')), '/');
$basePath = $basePath === '' ? '' : $basePath;
$currentUrl = $scheme . '://' . $host . $basePath . '/';
$mobileTemplateUrl = $scheme . '://IP_DO_MAC' . $basePath . '/';
$isLocalHost = str_contains($host, 'localhost') || str_contains($host, '127.0.0.1') || str_contains($host, '[::1]');
$hostName = gethostname() ?: 'o teu Mac';
$serverAddress = $_SERVER['SERVER_ADDR'] ?? '';
$maybeLanUrl = $serverAddress && !in_array($serverAddress, ['127.0.0.1', '::1'], true)
    ? $scheme . '://' . $serverAddress . $basePath . '/'
    : '';
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Telemóvel - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Acesso móvel à plataforma LexStudy para estudar no telemóvel.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-tools page-mobile-access">
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

        <main class="workspace mobile-access-workspace">
            <header class="topbar mobile-access-hero">
                <div>
                    <span class="eyebrow">Acesso móvel</span>
                    <h1>Leva o LexStudy para o telemóvel.</h1>
                    <p>Preparado para sessões rápidas no autocarro: revisão do dia, assistente, sala de estudo e foco com navegação inferior no ecrã pequeno.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="revisao.php">Revisão</a>
                    <a class="ghost-btn" href="assistant.php">Assistente</a>
                    <a class="primary-btn" href="sala.php">Sala de estudo</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            <?php endif; ?>

            <section class="mobile-access-grid">
                <article class="mobile-access-card">
                    <span class="eyebrow">Na mesma rede Wi-Fi</span>
                    <h2>Endereço para abrir no telemóvel</h2>
                    <?php if ($isLocalHost): ?>
                        <p>Este navegador está em localhost. No telemóvel, localhost aponta para o próprio telemóvel, por isso tens de usar o IP do Mac.</p>
                    <?php else: ?>
                        <p>Se o telemóvel estiver na mesma rede, este endereço deve funcionar diretamente.</p>
                    <?php endif; ?>
                    <div class="mobile-url-box">
                        <code><?= e($maybeLanUrl ?: $mobileTemplateUrl) ?></code>
                        <button class="ghost-btn" type="button" data-copy-value="<?= e($maybeLanUrl ?: $mobileTemplateUrl) ?>">Copiar</button>
                    </div>
                    <small>Mac detetado: <?= e($hostName) ?>. Se vires IP_DO_MAC, substitui pelo IP real do Mac.</small>
                </article>

                <article class="mobile-access-card">
                    <span class="eyebrow">Instalar como app</span>
                    <h2>Atalho no ecrã inicial</h2>
                    <div class="mobile-step-list">
                        <p><strong>iPhone:</strong> abre no Safari, toca em Partilhar e escolhe “Adicionar ao ecrã principal”.</p>
                        <p><strong>Android:</strong> abre no Chrome e escolhe “Adicionar ao ecrã principal” ou “Instalar app”.</p>
                    </div>
                    <p class="mobile-small-note">O modo instalável completo precisa de HTTPS. Em localhost funciona no Mac; num IP local do XAMPP pode abrir no browser, mas a instalação/offline pode ser limitada.</p>
                </article>

                <article class="mobile-access-card mobile-access-card-wide">
                    <span class="eyebrow">Rotina móvel</span>
                    <h2>Plano rápido para estudar fora de casa</h2>
                    <div class="mobile-routine">
                        <a href="revisao.php">
                            <span>01</span>
                            <strong>Rever matéria do dia</strong>
                            <small>Cola apontamentos e gera resumo, quiz e caso curto.</small>
                        </a>
                        <a href="assistant.php">
                            <span>02</span>
                            <strong>Perguntar ao professor IA</strong>
                            <small>Tira dúvidas em linguagem simples antes de voltares ao texto.</small>
                        </a>
                        <a href="flashcards.php">
                            <span>03</span>
                            <strong>Fixar conceitos</strong>
                            <small>Repetição curta para memória, sem sessões pesadas.</small>
                        </a>
                        <a href="foco.php">
                            <span>04</span>
                            <strong>Fechar uma sessão</strong>
                            <small>Regista foco e mantém continuidade no teu progresso.</small>
                        </a>
                    </div>
                </article>

                <article class="mobile-access-card">
                    <span class="eyebrow">Fora de casa</span>
                    <h2>Quando não estás na mesma Wi-Fi</h2>
                    <p>Para acesso fora da tua rede, o XAMPP local não chega. O caminho certo é publicar a app num servidor com PHP/MySQL ou usar um túnel privado enquanto ainda estás a desenvolver.</p>
                    <p class="mobile-small-note">Isto é a diferença entre “funciona no meu telemóvel em casa” e “funciona em qualquer lugar”.</p>
                </article>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
