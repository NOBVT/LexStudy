<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();
handleStudyProfileRequest();

$data = appData();
$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$dbReady = dbSchemaIsReady();
$flash = consumeFlash();
$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$diagnosticQuestions = studyDiagnosticQuestions();
$studyProfile = getStudyProfile($userId);
$smartPlan = recommendedDailyPlan($data, $studyProfile);
$domain = getStudyDomainMap($userId, $initialState, $studyProfile);
$weakestCompetency = $domain['weakest'];
$primaryTarget = $studyProfile ? 'sala.php' : '#diagnostico';
$primaryLabel = $studyProfile ? 'Entrar na Sala de Estudo' : 'Fazer diagnóstico';
$primaryDetail = $studyProfile
    ? 'Aulas guiadas, recursos e professor IA. É a base certa antes de treinar exames.'
    : 'Sem diagnóstico, o painel ainda não sabe se deve puxar por conceitos, casos ou memória.';
$methodsByTarget = [];
foreach ($data['methods'] as $method) {
    $methodsByTarget[$method['target']] = $method;
}
$toolGroups = [
    'Orientação' => ['comecar.php', 'plano.php', 'disciplinas.php', 'materia.php', 'sala.php', 'revisao.php', 'trilho.php', 'dominio.php', 'relatorio.php', 'foco.php'],
    'IA e correção' => ['assistant.php', 'exame.php', 'acordaos.php', 'simulator.php'],
    'Produção jurídica' => ['mentor.php', 'pesquisa.php', 'teses.php', 'pecas.php', 'conceitos.php'],
    'Treino e revisão' => ['cases.php', 'flashcards.php', 'caderno.php', 'library.php'],
];
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_NAME) ?> - Plataforma de Estudo Jurídico</title>
    <meta name="description" content="Plataforma profissional de estudo jurídico com casos práticos, flashcards, quiz e treino de argumentação.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-home">
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
                    <span class="eyebrow">Centro de treino jurídico</span>
                    <h1>Estuda Direito com menos ruído.</h1>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="#diagnostico">Diagnóstico</a>
                    <a class="ghost-btn" href="ferramentas.php">Ferramentas</a>
                    <a class="ghost-btn" href="relatorio.php">Relatório</a>
                    <a class="primary-btn" href="foco.php">Começar sessão</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>">
                    <?= e($flash['message'] ?? '') ?>
                </div>
            <?php endif; ?>

            <?php if (!$dbReady): ?>
                <div class="notice warning">
                    A aplicação está em modo demonstração porque a base de dados `lexstudy` ainda não está disponível. Importa o ficheiro `lexstudy.sql` no phpMyAdmin para ativar contas e progresso persistente.
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
                    <form method="post" class="auth-form">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="auth_action" value="login">
                        <label>
                            <span>Email</span>
                            <input type="email" name="email" autocomplete="email" required>
                        </label>
                        <label>
                            <span>Password</span>
                            <input type="password" name="password" autocomplete="current-password" required>
                        </label>
                        <button class="primary-btn" type="submit">Entrar</button>
                    </form>
                    <form method="post" class="auth-form">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="auth_action" value="register">
                        <label>
                            <span>Nome</span>
                            <input type="text" name="name" autocomplete="name" required>
                        </label>
                        <label>
                            <span>Email</span>
                            <input type="email" name="email" autocomplete="email" required>
                        </label>
                        <label>
                            <span>Password</span>
                            <input type="password" name="password" autocomplete="new-password" minlength="6" required>
                        </label>
                        <button class="secondary-btn" type="submit">Criar conta</button>
                    </form>
                <?php endif; ?>
            </section>

            <section class="command-center" aria-label="Mesa de comando de estudo">
                <article class="priority-card">
                    <span class="eyebrow">Próxima ação</span>
                    <h2><?= e($primaryLabel) ?></h2>
                    <p><?= e($primaryDetail) ?></p>
                    <a class="primary-btn" href="<?= e($primaryTarget) ?>">Abrir agora</a>
                </article>

                <article class="rhythm-card">
                    <div class="section-heading split">
                        <div>
                            <span class="eyebrow">Sessão curta</span>
                            <h2>25 minutos bem usados.</h2>
                        </div>
                        <small><?= e($studyProfile['session_time'] ?? 'Plano base') ?></small>
                    </div>
                    <ol>
                        <?php foreach (array_slice($smartPlan, 0, 3) as $item): ?>
                            <li>
                                <a href="<?= e($item['target']) ?>">
                                    <strong><?= e($item['title']) ?></strong>
                                    <span><?= e($item['label']) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </article>

                <article class="signal-card">
                    <span class="eyebrow">Sinal do sistema</span>
                    <h2><?= e($weakestCompetency['name'] ?? 'Orientação') ?></h2>
                    <p><?= e($weakestCompetency['next'] ?? 'Começa pelo diagnóstico e segue uma rotina curta.') ?></p>
                    <a class="notice-link" href="<?= e($weakestCompetency['target'] ?? 'dominio.php') ?>">Treinar ponto fraco</a>
                </article>
            </section>

            <section id="diagnostico" class="diagnostic-layout" aria-label="Diagnóstico inicial e plano personalizado">
                <article class="diagnostic-panel">
                    <div class="section-heading split">
                        <div>
                            <span class="eyebrow">Diagnóstico inicial</span>
                            <h2><?= $studyProfile ? e($studyProfile['title']) : 'Diz ao sistema como estudas.' ?></h2>
                            <p><?= $studyProfile ? e($studyProfile['summary']) : 'Responde rápido. O programa ajusta o plano do dia e o tom do assistente ao teu nível.' ?></p>
                        </div>
                        <?php if ($studyProfile): ?>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                <input type="hidden" name="study_action" value="reset_diagnostic">
                                <button class="secondary-btn" type="submit">Refazer</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if ($studyProfile): ?>
                        <div class="profile-orbit">
                            <span><?= e($studyProfile['focus']) ?></span>
                            <span><?= e($studyProfile['tone']) ?></span>
                            <span><?= e($studyProfile['session_time']) ?></span>
                        </div>
                        <div class="mentor-card">
                            <span class="eyebrow">Pergunta pronta para o assistente</span>
                            <strong><?= e($studyProfile['mentor_question']) ?></strong>
                            <a class="notice-link" href="assistant.php">Abrir assistente</a>
                        </div>
                    <?php else: ?>
                        <form method="post" class="diagnostic-form">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="study_action" value="save_diagnostic">
                            <?php foreach ($diagnosticQuestions as $question): ?>
                                <fieldset>
                                    <legend><?= e($question['label']) ?></legend>
                                    <div class="choice-grid">
                                        <?php foreach ($question['options'] as $value => $label): ?>
                                            <label>
                                                <input type="radio" name="<?= e($question['key']) ?>" value="<?= e($value) ?>" <?= array_key_first($question['options']) === $value ? 'checked' : '' ?>>
                                                <span><?= e($label) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </fieldset>
                            <?php endforeach; ?>
                            <button class="primary-btn" type="submit">Criar plano inteligente</button>
                        </form>
                    <?php endif; ?>
                </article>

                <article id="plano-inteligente" class="smart-plan-panel">
                    <div class="section-heading">
                        <span class="eyebrow">Plano inteligente</span>
                        <h2><?= $studyProfile ? 'O teu plano de hoje.' : 'Plano base para começar.' ?></h2>
                        <p><?= $studyProfile ? 'Segue por ordem. Pouco, mas bem feito.' : 'Sem diagnóstico, uso uma rotina segura para iniciantes.' ?></p>
                    </div>
                    <div class="smart-plan-list">
                        <?php foreach ($smartPlan as $index => $item): ?>
                            <a href="<?= e($item['target']) ?>">
                                <span><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?> · <?= e($item['label']) ?></span>
                                <strong><?= e($item['title']) ?></strong>
                                <small><?= e($item['detail']) ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </article>
            </section>

            <section class="domain-strip" aria-label="Mapa de domínio">
                <article>
                    <span class="eyebrow">Maturidade jurídica</span>
                    <strong><?= e($domain['average']) ?>% · <?= e($domain['level']) ?></strong>
                    <div class="meter"><span style="width: <?= e($domain['average']) ?>%"></span></div>
                </article>
                <article>
                    <span class="eyebrow">Ponto fraco atual</span>
                    <strong><?= e($weakestCompetency['name'] ?? 'Orientação') ?></strong>
                    <small><?= e($weakestCompetency['next'] ?? 'Começa pelo diagnóstico.') ?></small>
                </article>
                <a class="domain-strip-action" href="dominio.php">
                    <span>Ver sistema completo</span>
                    <strong>Mapa de Domínio</strong>
                </a>
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
                    <span>Cartas dominadas</span>
                    <strong data-mastered-cards><?= e($initialState['masteredCards']) ?></strong>
                </article>
            </section>

            <section id="study" class="section-block">
                <div class="section-heading">
                    <span class="eyebrow">Ferramentas por objetivo</span>
                    <h2>Escolhe pelo problema que queres resolver.</h2>
                </div>
                <div class="tool-cluster-grid">
                    <?php foreach ($toolGroups as $groupName => $targets): ?>
                        <article class="tool-cluster">
                            <span class="eyebrow"><?= e($groupName) ?></span>
                            <div class="compact-method-list">
                                <?php foreach ($targets as $target): ?>
                                    <?php if (!isset($methodsByTarget[$target])) {
                                        continue;
                                    } ?>
                                    <?php $method = $methodsByTarget[$target]; ?>
                                    <a class="tool-link" href="<?= e($method['target']) ?>">
                                        <span><?= e($method['meta']) ?></span>
                                        <strong><?= e($method['name']) ?></strong>
                                        <small><?= e($method['description']) ?></small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
