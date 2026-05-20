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
$query = trim((string)($_GET['q'] ?? ''));
$selectedId = (int)($_GET['id'] ?? 0);
$notice = null;
$error = null;
$generatedDeck = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tool_action'] ?? '') === 'generate_flashcards') {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        $error = 'Sessão expirada. Recarrega a página.';
    } elseif (!$userId) {
        $error = 'Tens de iniciar sessão para criar flashcards pessoais.';
    } else {
        try {
            $selectedId = max(1, (int)($_POST['judgment_id'] ?? 0));
            $generatedDeck = generateJudgmentFlashcards($userId, $selectedId);
            $notice = $generatedDeck['existing']
                ? 'Este baralho já existia. Podes abri-lo nos Flashcards.'
                : 'Flashcards criados: ' . $generatedDeck['created'] . ' cartas novas.';
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$records = getJudgmentLibrary($userId, $query);
$selected = $selectedId > 0 ? getJudgmentSummaryRecord($userId, $selectedId) : null;

if (!$selected && $records) {
    $selected = getJudgmentSummaryRecord($userId, (int)$records[0]['id']);
}

$areas = array_values(array_unique(array_filter(array_map(
    static fn(array $item): ?string => $item['summary']['area_direito'] ?? null,
    $records
))));
$selectedSummary = $selected['summary'] ?? null;
$sourceExcerpt = $selected ? mb_substr(normalizeWhitespace($selected['extracted_text']), 0, 950, 'UTF-8') : '';
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Biblioteca Jurídica - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Biblioteca pessoal de acórdãos analisados, com pesquisa, factos, normas e questões de exame.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-library">
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
                <small><?= e($initialState['xp']) ?> XP</small>
            </div>
        </aside>

        <main id="top" class="workspace">
            <header class="topbar">
                <div>
                    <span class="eyebrow">Biblioteca Jurídica Pessoal</span>
                    <h1>O teu arquivo de acórdãos, pronto para estudar.</h1>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="acordaos.php">Adicionar acórdão</a>
                    <a class="primary-btn" href="simulator.php">Criar caso</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>">
                    <?= e($flash['message'] ?? '') ?>
                </div>
            <?php endif; ?>

            <?php if ($notice): ?>
                <div class="notice">
                    <?= e($notice) ?>
                    <?php if ($generatedDeck): ?>
                        <a class="notice-link" href="flashcards.php?deck_id=<?= e($generatedDeck['deck_id']) ?>">Abrir baralho</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="notice error"><?= e($error) ?></div>
            <?php endif; ?>

            <?php if (!$dbReady): ?>
                <div class="notice warning">
                    A biblioteca precisa da base de dados ativa. Importa `lexstudy.sql` no phpMyAdmin.
                </div>
            <?php endif; ?>

            <?php if (!$currentUser): ?>
                <section class="auth-strip">
                    <div>
                        <span class="eyebrow">Conta necessária</span>
                        <strong>A biblioteca é pessoal.</strong>
                        <small>Entra ou cria conta no painel para guardar acórdãos analisados.</small>
                    </div>
                    <a class="primary-btn" href="index.php">Entrar no painel</a>
                </section>
            <?php else: ?>
                <section class="auth-strip">
                    <div>
                        <span class="eyebrow">Conta ativa</span>
                        <strong><?= e($currentUser['name']) ?></strong>
                        <small><?= e($currentUser['email']) ?></small>
                    </div>
                    <form method="get" class="library-search">
                        <label>
                            <span>Pesquisar</span>
                            <input type="search" name="q" value="<?= e($query) ?>" placeholder="Ex: responsabilidade, art. 483, STJ">
                        </label>
                        <button class="secondary-btn" type="submit">Filtrar</button>
                    </form>
                </section>

                <section class="stats-grid" aria-label="Indicadores da biblioteca">
                    <article>
                        <span><?= e($query === '' ? 'Acórdãos guardados' : 'Resultados') ?></span>
                        <strong><?= e(count($records)) ?></strong>
                    </article>
                    <article>
                        <span>Áreas encontradas</span>
                        <strong><?= e(count($areas)) ?></strong>
                    </article>
                    <article>
                        <span>Modo mais recente</span>
                        <strong><?= e($records[0]['ai_mode'] ?? 'Sem dados') ?></strong>
                    </article>
                    <article>
                        <span>Biblioteca</span>
                        <strong><?= e($dbReady ? 'Ativa' : 'Offline') ?></strong>
                    </article>
                </section>

                <?php if (!$records): ?>
                    <section class="section-block quiet-panel">
                        <span class="eyebrow">Sem acórdãos</span>
                        <h2><?= e($query === '' ? 'Ainda não existe arquivo.' : 'Nenhum resultado encontrado.') ?></h2>
                        <p>Analisa um PDF ou TXT no Parser de Acórdãos. Se estiveres com sessão iniciada, o resumo fica guardado automaticamente aqui.</p>
                        <a class="primary-btn" href="acordaos.php">Adicionar primeiro acórdão</a>
                    </section>
                <?php else: ?>
                    <section class="library-layout">
                        <aside class="library-list" aria-label="Acórdãos guardados">
                            <?php foreach ($records as $record): ?>
                                <?php $summary = $record['summary']; ?>
                                <a class="library-card <?= $selected && $selected['id'] === $record['id'] ? 'is-active' : '' ?>" href="<?= e('library.php?id=' . $record['id'] . ($query !== '' ? '&q=' . rawurlencode($query) : '')) ?>">
                                    <span><?= e($summary['area_direito'] ?: 'Geral') ?></span>
                                    <strong><?= e($summary['resumo_curto'] ?: $record['filename']) ?></strong>
                                    <small><?= e($record['filename']) ?> · <?= e($record['created_at']) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </aside>

                        <article class="library-detail">
                            <?php if ($selected && $selectedSummary): ?>
                                <div class="section-heading split">
                                    <div>
                                        <span class="eyebrow"><?= e($selectedSummary['area_direito'] ?: 'Acórdão') ?></span>
                                        <h2><?= e($selectedSummary['resumo_curto'] ?: $selected['filename']) ?></h2>
                                    </div>
                                    <div class="top-actions">
                                        <span class="difficulty medium"><?= e($selected['ai_mode']) ?></span>
                                        <form method="post" class="inline-form">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                            <input type="hidden" name="tool_action" value="generate_flashcards">
                                            <input type="hidden" name="judgment_id" value="<?= e($selected['id']) ?>">
                                            <button class="primary-btn" type="submit">Gerar flashcards</button>
                                        </form>
                                    </div>
                                </div>

                                <div class="meta-grid">
                                    <?php foreach (['tribunal', 'processo', 'data', 'relator'] as $key): ?>
                                        <div>
                                            <span><?= e(str_replace('_', ' ', $key)) ?></span>
                                            <strong><?= e($selectedSummary[$key] ?: 'Não identificado') ?></strong>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="case-tool-grid">
                                    <section class="coach-output compact">
                                        <strong>Decisão</strong>
                                        <p><?= e($selectedSummary['decisao'] ?: 'Não identificada.') ?></p>
                                    </section>
                                    <section class="coach-output compact">
                                        <strong>Ratio decidendi</strong>
                                        <p><?= e($selectedSummary['ratio_decidendi'] ?: 'Não identificada.') ?></p>
                                    </section>
                                </div>

                                <div class="case-tool-grid">
                                    <?php foreach ([
                                        'factos_provados' => 'Factos provados',
                                        'questoes_juridicas' => 'Questões jurídicas',
                                        'normas_relevantes' => 'Normas relevantes',
                                        'conceitos_para_estudar' => 'Conceitos para estudar',
                                        'possiveis_perguntas_exame' => 'Perguntas de exame',
                                    ] as $key => $label): ?>
                                        <section class="coach-output">
                                            <strong><?= e($label) ?></strong>
                                            <ul class="result-list">
                                                <?php foreach ((array)$selectedSummary[$key] as $item): ?>
                                                    <li><?= e($item) ?></li>
                                                <?php endforeach; ?>
                                                <?php if (!$selectedSummary[$key]): ?>
                                                    <li>Sem dados extraídos.</li>
                                                <?php endif; ?>
                                            </ul>
                                        </section>
                                    <?php endforeach; ?>
                                </div>

                                <?php if ($sourceExcerpt !== ''): ?>
                                    <details class="json-box">
                                        <summary>Ver excerto do texto original</summary>
                                        <p><?= e($sourceExcerpt) ?>...</p>
                                    </details>
                                <?php endif; ?>

                                <details class="json-box">
                                    <summary>Ver JSON guardado</summary>
                                    <pre><?= e(json_encode($selectedSummary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                                </details>
                            <?php endif; ?>
                        </article>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
