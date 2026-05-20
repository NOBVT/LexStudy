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
$notice = null;
$error = null;
$generatedDeck = null;
$selectedId = isset($_GET['id']) ? max(1, (int)$_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tool_action'] ?? '') === 'lookup_concept') {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        $error = 'Sessão expirada. Recarrega a página.';
    } else {
        try {
            $concept = generateLegalConcept((string)($_POST['term'] ?? ''), (string)($_POST['area'] ?? ''));
            $selectedId = saveLegalConcept($userId, $concept);
            $notice = 'Conceito analisado.';
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tool_action'] ?? '') === 'concept_flashcards') {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        $error = 'Sessão expirada. Recarrega a página.';
    } elseif (!$userId) {
        $error = 'Tens de iniciar sessão para criar flashcards pessoais.';
    } else {
        try {
            $selectedId = max(1, (int)($_POST['concept_id'] ?? 0));
            $generatedDeck = generateConceptFlashcards($userId, $selectedId);
            $notice = $generatedDeck['existing']
                ? 'Este baralho já existia. Podes abri-lo nos Flashcards.'
                : 'Flashcards criados: ' . $generatedDeck['created'] . ' cartas novas.';
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$concepts = getLegalConcepts($userId);
if ($selectedId === 0 && $concepts) {
    $selectedId = (int)$concepts[0]['id'];
}
$selected = $selectedId > 0 ? getLegalConcept($userId, $selectedId) : null;
$concept = $selected['concept'] ?? null;
$modeLabel = static fn(?string $mode): string => $mode === 'inteligencia' ? 'Inteligência ativa' : 'Modo local';
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dicionário Jurídico - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Dicionário jurídico inteligente com definição, requisitos, exemplos e erros comuns.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-concepts">
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
            <header class="topbar">
                <div>
                    <span class="eyebrow">Dicionário Jurídico</span>
                    <h1>Domina conceitos antes de resolver casos.</h1>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="flashcards.php">Rever flashcards</a>
                    <a class="primary-btn" href="#pesquisar">Pesquisar conceito</a>
                </div>
            </header>

            <?php if ($flash): ?><div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div><?php endif; ?>
            <?php if ($notice): ?>
                <div class="notice">
                    <?= e($notice) ?>
                    <?php if ($generatedDeck): ?>
                        <a class="notice-link" href="flashcards.php?deck_id=<?= e($generatedDeck['deck_id']) ?>">Abrir baralho</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
            <?php if (!$dbReady): ?><div class="notice warning">Sem base de dados, os conceitos ficam só nesta sessão.</div><?php endif; ?>

            <section class="concept-layout">
                <aside class="draft-history">
                    <div class="section-heading">
                        <span class="eyebrow">Arquivo</span>
                        <h2>Conceitos</h2>
                    </div>
                    <?php if (!$concepts): ?>
                        <p>Ainda não pesquisaste conceitos.</p>
                    <?php else: ?>
                        <div class="thread-list">
                            <?php foreach ($concepts as $item): ?>
                                <a class="thread-card <?= (int)$item['id'] === $selectedId ? 'is-active' : '' ?>" href="conceitos.php?id=<?= e($item['id']) ?>">
                                    <span><?= e($item['area']) ?></span>
                                    <strong><?= e($item['term']) ?></strong>
                                    <small><?= e($modeLabel($item['ai_mode'] ?? null)) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </aside>

                <section id="pesquisar" class="tool-panel concept-search">
                    <span class="eyebrow">Pesquisa rápida</span>
                    <h2>Que conceito queres dominar?</h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="tool_action" value="lookup_concept">
                        <label>
                            <span>Conceito</span>
                            <input type="text" name="term" placeholder="Ex: responsabilidade civil, dolo eventual, estado de necessidade">
                        </label>
                        <label>
                            <span>Área opcional</span>
                            <input type="text" name="area" placeholder="Ex: Direito Penal">
                        </label>
                        <button class="primary-btn" type="submit">Explicar conceito</button>
                    </form>
                </section>

                <section class="concept-output">
                    <?php if (!$concept): ?>
                        <article class="tool-panel quiet-panel">
                            <span class="eyebrow">Resultado</span>
                            <h2>O conceito aparece aqui.</h2>
                            <p>Usa esta ferramenta para perceber termos antes de entrares em acórdãos, casos ou peças.</p>
                        </article>
                    <?php else: ?>
                        <article class="library-detail concept-card-detail">
                            <div class="section-heading split">
                                <div>
                                    <span class="eyebrow"><?= e($concept['area']) ?></span>
                                    <h2><?= e($concept['term']) ?></h2>
                                    <p><?= e($concept['short_definition']) ?></p>
                                </div>
                                <div class="top-actions concept-actions">
                                    <span class="difficulty medium"><?= e($modeLabel($selected['ai_mode'] ?? ($concept['_mode'] ?? null))) ?></span>
                                    <?php if ($currentUser): ?>
                                        <form method="post" class="inline-form">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                            <input type="hidden" name="tool_action" value="concept_flashcards">
                                            <input type="hidden" name="concept_id" value="<?= e($selectedId) ?>">
                                            <button class="primary-btn" type="submit">Criar flashcards</button>
                                        </form>
                                    <?php else: ?>
                                        <a class="primary-btn" href="index.php">Entrar para criar flashcards</a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="draft-section">
                                <strong>Explicação simples</strong>
                                <p><?= nl2br(e($concept['plain_explanation'])) ?></p>
                            </div>

                            <div class="case-tool-grid">
                                <?php foreach (['requirements' => 'Requisitos', 'examples' => 'Exemplos', 'common_mistakes' => 'Erros comuns', 'related_terms' => 'Termos ligados'] as $key => $label): ?>
                                    <article class="coach-output compact">
                                        <strong><?= e($label) ?></strong>
                                        <ul class="result-list">
                                            <?php foreach ((array)$concept[$key] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                        </ul>
                                    </article>
                                <?php endforeach; ?>
                            </div>

                            <div class="meta-grid concept-memory">
                                <div>
                                    <span>Mnemónica</span>
                                    <strong><?= e($concept['memory_hook']) ?></strong>
                                </div>
                                <div>
                                    <span>Pergunta de revisão</span>
                                    <strong><?= e($concept['review_question']) ?></strong>
                                </div>
                            </div>
                        </article>
                    <?php endif; ?>
                </section>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
