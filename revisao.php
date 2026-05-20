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
$notice = null;
$error = null;
$selectedId = isset($_GET['id']) ? max(1, (int)$_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['tool_action'] ?? '');
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        $error = 'Sessão expirada. Recarrega a página.';
    } elseif ($action === 'generate_daily_review') {
        try {
            $discipline = trim((string)($_POST['discipline'] ?? 'Geral'));
            $topic = trim((string)($_POST['topic'] ?? ''));
            $classDate = trim((string)($_POST['class_date'] ?? date('Y-m-d')));
            $notes = trim((string)($_POST['notes'] ?? ''));

            if (isset($_FILES['source_file']) && ($_FILES['source_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $file = extractUploadedDocumentText($_FILES['source_file']);
                $fileText = trim((string)$file['text']);
                if ($fileText === '') {
                    throw new RuntimeException('Consegui ler o ficheiro, mas não encontrei texto útil.');
                }
                $notes = trim($notes . "\n\nTexto do ficheiro " . (string)$file['name'] . ":\n" . $fileText);
                if ($topic === '') {
                    $topic = pathinfo((string)$file['name'], PATHINFO_FILENAME) ?: 'Matéria do dia';
                }
            }

            $review = generateDailyStudyReview($discipline, $topic, $notes, $classDate);
            $selectedId = saveDailyStudyReview($userId, $review, $notes);
            $notice = 'Revisão criada.';
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    } elseif ($action === 'daily_review_flashcards') {
        try {
            if (!$currentUser) {
                throw new RuntimeException('Entra na tua conta para criar baralhos pessoais.');
            }
            $selectedId = max(1, (int)($_POST['review_id'] ?? 0));
            $created = createDailyReviewFlashcards((int)$currentUser['id'], $selectedId);
            $notice = $created['existing']
                ? 'Este baralho já existia. Abri o caminho para os flashcards.'
                : 'Flashcards criados a partir da revisão.';
            $notice .= ' Podes rever em flashcards.php?deck_id=' . (int)$created['deck_id'];
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    } elseif ($action === 'daily_review_answer') {
        try {
            $selectedId = max(1, (int)($_POST['review_id'] ?? 0));
            $answer = trim((string)($_POST['answer'] ?? ''));
            $record = getDailyStudyReview($userId, $selectedId);
            if (!$record || empty($record['review'])) {
                throw new RuntimeException('Revisão não encontrada.');
            }
            $reviewForEvaluation = normalizeDailyStudyReview(
                (array)$record['review'],
                (string)($record['discipline'] ?? 'Geral'),
                (string)($record['topic'] ?? 'Matéria do dia'),
                (string)($record['raw_notes'] ?? ''),
                (string)($record['class_date'] ?? date('Y-m-d'))
            );
            $evaluation = evaluateDailyReviewAnswer($reviewForEvaluation, $answer);
            $attemptId = saveDailyReviewAttempt($userId, $selectedId, $answer, $evaluation);
            if ($userId && (int)$evaluation['score'] < 14) {
                logStudyMistake($userId, [
                    'source_type' => 'daily_review',
                    'source_id' => $attemptId,
                    'area' => $reviewForEvaluation['discipline'] ?? 'Geral',
                    'title' => 'Revisão do Dia: ' . ($reviewForEvaluation['topic'] ?? 'Matéria'),
                    'prompt' => $reviewForEvaluation['mini_case']['task'] ?? '',
                    'correction' => $evaluation['better_answer'] ?? 'Rever estrutura da resposta.',
                    'next_step' => $evaluation['next_drill'] ?? 'Reescrever a resposta com estrutura.',
                    'weight' => 3,
                ]);
            }
            $notice = 'Resposta corrigida: ' . (int)$evaluation['score'] . '/20.';
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$reviews = getDailyStudyReviews($userId);
if ($selectedId === 0 && $reviews) {
    $selectedId = (int)$reviews[0]['id'];
}

$selected = $selectedId > 0 ? getDailyStudyReview($userId, $selectedId) : null;
$review = $selected['review'] ?? null;
if (is_array($review) && $review) {
    $review = normalizeDailyStudyReview(
        $review,
        (string)($selected['discipline'] ?? 'Geral'),
        (string)($selected['topic'] ?? 'Matéria do dia'),
        (string)($selected['raw_notes'] ?? ''),
        (string)($selected['class_date'] ?? date('Y-m-d'))
    );
}

$attempts = $selectedId > 0 ? getDailyReviewAttempts($userId, $selectedId) : [];
$latestAttempt = $attempts[0] ?? null;
$modeLabel = static fn(?string $mode): string => $mode === 'inteligencia' ? 'Inteligência ativa' : 'Modo local';
$today = date('Y-m-d');
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Revisão do Dia - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Revisão diária de aulas jurídicas com resumo, perguntas, mini-caso e flashcards.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-daily-review">
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
            <header class="topbar daily-hero">
                <div>
                    <span class="eyebrow">Revisão do Dia</span>
                    <h1>A tua aula entra aqui. O estudo sai organizado.</h1>
                    <p>Depois das aulas, cola apontamentos ou envia PDF/TXT. A plataforma devolve resumo, explicação de professor, perguntas ativas, mini-caso e cartas.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="sala.php">Sala</a>
                    <a class="ghost-btn" href="assistant.php">Professor IA</a>
                    <a class="primary-btn" href="#nova">Rever aula</a>
                </div>
            </header>

            <?php if ($flash): ?><div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div><?php endif; ?>
            <?php if ($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
            <?php if (!$dbReady): ?>
                <div class="notice warning">Sem base de dados, as revisões ficam guardadas só nesta sessão.</div>
            <?php elseif (!$currentUser): ?>
                <div class="notice warning">Entra no painel para guardar o teu diário de estudo e criar flashcards pessoais.</div>
            <?php endif; ?>

            <section class="daily-rhythm" aria-label="Ritual diário">
                <article>
                    <span>01</span>
                    <strong>Descarregar</strong>
                    <small>Tira a matéria da cabeça e põe tudo no sistema.</small>
                </article>
                <article>
                    <span>02</span>
                    <strong>Compreender</strong>
                    <small>Resumo e explicação simples, sem perder rigor.</small>
                </article>
                <article>
                    <span>03</span>
                    <strong>Treinar</strong>
                    <small>Perguntas, mini-caso e flashcards para memória ativa.</small>
                </article>
            </section>

            <section class="daily-layout">
                <aside class="draft-history daily-history">
                    <div class="section-heading">
                        <span class="eyebrow">Diário</span>
                        <h2>Revisões</h2>
                    </div>
                    <?php if (!$reviews): ?>
                        <p>Ainda não tens revisões. O objetivo é criar uma por dia de aulas, mesmo que seja curta.</p>
                    <?php else: ?>
                        <div class="thread-list">
                            <?php foreach ($reviews as $item): ?>
                                <a class="thread-card <?= (int)$item['id'] === $selectedId ? 'is-active' : '' ?>" href="revisao.php?id=<?= e($item['id']) ?>">
                                    <span><?= e($item['class_date'] ?: 'Sem data') ?></span>
                                    <strong><?= e($item['topic']) ?></strong>
                                    <small><?= e($item['discipline']) ?> · <?= e($modeLabel($item['ai_mode'] ?? null)) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </aside>

                <section id="nova" class="tool-panel daily-command">
                    <span class="eyebrow">Nova revisão</span>
                    <h2>O que deste hoje?</h2>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="tool_action" value="generate_daily_review">
                        <div class="draft-form-grid">
                            <label>
                                <span>Data</span>
                                <input type="date" name="class_date" value="<?= e($today) ?>">
                            </label>
                            <label>
                                <span>Disciplina</span>
                                <select name="discipline">
                                    <?php foreach ($areas as $areaOption): ?>
                                        <option value="<?= e($areaOption) ?>"><?= e($areaOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        <label>
                            <span>Tema</span>
                            <input type="text" name="topic" placeholder="Ex: fontes do direito, teoria da norma, responsabilidade civil">
                        </label>
                        <label>
                            <span>Apontamentos</span>
                            <textarea name="notes" rows="9" placeholder="Cola aqui o que escreveste na aula, tópicos soltos, dúvidas, exemplos do professor ou texto do manual."></textarea>
                        </label>
                        <label class="daily-file-drop">
                            <span>PDF/TXT opcional</span>
                            <input type="file" name="source_file" accept=".pdf,.txt,application/pdf,text/plain">
                            <small>Funciona melhor com PDFs que tenham texto selecionável.</small>
                        </label>
                        <button class="primary-btn" type="submit">Criar revisão</button>
                    </form>
                </section>

                <section class="daily-output">
                    <?php if (!$review): ?>
                        <article class="tool-panel quiet-panel daily-empty">
                            <span class="eyebrow">Sistema pessoal</span>
                            <h2>Esta página deve ser o teu ritual diário.</h2>
                            <p>A ideia é simples: depois de cada dia no curso, descarregas a matéria aqui. O sistema organiza, testa-te e transforma o que interessa em memória ativa.</p>
                            <div class="daily-method-strip">
                                <span>Apontamentos</span>
                                <span>Resumo</span>
                                <span>Mini-caso</span>
                                <span>Cartas</span>
                            </div>
                        </article>
                    <?php else: ?>
                        <article class="library-detail daily-dossier">
                            <div class="section-heading split">
                                <div>
                                    <span class="eyebrow"><?= e($review['class_date']) ?> · <?= e($review['discipline']) ?></span>
                                    <h2><?= e($review['title']) ?></h2>
                                    <p><?= e($review['summary']) ?></p>
                                </div>
                                <span class="difficulty medium"><?= e($modeLabel($selected['ai_mode'] ?? ($review['_mode'] ?? 'local'))) ?></span>
                            </div>

                            <section class="daily-professor">
                                <span>Professor</span>
                                <p><?= e($review['professor_explanation']) ?></p>
                            </section>

                            <section class="daily-client-brief">
                                <span>Modo cliente</span>
                                <h3>Conseguirias explicar isto a alguém sem formação jurídica?</h3>
                                <div>
                                    <article>
                                        <strong>Explicação simples</strong>
                                        <p><?= e($review['client_brief']['plain_explanation'] ?? '') ?></p>
                                    </article>
                                    <article>
                                        <strong>Cuidado profissional</strong>
                                        <p><?= e($review['client_brief']['client_warning'] ?? '') ?></p>
                                    </article>
                                    <article>
                                        <strong>Exemplo útil</strong>
                                        <p><?= e($review['client_brief']['useful_example'] ?? '') ?></p>
                                    </article>
                                </div>
                            </section>

                            <div class="daily-question-grid">
                                <article class="coach-output compact">
                                    <strong>Conceitos-chave</strong>
                                    <ul class="result-list">
                                        <?php foreach ((array)$review['key_concepts'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                    </ul>
                                </article>
                                <article class="coach-output compact">
                                    <strong>O que rever</strong>
                                    <ul class="result-list">
                                        <?php foreach ((array)$review['must_review'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                    </ul>
                                </article>
                                <article class="coach-output compact">
                                    <strong>Armadilhas</strong>
                                    <ul class="result-list">
                                        <?php foreach ((array)$review['confusion_flags'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                    </ul>
                                </article>
                                <article class="coach-output compact">
                                    <strong>Perguntas ativas</strong>
                                    <ul class="result-list">
                                        <?php foreach ((array)$review['active_questions'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                    </ul>
                                </article>
                            </div>

                            <section class="daily-mini-case">
                                <span>Mini-caso</span>
                                <h3><?= e($review['mini_case']['scenario'] ?? '') ?></h3>
                                <p><?= e($review['mini_case']['task'] ?? '') ?></p>
                            </section>

                            <section class="daily-practice">
                                <div class="section-heading split">
                                    <div>
                                        <span class="eyebrow">Correção ativa</span>
                                        <h3>Responde ao mini-caso</h3>
                                    </div>
                                    <?php if ($latestAttempt): ?>
                                        <span class="daily-score"><?= e((int)($latestAttempt['score'] ?? 0)) ?>/20</span>
                                    <?php endif; ?>
                                </div>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="tool_action" value="daily_review_answer">
                                    <input type="hidden" name="review_id" value="<?= e($selectedId) ?>">
                                    <label>
                                        <span>A tua resposta</span>
                                        <textarea name="answer" rows="7" placeholder="Escreve como num teste: factos relevantes, problema jurídico, regra, aplicação e conclusão."><?= e($latestAttempt['answer'] ?? '') ?></textarea>
                                    </label>
                                    <button class="primary-btn" type="submit">Corrigir resposta</button>
                                </form>
                                <?php if ($latestAttempt && !empty($latestAttempt['evaluation'])): ?>
                                    <?php $evaluation = (array)$latestAttempt['evaluation']; ?>
                                    <div class="daily-feedback">
                                        <article>
                                            <span><?= e($evaluation['grade_label'] ?? 'Feedback') ?></span>
                                            <strong><?= e((int)($evaluation['score'] ?? 0)) ?>/20</strong>
                                            <p><?= e($evaluation['better_answer'] ?? '') ?></p>
                                        </article>
                                        <article>
                                            <strong>Pontos fortes</strong>
                                            <ul class="result-list">
                                                <?php foreach ((array)($evaluation['strengths'] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                            </ul>
                                        </article>
                                        <article>
                                            <strong>Falhas a corrigir</strong>
                                            <ul class="result-list">
                                                <?php foreach ((array)($evaluation['gaps'] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                            </ul>
                                        </article>
                                        <article>
                                            <strong>Próximo treino</strong>
                                            <p><?= e($evaluation['next_drill'] ?? '') ?></p>
                                            <small><?= e($evaluation['client_style_tip'] ?? '') ?></small>
                                        </article>
                                    </div>
                                <?php endif; ?>
                            </section>

                            <section class="daily-quiz">
                                <div class="section-heading">
                                    <span class="eyebrow">Quiz rápido</span>
                                    <h3>Responde antes de abrir a solução.</h3>
                                </div>
                                <div class="daily-quiz-list">
                                    <?php foreach ((array)$review['quick_quiz'] as $item): ?>
                                        <details>
                                            <summary><?= e($item['question'] ?? '') ?></summary>
                                            <small><?= e($item['hint'] ?? '') ?></small>
                                            <p><?= e($item['answer'] ?? '') ?></p>
                                        </details>
                                    <?php endforeach; ?>
                                </div>
                            </section>

                            <div class="daily-review-plan">
                                <section>
                                    <strong>Plano de 30 minutos</strong>
                                    <ol class="result-list numbered">
                                        <?php foreach ((array)$review['review_plan'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                    </ol>
                                </section>
                                <section>
                                    <strong>Teste de confiança</strong>
                                    <p><?= e($review['confidence_check']) ?></p>
                                </section>
                            </div>

                            <section class="daily-flashcards">
                                <div class="section-heading split">
                                    <div>
                                        <span class="eyebrow">Memória ativa</span>
                                        <h3>Cartas sugeridas</h3>
                                    </div>
                                    <?php if ($currentUser): ?>
                                        <form method="post">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                            <input type="hidden" name="tool_action" value="daily_review_flashcards">
                                            <input type="hidden" name="review_id" value="<?= e($selectedId) ?>">
                                            <button class="secondary-btn" type="submit">Criar baralho</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <div class="daily-card-preview">
                                    <?php foreach ((array)$review['flashcards'] as $card): ?>
                                        <article>
                                            <span><?= e($card['difficulty'] ?? 'Médio') ?></span>
                                            <strong><?= e($card['front'] ?? '') ?></strong>
                                            <p><?= e($card['back'] ?? '') ?></p>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </section>

                            <section class="research-next daily-next">
                                <label>
                                    <span>Prompt para o Professor IA</span>
                                    <textarea readonly rows="3"><?= e($review['next_session_prompt']) ?></textarea>
                                </label>
                                <div class="top-actions">
                                    <a class="ghost-btn" href="assistant.php">Abrir assistente</a>
                                    <a class="ghost-btn" href="exame.php">Treinar exame</a>
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
