<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();
ensureLearningTables();

$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$dbReady = dbSchemaIsReady();
$flash = consumeFlash();
$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$areas = legalResearchAreas();
$objectives = legalResearchObjectives();
$notice = null;
$error = null;
$selectedId = isset($_GET['id']) ? max(1, (int)$_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tool_action'] ?? '') === 'generate_research') {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        $error = 'Sessão expirada. Recarrega a página.';
    } else {
        try {
            $topic = trim((string)($_POST['topic'] ?? ''));
            $area = trim((string)($_POST['area'] ?? 'Geral'));
            $objective = trim((string)($_POST['objective'] ?? 'Aula do zero'));
            $guide = generateLegalResearchGuide($topic, $area, $objective);
            $selectedId = saveLegalResearchGuide($userId, $guide);
            $notice = 'Roteiro de pesquisa criado.';
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$guides = getLegalResearchGuides($userId);
if ($selectedId === 0 && $guides) {
    $selectedId = (int)$guides[0]['id'];
}

$selected = $selectedId > 0 ? getLegalResearchGuide($userId, $selectedId) : null;
$guide = $selected['guide'] ?? null;
$modeLabel = static fn(?string $mode): string => $mode === 'inteligencia' ? 'Inteligência ativa' : 'Modo local';
$assistantPrompt = $guide
    ? ($guide['next_step_prompt'] ?? ('Ajuda-me a estudar este tema: ' . ($guide['topic'] ?? '')))
    : '';
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesquisa Jurídica - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Pesquisa jurídica guiada para transformar temas soltos em estudo organizado.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-research">
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
            <header class="topbar research-hero">
                <div>
                    <span class="eyebrow">Pesquisa Jurídica</span>
                    <h1>Pesquisa como jurista, não como motor de busca.</h1>
                    <p>Escolhe um tema e a plataforma cria uma ficha de investigação: conceitos, fontes, perguntas, jurisprudência a procurar e teses possíveis.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="ferramentas.php">Ferramentas</a>
                    <a class="ghost-btn" href="acordaos.php">Acórdãos</a>
                    <a class="primary-btn" href="#nova">Criar pesquisa</a>
                </div>
            </header>

            <?php if ($flash): ?><div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div><?php endif; ?>
            <?php if ($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
            <?php if (!$dbReady): ?>
                <div class="notice warning">Sem base de dados, as pesquisas ficam guardadas só nesta sessão.</div>
            <?php elseif (!$currentUser): ?>
                <div class="notice warning">Entra no painel para guardar a tua biblioteca de pesquisas permanentemente.</div>
            <?php endif; ?>

            <section class="research-layout">
                <aside class="draft-history research-history">
                    <div class="section-heading">
                        <span class="eyebrow">Arquivo</span>
                        <h2>Pesquisas</h2>
                    </div>
                    <?php if (!$guides): ?>
                        <p>Ainda não criaste roteiros. Começa com um tema concreto, por exemplo “despedimento ilícito”.</p>
                    <?php else: ?>
                        <div class="thread-list">
                            <?php foreach ($guides as $item): ?>
                                <a class="thread-card <?= (int)$item['id'] === $selectedId ? 'is-active' : '' ?>" href="pesquisa.php?id=<?= e($item['id']) ?>">
                                    <span><?= e($item['area']) ?></span>
                                    <strong><?= e($item['topic']) ?></strong>
                                    <small><?= e($item['objective']) ?> · <?= e($modeLabel($item['ai_mode'] ?? null)) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </aside>

                <section id="nova" class="tool-panel research-command">
                    <span class="eyebrow">Novo roteiro</span>
                    <h2>O que queres investigar?</h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="tool_action" value="generate_research">
                        <label>
                            <span>Tema</span>
                            <input type="text" name="topic" placeholder="Ex: despedimento ilícito, furto por necessidade, caução no arrendamento">
                        </label>
                        <div class="draft-form-grid">
                            <label>
                                <span>Área</span>
                                <select name="area">
                                    <?php foreach ($areas as $areaOption): ?>
                                        <option value="<?= e($areaOption) ?>"><?= e($areaOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span>Objetivo</span>
                                <select name="objective">
                                    <?php foreach ($objectives as $objectiveOption): ?>
                                        <option value="<?= e($objectiveOption) ?>"><?= e($objectiveOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        <button class="primary-btn" type="submit">Criar roteiro</button>
                    </form>
                </section>

                <section class="research-output">
                    <?php if (!$guide): ?>
                        <article class="tool-panel quiet-panel research-empty">
                            <span class="eyebrow">Método</span>
                            <h2>A pesquisa aparece aqui.</h2>
                            <p>Esta ferramenta é para quando ainda não sabes por onde começar. Ela dá uma ordem de investigação para não saltares entre livros, IA e acórdãos sem critério.</p>
                            <div class="research-method-strip">
                                <span>1. Conceitos</span>
                                <span>2. Normas</span>
                                <span>3. Jurisprudência</span>
                                <span>4. Tese</span>
                            </div>
                        </article>
                    <?php else: ?>
                        <article class="library-detail research-dossier">
                            <div class="section-heading split">
                                <div>
                                    <span class="eyebrow"><?= e($guide['area']) ?> · <?= e($guide['objective']) ?></span>
                                    <h2><?= e($guide['title']) ?></h2>
                                    <p><?= e($guide['summary']) ?></p>
                                </div>
                                <span class="difficulty medium"><?= e($modeLabel($selected['ai_mode'] ?? ($guide['_mode'] ?? 'local'))) ?></span>
                            </div>

                            <div class="research-question">
                                <span>Pergunta-mãe</span>
                                <strong><?= e($guide['opening_question']) ?></strong>
                            </div>

                            <div class="research-axis">
                                <?php
                                $blocks = [
                                    'concepts' => 'Conceitos-chave',
                                    'legal_sources' => 'Fontes prováveis',
                                    'research_questions' => 'Perguntas de investigação',
                                    'case_law_targets' => 'Jurisprudência a procurar',
                                ];
                                ?>
                                <?php foreach ($blocks as $key => $label): ?>
                                    <article class="coach-output compact">
                                        <strong><?= e($label) ?></strong>
                                        <ul class="result-list">
                                            <?php foreach ((array)($guide[$key] ?? []) as $item): ?>
                                                <li><?= e($item) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </article>
                                <?php endforeach; ?>
                            </div>

                            <div class="research-track-list">
                                <?php foreach ((array)($guide['argument_tracks'] ?? []) as $track): ?>
                                    <section class="research-track">
                                        <span>Tese</span>
                                        <h3><?= e($track['name'] ?? 'Linha de argumento') ?></h3>
                                        <ol>
                                            <?php foreach ((array)($track['steps'] ?? []) as $step): ?>
                                                <li><?= e($step) ?></li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </section>
                                <?php endforeach; ?>
                            </div>

                            <div class="case-tool-grid">
                                <article class="coach-output compact">
                                    <strong>Tarefas de estudo</strong>
                                    <ul class="result-list">
                                        <?php foreach ((array)($guide['study_tasks'] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                    </ul>
                                </article>
                                <article class="coach-output compact">
                                    <strong>Erros a evitar</strong>
                                    <ul class="result-list">
                                        <?php foreach ((array)($guide['common_mistakes'] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                    </ul>
                                </article>
                            </div>

                            <div class="research-searches">
                                <span class="eyebrow">Pesquisas úteis</span>
                                <div>
                                    <?php foreach ((array)($guide['search_queries'] ?? []) as $query): ?>
                                        <code><?= e($query) ?></code>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="research-next">
                                <label>
                                    <span>Prompt para levar ao Assistente</span>
                                    <textarea readonly rows="3"><?= e($assistantPrompt) ?></textarea>
                                </label>
                                <div class="top-actions">
                                    <a class="ghost-btn" href="assistant.php">Abrir assistente</a>
                                    <a class="ghost-btn" href="conceitos.php">Ver conceitos</a>
                                    <a class="ghost-btn" href="acordaos.php">Analisar acórdão</a>
                                </div>
                            </div>

                            <p class="draft-disclaimer"><?= e($guide['disclaimer']) ?></p>
                        </article>
                    <?php endif; ?>
                </section>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
