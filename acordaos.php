<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();
ensureLearningTables();

$data = appData();
$currentUser = currentUser();
$initialState = getUserStudyState($currentUser ? (int)$currentUser['id'] : null);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$summary = null;
$error = null;
$extractedLength = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tool_action'] ?? '') === 'summarize_judgment') {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        $error = 'Sessão expirada. Recarrega a página.';
    } else {
        try {
            $doc = extractUploadedDocumentText($_FILES['judgment_file'] ?? []);
            $text = trim((string)$doc['text']);
            $extractedLength = mb_strlen($text, 'UTF-8');

            if ($extractedLength < 250 && $doc['mime'] === 'application/pdf' && GEMINI_API_KEY === '') {
                throw new RuntimeException('Não consegui extrair texto suficiente deste PDF sem IA. Usa um PDF pesquisável, faz upload em TXT, ou configura a chave de IA para ler o PDF diretamente.');
            }

            $inlinePdf = $doc['mime'] === 'application/pdf'
                ? ['mime_type' => 'application/pdf', 'bytes' => $doc['bytes']]
                : null;
            $summary = summarizeJudgment($text, $inlinePdf);
            saveJudgmentSummary($currentUser ? (int)$currentUser['id'] : null, $doc['name'], $text, $summary);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Parser de Acórdãos - <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-judgments">
    <div class="app-shell">
        <aside class="sidebar" aria-label="Dock principal">
            <a class="brand" href="index.php" aria-label="LexStudy">
                <span class="brand-mark"><img src="assets/lexstudy-mark.svg" alt=""></span>
                <span><strong>LexStudy</strong><small>Direito aplicado</small></span>
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
                <div class="meter"><span style="width: <?= e($progress['percent']) ?>%"></span></div>
                <small><?= e($initialState['xp']) ?> XP</small>
            </div>
        </aside>

        <main class="workspace">
            <header class="topbar">
                <div>
                    <span class="eyebrow">Parser e resumidor</span>
                    <h1>Faz upload de um acórdão e recebe o raio-X jurídico.</h1>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="library.php">Biblioteca</a>
                    <a class="primary-btn" href="https://www.dgsi.pt/" target="_blank" rel="noreferrer">Abrir DGSI</a>
                </div>
            </header>

            <?php if ($error): ?>
                <div class="notice error"><?= e($error) ?></div>
            <?php endif; ?>

            <section class="section-block two-column">
                <form class="tool-panel" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="tool_action" value="summarize_judgment">
                    <span class="eyebrow">Upload</span>
                    <h2>PDF ou TXT do acórdão</h2>
                    <p>Com a inteligência artificial ativa, o sistema pode ler PDF diretamente. Sem chave, tenta extrair texto localmente.</p>
                    <label class="file-drop">
                        <span>Selecionar ficheiro</span>
                        <input type="file" name="judgment_file" accept="application/pdf,text/plain,.pdf,.txt" required>
                    </label>
                    <button class="primary-btn" type="submit">Analisar acórdão</button>
                </form>

                <article class="tool-panel">
                    <span class="eyebrow">Saída esperada</span>
                    <h2>JSON jurídico estruturado</h2>
                    <p>Tribunal, processo, factos provados, questão jurídica, normas, decisão, ratio decidendi e perguntas de exame.</p>
                    <div class="refs">
                        <span><?= e(GEMINI_API_KEY === '' ? 'Modo local' : 'Inteligência ativa') ?></span>
                        <span><?= e($extractedLength ? $extractedLength . ' caracteres extraídos' : 'Aguardando ficheiro') ?></span>
                    </div>
                </article>
            </section>

            <?php if ($summary): ?>
                <section class="section-block">
                    <div class="section-heading split">
                        <div>
                            <span class="eyebrow">Resultado</span>
                            <h2><?= e($summary['resumo_curto'] ?: 'Resumo do acórdão') ?></h2>
                            <?php if ($currentUser): ?>
                                <p>Este acórdão foi guardado automaticamente na tua biblioteca.</p>
                            <?php endif; ?>
                        </div>
                        <a class="secondary-btn" href="library.php">Abrir biblioteca</a>
                    </div>
                    <div class="analysis-grid">
                        <?php foreach (['tribunal', 'processo', 'data', 'relator', 'area_direito', 'decisao', 'ratio_decidendi'] as $key): ?>
                            <article class="area-card">
                                <span><?= e(str_replace('_', ' ', $key)) ?></span>
                                <p><?= e(is_array($summary[$key]) ? implode(', ', $summary[$key]) : ($summary[$key] ?? 'Não identificado')) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="case-tool-grid">
                        <?php foreach (['factos_provados', 'questoes_juridicas', 'normas_relevantes', 'fundamentacao_essencial', 'conceitos_para_estudar', 'possiveis_perguntas_exame'] as $key): ?>
                            <article class="coach-output">
                                <strong><?= e(str_replace('_', ' ', $key)) ?></strong>
                                <ul class="result-list">
                                    <?php foreach ((array)$summary[$key] as $item): ?>
                                        <li><?= e($item) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <details class="json-box">
                        <summary>Ver JSON completo</summary>
                        <pre><?= e(json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                    </details>
                </section>
            <?php endif; ?>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
