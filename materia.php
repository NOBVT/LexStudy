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
$disciplines = disciplineBlueprints();
$selectedKey = normalizeDisciplineKey($_GET['disciplina'] ?? ($_POST['discipline_key'] ?? 'civil'));
$selectedDiscipline = $disciplines[$selectedKey] ?? $disciplines['base'];
$selectedId = isset($_GET['id']) ? max(1, (int)$_GET['id']) : 0;
$notice = null;
$error = null;
$today = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['tool_action'] ?? '');
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        $error = 'Sessão expirada. Recarrega a página.';
    } elseif ($action === 'import_matter') {
        try {
            $selectedKey = normalizeDisciplineKey($_POST['discipline_key'] ?? $selectedKey);
            $selectedDiscipline = $disciplines[$selectedKey] ?? $selectedDiscipline;
            $disciplineName = $selectedDiscipline['name'];
            $topic = trim((string)($_POST['topic'] ?? ''));
            $classDate = normalizeDailyReviewDate((string)($_POST['class_date'] ?? $today));
            $notes = trim((string)($_POST['notes'] ?? ''));

            if (isset($_FILES['source_file']) && ($_FILES['source_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $file = extractUploadedDocumentText($_FILES['source_file']);
                $fileText = trim((string)$file['text']);
                if ($fileText === '') {
                    throw new RuntimeException('Consegui ler o ficheiro, mas não encontrei texto útil.');
                }
                $notes = trim($notes . "\n\nTexto do ficheiro " . (string)$file['name'] . ":\n" . $fileText);
                if ($topic === '') {
                    $topic = pathinfo((string)$file['name'], PATHINFO_FILENAME) ?: 'Matéria importada';
                }
            }

            $review = generateDailyStudyReview($disciplineName, $topic, $notes, $classDate);
            $selectedId = saveDailyStudyReview($userId, $review, $notes);
            $messages = ['Matéria transformada em ficha de estudo.'];

            if ($currentUser && !empty($_POST['save_source'])) {
                saveLegalSource(
                    $userId,
                    'Aula: ' . ($review['topic'] ?? $topic ?: 'Matéria importada'),
                    $review['discipline'] ?? $disciplineName,
                    matterSourceContent($review, $notes)
                );
                $messages[] = 'Resumo guardado na biblioteca.';
            }

            if ($currentUser && !empty($_POST['auto_flashcards'])) {
                $deck = createDailyReviewFlashcards((int)$currentUser['id'], $selectedId);
                $messages[] = $deck['existing'] ? 'Baralho já existia.' : 'Flashcards criados.';
            }

            setFlash(implode(' ', $messages), 'success');
            header('Location: materia.php?id=' . $selectedId . '&disciplina=' . rawurlencode($selectedKey));
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$reviews = getDailyStudyReviews($userId, 12);
if ($selectedId === 0 && $reviews) {
    $selectedId = (int)$reviews[0]['id'];
}

$selected = $selectedId > 0 ? getDailyStudyReview($userId, $selectedId) : null;
$review = $selected['review'] ?? null;
if (is_array($review) && $review) {
    $review = normalizeDailyStudyReview(
        $review,
        (string)($selected['discipline'] ?? 'Geral'),
        (string)($selected['topic'] ?? 'Matéria importada'),
        (string)($selected['raw_notes'] ?? ''),
        (string)($selected['class_date'] ?? $today)
    );
}
$cadence = $review ? matterReviewCadence($review) : [];
$modeLabel = static fn(?string $mode): string => $mode === 'inteligencia' ? 'Inteligência ativa' : 'Modo local';
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Estúdio de Aula - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Importador de matéria jurídica com resumo, revisão espaçada, mini-casos e flashcards.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css?v=2">
</head>
<body class="page-matter page-daily-review">
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
            <header class="topbar matter-hero">
                <div>
                    <span class="eyebrow">Estúdio de Aula</span>
                    <h1>Transforma matéria real em estudo ativo.</h1>
                    <p>Cola apontamentos, escolhe a disciplina e sai com resumo, perguntas, mini-caso, flashcards e revisão espaçada.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="disciplinas.php?disciplina=<?= e($selectedKey) ?>">Disciplina</a>
                    <a class="ghost-btn" href="revisao.php">Revisão do Dia</a>
                    <a class="primary-btn" href="#importar">Importar matéria</a>
                </div>
            </header>

            <?php if ($flash): ?><div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div><?php endif; ?>
            <?php if ($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
            <?php if (!$dbReady): ?>
                <div class="notice warning">Sem base de dados, a matéria fica guardada só nesta sessão.</div>
            <?php elseif (!$currentUser): ?>
                <div class="notice warning">Entra no painel para guardar biblioteca e criar flashcards pessoais automaticamente.</div>
            <?php endif; ?>

            <section class="daily-rhythm matter-rhythm" aria-label="Fluxo do Estúdio de Aula">
                <article><span>01</span><strong>Capturar</strong><small>Apontamentos, ficheiro PDF/TXT, exemplos e dúvidas da aula.</small></article>
                <article><span>02</span><strong>Converter</strong><small>A IA separa resumo, conceitos, perguntas e armadilhas.</small></article>
                <article><span>03</span><strong>Ativar</strong><small>Flashcards, mini-caso e revisão 24h/3d/7d.</small></article>
            </section>

            <section class="daily-layout matter-layout">
                <aside class="draft-history daily-history matter-history">
                    <div class="section-heading">
                        <span class="eyebrow">Arquivo recente</span>
                        <h2>Matéria importada</h2>
                    </div>
                    <?php if (!$reviews): ?>
                        <p>Ainda não tens matéria importada. O objetivo é alimentar esta página depois de cada aula.</p>
                    <?php else: ?>
                        <div class="thread-list">
                            <?php foreach ($reviews as $item): ?>
                                <a class="thread-card <?= (int)$item['id'] === $selectedId ? 'is-active' : '' ?>" href="materia.php?id=<?= e($item['id']) ?>&disciplina=<?= e(rawurlencode(disciplineKeyFromLabel((string)$item['discipline']))) ?>">
                                    <span><?= e($item['class_date'] ?: 'Sem data') ?></span>
                                    <strong><?= e($item['topic']) ?></strong>
                                    <small><?= e($item['discipline']) ?> · <?= e($modeLabel($item['ai_mode'] ?? null)) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </aside>

                <section id="importar" class="tool-panel daily-command matter-command">
                    <span class="eyebrow">Entrada de matéria</span>
                    <h2>O que trouxeste da aula?</h2>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="tool_action" value="import_matter">
                        <div class="draft-form-grid">
                            <label>
                                <span>Data</span>
                                <input type="date" name="class_date" value="<?= e($today) ?>">
                            </label>
                            <label>
                                <span>Disciplina</span>
                                <select name="discipline_key">
                                    <?php foreach ($disciplines as $key => $discipline): ?>
                                        <option value="<?= e($key) ?>" <?= $key === $selectedKey ? 'selected' : '' ?>><?= e($discipline['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        <label>
                            <span>Tema</span>
                            <input type="text" name="topic" placeholder="Ex: responsabilidade civil, fontes do direito, contrato de trabalho">
                        </label>
                        <label>
                            <span>Apontamentos</span>
                            <textarea name="notes" rows="10" placeholder="Cola aqui os teus apontamentos. Pode estar desorganizado: tópicos, exemplos, dúvidas, frases do professor, páginas do manual."></textarea>
                        </label>
                        <label class="daily-file-drop">
                            <span>PDF/TXT opcional</span>
                            <input type="file" name="source_file" accept=".pdf,.txt,application/pdf,text/plain">
                            <small>Funciona melhor com PDFs que permitam selecionar texto.</small>
                        </label>
                        <div class="matter-switches">
                            <label>
                                <input type="checkbox" name="save_source" value="1" <?= $currentUser ? 'checked' : '' ?>>
                                <span>Guardar resumo na Biblioteca</span>
                            </label>
                            <label>
                                <input type="checkbox" name="auto_flashcards" value="1" <?= $currentUser ? 'checked' : '' ?>>
                                <span>Criar flashcards automaticamente</span>
                            </label>
                        </div>
                        <button class="primary-btn" type="submit">Transformar matéria</button>
                    </form>
                </section>

                <section class="daily-output matter-output">
                    <?php if (!$review): ?>
                        <article class="tool-panel quiet-panel daily-empty matter-empty">
                            <span class="eyebrow">Sistema de entrada</span>
                            <h2>Esta página é para usar quando chegas das aulas.</h2>
                            <p>Não serve para ler mais. Serve para transformar matéria bruta em tarefas concretas de estudo.</p>
                            <div class="daily-method-strip">
                                <span>Resumo</span>
                                <span>Perguntas</span>
                                <span>Mini-caso</span>
                                <span>Flashcards</span>
                            </div>
                        </article>
                    <?php else: ?>
                        <article class="library-detail daily-dossier matter-dossier">
                            <div class="section-heading split">
                                <div>
                                    <span class="eyebrow"><?= e($review['class_date']) ?> · <?= e($review['discipline']) ?></span>
                                    <h2><?= e($review['title']) ?></h2>
                                    <p><?= e($review['summary']) ?></p>
                                </div>
                                <span class="difficulty medium"><?= e($modeLabel($selected['ai_mode'] ?? ($review['_mode'] ?? 'local'))) ?></span>
                            </div>

                            <section class="daily-professor matter-summary">
                                <span>Professor</span>
                                <p><?= e($review['professor_explanation']) ?></p>
                            </section>

                            <div class="daily-question-grid matter-review-stack">
                                <article class="coach-output compact">
                                    <strong>Conceitos</strong>
                                    <ul class="result-list">
                                        <?php foreach ((array)$review['key_concepts'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                    </ul>
                                </article>
                                <article class="coach-output compact">
                                    <strong>Dúvidas prováveis</strong>
                                    <ul class="result-list">
                                        <?php foreach ((array)$review['active_questions'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                    </ul>
                                </article>
                                <article class="coach-output compact">
                                    <strong>Armadilhas</strong>
                                    <ul class="result-list">
                                        <?php foreach ((array)$review['confusion_flags'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                    </ul>
                                </article>
                                <article class="coach-output compact">
                                    <strong>Rever primeiro</strong>
                                    <ul class="result-list">
                                        <?php foreach ((array)$review['must_review'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                    </ul>
                                </article>
                            </div>

                            <section class="daily-mini-case">
                                <span>Mini-caso de treino</span>
                                <h3><?= e($review['mini_case']['scenario'] ?? '') ?></h3>
                                <p><?= e($review['mini_case']['task'] ?? '') ?></p>
                            </section>

                            <section class="matter-cadence">
                                <div class="section-heading">
                                    <span class="eyebrow">Revisão espaçada</span>
                                    <h3>Quando voltar a esta matéria.</h3>
                                </div>
                                <div class="matter-cadence-grid">
                                    <?php foreach ($cadence as $item): ?>
                                        <article>
                                            <span><?= e($item['when']) ?></span>
                                            <strong><?= e($item['title']) ?></strong>
                                            <p><?= e($item['task']) ?></p>
                                            <small><?= e($item['output']) ?></small>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </section>

                            <section class="daily-flashcards matter-flashcards">
                                <div class="section-heading split">
                                    <div>
                                        <span class="eyebrow">Cartas sugeridas</span>
                                        <h3>Memória ativa criada da aula.</h3>
                                    </div>
                                    <a class="secondary-btn" href="flashcards.php">Abrir flashcards</a>
                                </div>
                                <div class="daily-card-preview">
                                    <?php foreach (array_slice((array)$review['flashcards'], 0, 6) as $card): ?>
                                        <article>
                                            <span><?= e($card['difficulty'] ?? 'Médio') ?></span>
                                            <strong><?= e($card['front'] ?? '') ?></strong>
                                            <p><?= e($card['back'] ?? '') ?></p>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </section>

                            <section class="research-next daily-next matter-actions">
                                <label>
                                    <span>Prompt para continuar com IA</span>
                                    <textarea readonly rows="3"><?= e($review['next_session_prompt']) ?></textarea>
                                </label>
                                <div class="top-actions">
                                    <a class="ghost-btn" href="assistant.php">Abrir assistente</a>
                                    <a class="ghost-btn" href="exame.php">Gerar exame</a>
                                    <a class="ghost-btn" href="disciplinas.php?disciplina=<?= e(rawurlencode(disciplineKeyFromLabel((string)$review['discipline']))) ?>">Ver disciplina</a>
                                </div>
                            </section>

                            <p class="draft-disclaimer"><?= e($review['disclaimer']) ?></p>
                        </article>
                    <?php endif; ?>
                </section>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
