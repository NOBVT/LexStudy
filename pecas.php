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
$selectedId = isset($_GET['id']) ? max(1, (int)$_GET['id']) : 0;
$pieceTypes = legalDraftTypes();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tool_action'] ?? '') === 'generate_draft') {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        $error = 'Sessão expirada. Recarrega a página.';
    } else {
        try {
            $pieceType = (string)($_POST['piece_type'] ?? 'Parecer jurídico');
            $area = trim((string)($_POST['area'] ?? 'Geral'));
            $goal = trim((string)($_POST['goal'] ?? ''));
            $facts = trim((string)($_POST['facts'] ?? ''));
            $draft = generateLegalDraft($pieceType, $area, $facts, $goal);
            $selectedId = saveLegalDraft($userId, $pieceType, $area, $facts, $goal, $draft);
            $notice = 'Rascunho criado.';
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$drafts = getLegalDrafts($userId);
if ($selectedId === 0 && $drafts) {
    $selectedId = (int)$drafts[0]['id'];
}

$selected = $selectedId > 0 ? getLegalDraft($userId, $selectedId) : null;
$selectedDraft = $selected['draft'] ?? null;
$modeLabel = static function (?string $mode): string {
    return $mode === 'inteligencia' ? 'Inteligência ativa' : 'Modo local';
};
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oficina de Peças - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Oficina de escrita jurídica para estruturar peças, pareceres, requerimentos e recursos.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-drafts">
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
                    <span class="eyebrow">Oficina de Peças</span>
                    <h1>Transforma factos em estrutura jurídica clara.</h1>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="assistant.php">Perguntar ao assistente</a>
                    <a class="primary-btn" href="#nova">Criar peça</a>
                </div>
            </header>

            <?php if ($flash): ?><div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div><?php endif; ?>
            <?php if ($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
            <?php if (!$dbReady): ?>
                <div class="notice warning">Sem base de dados, os rascunhos ficam guardados só nesta sessão.</div>
            <?php elseif (!$currentUser): ?>
                <div class="notice warning">Entra no painel para guardar a tua oficina de peças permanentemente.</div>
            <?php endif; ?>

            <section class="draft-layout">
                <aside class="draft-history">
                    <div class="section-heading">
                        <span class="eyebrow">Arquivo</span>
                        <h2>Rascunhos</h2>
                    </div>
                    <?php if (!$drafts): ?>
                        <p>Ainda não criaste nenhuma peça. Começa pelo formulário.</p>
                    <?php else: ?>
                        <div class="thread-list">
                            <?php foreach ($drafts as $draftItem): ?>
                                <a class="thread-card <?= (int)$draftItem['id'] === $selectedId ? 'is-active' : '' ?>" href="pecas.php?id=<?= e($draftItem['id']) ?>">
                                    <span><?= e($draftItem['piece_type']) ?></span>
                                    <strong><?= e($draftItem['title']) ?></strong>
                                    <small><?= e($draftItem['area']) ?> · <?= e($modeLabel($draftItem['ai_mode'] ?? null)) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </aside>

                <section id="nova" class="tool-panel draft-form">
                    <span class="eyebrow">Novo rascunho</span>
                    <h2>Dados da peça</h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="tool_action" value="generate_draft">
                        <div class="draft-form-grid">
                            <label>
                                <span>Tipo</span>
                                <select name="piece_type">
                                    <?php foreach ($pieceTypes as $type): ?>
                                        <option value="<?= e($type) ?>"><?= e($type) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span>Área</span>
                                <input type="text" name="area" value="Direito Civil" placeholder="Ex: Direito Civil, Penal, Trabalho">
                            </label>
                        </div>
                        <label>
                            <span>Objetivo</span>
                            <input type="text" name="goal" placeholder="Ex: pedir indemnização, contestar despedimento, recorrer da decisão">
                        </label>
                        <label>
                            <span>Factos do caso</span>
                            <textarea name="facts" rows="9" placeholder="Cola aqui os factos. Quanto mais concreto fores, melhor fica a estrutura."></textarea>
                        </label>
                        <button class="primary-btn" type="submit">Gerar estrutura</button>
                    </form>
                </section>

                <section class="draft-output">
                    <?php if (!$selectedDraft): ?>
                        <article class="quiet-panel tool-panel">
                            <span class="eyebrow">Resultado</span>
                            <h2>A peça aparece aqui.</h2>
                            <p>Esta ferramenta não escreve por ti para entregares sem pensar. Ela dá estrutura, pontos fortes, prova e riscos para treinares escrita jurídica.</p>
                        </article>
                    <?php else: ?>
                        <article class="library-detail draft-document">
                            <div class="section-heading split">
                                <div>
                                    <span class="eyebrow"><?= e($selectedDraft['piece_type'] ?? $selected['piece_type']) ?></span>
                                    <h2><?= e($selectedDraft['title'] ?? $selected['title']) ?></h2>
                                    <p><?= e($selectedDraft['summary'] ?? '') ?></p>
                                </div>
                                <span class="difficulty medium"><?= e($modeLabel($selected['ai_mode'] ?? ($selectedDraft['_mode'] ?? 'local'))) ?></span>
                            </div>

                            <?php foreach (($selectedDraft['sections'] ?? []) as $section): ?>
                                <div class="draft-section">
                                    <strong><?= e($section['heading'] ?? 'Secção') ?></strong>
                                    <p><?= nl2br(e($section['content'] ?? '')) ?></p>
                                </div>
                            <?php endforeach; ?>

                            <div class="case-tool-grid">
                                <?php foreach (['arguments' => 'Argumentos', 'evidence' => 'Prova', 'risks' => 'Riscos', 'next_steps' => 'Próximos passos'] as $key => $label): ?>
                                    <article class="coach-output compact">
                                        <strong><?= e($label) ?></strong>
                                        <ul class="result-list">
                                            <?php foreach ((array)($selectedDraft[$key] ?? []) as $item): ?>
                                                <li><?= e($item) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </article>
                                <?php endforeach; ?>
                            </div>

                            <p class="draft-disclaimer"><?= e($selectedDraft['disclaimer'] ?? 'Material de estudo.') ?></p>
                        </article>
                    <?php endif; ?>
                </section>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
