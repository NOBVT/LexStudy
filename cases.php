<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$data = appData();
$currentUser = currentUser();
$dbReady = dbSchemaIsReady();
$flash = consumeFlash();
$initialState = getUserStudyState($currentUser ? (int)$currentUser['id'] : null);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Casos Práticos - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Treino de casos práticos de direito com defesa, acusação, feedback e relatório.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-cases">
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
                <strong data-level-title><?= e(getLevelTitle($level)) ?></strong>
                <div class="meter" aria-label="Progresso de nível">
                    <span style="width: <?= e($progress['percent']) ?>%"></span>
                </div>
                <small><span data-xp-current><?= e($progress['current']) ?></span> / <span data-xp-required><?= e($progress['required']) ?></span> XP</small>
            </div>
        </aside>

        <main id="top" class="workspace">
            <header class="topbar">
                <div>
                    <span class="eyebrow">Simulador de casos</span>
                    <h1>Treina como se estivesses a preparar uma peça processual.</h1>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="index.php">Painel</a>
                    <a class="ghost-btn" href="caderno.php">Caderno de erros</a>
                    <button class="primary-btn" type="button" data-random-case>Novo caso</button>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>">
                    <?= e($flash['message'] ?? '') ?>
                </div>
            <?php endif; ?>

            <?php if (!$dbReady): ?>
                <div class="notice warning">
                    Modo demonstração: importa `lexstudy.sql` para guardar progresso por conta.
                </div>
            <?php endif; ?>

            <section class="auth-strip" aria-label="Conta de utilizador">
                <?php if ($currentUser): ?>
                    <div>
                        <span class="eyebrow">Conta ativa</span>
                        <strong><?= e($currentUser['name']) ?></strong>
                        <small><?= e($currentUser['email']) ?></small>
                    </div>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="auth_action" value="logout">
                        <button class="secondary-btn" type="submit">Sair</button>
                    </form>
                <?php else: ?>
                    <div>
                        <span class="eyebrow">Sessão local</span>
                        <strong>Progresso só neste browser</strong>
                        <small>Cria conta no painel para guardar no servidor.</small>
                    </div>
                    <a class="secondary-btn" href="index.php">Entrar ou criar conta</a>
                <?php endif; ?>
            </section>

            <section class="stats-grid" aria-label="Indicadores">
                <article>
                    <span>XP total</span>
                    <strong data-total-xp><?= e($initialState['xp']) ?></strong>
                </article>
                <article>
                    <span>Sequência</span>
                    <strong><span data-streak><?= e($initialState['streak']) ?></span> dias</strong>
                </article>
                <article>
                    <span>Casos resolvidos</span>
                    <strong data-solved-cases><?= e($initialState['solvedCases']) ?></strong>
                </article>
                <article>
                    <span>Modo</span>
                    <strong><?= e(ANTHROPIC_API_KEY === '' ? 'Local' : 'IA') ?></strong>
                </article>
            </section>

            <section class="case-layout case-page-layout">
                <div class="case-list" aria-label="Lista de casos">
                    <?php foreach ($data['cases'] as $case): ?>
                        <button class="case-card" type="button" data-case-id="<?= e($case['id']) ?>">
                            <span style="--area-color: <?= e(areaColor($case['area_key'])) ?>"><?= e($case['area']) ?></span>
                            <strong><?= e($case['title']) ?></strong>
                            <small class="difficulty <?= e(difficultyClass($case['difficulty'])) ?>"><?= e($case['difficulty']) ?></small>
                        </button>
                    <?php endforeach; ?>
                </div>

                <article class="case-workbench">
                    <div class="case-header">
                        <div>
                            <span class="eyebrow" data-case-area><?= e($data['cases'][0]['area']) ?></span>
                            <h2 data-case-title><?= e($data['cases'][0]['title']) ?></h2>
                        </div>
                        <div class="segmented" role="group" aria-label="Papel no caso">
                            <button class="is-active" type="button" data-role="defense">Defesa</button>
                            <button type="button" data-role="attack">Acusação</button>
                        </div>
                    </div>

                    <p data-case-description><?= e($data['cases'][0]['description']) ?></p>

                    <div class="case-tool-grid">
                        <section>
                            <span class="eyebrow">Factos a usar</span>
                            <div class="fact-grid" data-case-facts>
                                <?php foreach ($data['cases'][0]['facts'] as $fact): ?>
                                    <span><?= e($fact) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </section>
                        <section>
                            <span class="eyebrow">Referências</span>
                            <div class="refs" data-case-refs>
                                <?php foreach ($data['cases'][0]['legal_refs'] as $ref): ?>
                                    <span><?= e($ref) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>

                    <div class="strategy-grid">
                        <div>
                            <span class="eyebrow">Estrutura recomendada</span>
                            <ol class="study-steps">
                                <li>Isola os factos prováveis.</li>
                                <li>Define o problema jurídico numa frase.</li>
                                <li>Escolhe dois argumentos fortes.</li>
                                <li>Antecipia o contra-argumento.</li>
                                <li>Fecha com pedido ou consequência.</li>
                            </ol>
                        </div>
                        <div class="coach-output compact" data-session-log>
                            <strong>Histórico da sessão</strong>
                            <p>Ainda não há análises nesta sessão.</p>
                        </div>
                    </div>

                    <label class="argument-box">
                        <span>A tua tese</span>
                        <textarea data-argument rows="9">A minha posição é que...</textarea>
                    </label>

                    <div class="coach-actions">
                        <button class="primary-btn" type="button" data-coach-submit>Analisar tese</button>
                        <button class="secondary-btn" type="button" data-generate-report>Gerar relatório</button>
                        <button class="secondary-btn" type="button" data-mark-solved>Marcar resolvido</button>
                    </div>

                    <div class="coach-output" data-coach-output>
                        <strong>Feedback jurídico</strong>
                        <p>Escreve a tua tese. A análise vai apontar força, falha principal, contra-argumento e próximo exercício.</p>
                    </div>

                    <div class="case-report" data-case-report hidden></div>
                </article>
            </section>
        </main>
    </div>

    <script>
        window.LEXSTUDY_DATA = <?= jsData($data) ?>;
        window.LEXSTUDY_USER = <?= jsData([
            'authenticated' => (bool)$currentUser,
            'csrfToken' => csrfToken(),
            'state' => $initialState,
        ]) ?>;
    </script>
    <script src="assets/cases.js"></script>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
