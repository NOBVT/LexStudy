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
$areas = legalExamAreas();
$difficulties = legalExamDifficulties();
$notice = null;
$error = null;
$xpAwarded = 0;
$selectedId = isset($_GET['id']) ? max(1, (int)$_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        $error = 'Sessão expirada. Recarrega a página.';
    } else {
        try {
            $action = (string)($_POST['tool_action'] ?? '');

            if ($action === 'generate_exam') {
                $area = trim((string)($_POST['area'] ?? 'Geral'));
                $difficulty = trim((string)($_POST['difficulty'] ?? 'Médio'));
                $focus = trim((string)($_POST['focus'] ?? ''));
                $exam = generateLegalExam($area, $difficulty, $focus);
                $selectedId = saveLegalExamSession($userId, $exam);
                $notice = 'Exame criado.';
            }

            if ($action === 'evaluate_exam') {
                $selectedId = max(1, (int)($_POST['exam_id'] ?? 0));
                $session = getLegalExamSession($userId, $selectedId);
                if (!$session) {
                    throw new RuntimeException('Exame não encontrado.');
                }
                $answer = trim((string)($_POST['answer'] ?? ''));
                $evaluation = evaluateLegalExamAnswer((array)($session['exam'] ?? []), $answer);
                $xpAwarded = saveLegalExamEvaluation($userId, $selectedId, $answer, $evaluation);
                $notice = 'Resposta corrigida.' . ($xpAwarded > 0 ? ' Ganhaste ' . $xpAwarded . ' XP.' : '');
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$sessions = getLegalExamSessions($userId);
if ($selectedId === 0 && $sessions) {
    $selectedId = (int)$sessions[0]['id'];
}

$selected = $selectedId > 0 ? getLegalExamSession($userId, $selectedId) : null;
$exam = $selected['exam'] ?? null;
$evaluation = $selected['evaluation'] ?? null;
$answer = (string)($selected['answer'] ?? '');
$modeLabel = static fn(?string $mode): string => $mode === 'inteligencia' ? 'Inteligência ativa' : 'Modo local';
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Banca de Exame - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Simulador de exame jurídico com enunciado, resposta escrita e correção por grelha.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-exam">
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
            <header class="topbar exam-hero">
                <div>
                    <span class="eyebrow">Banca de Exame</span>
                    <h1>Treina como se estivesses numa prova escrita.</h1>
                    <p>Gera um enunciado, responde por escrito e recebe uma correção com nota, grelha, falhas e tarefas de melhoria.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="ferramentas.php">Ferramentas</a>
                    <a class="ghost-btn" href="simulator.php">Juiz Virtual</a>
                    <a class="primary-btn" href="#novo">Novo exame</a>
                </div>
            </header>

            <?php if ($flash): ?><div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div><?php endif; ?>
            <?php if ($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
            <?php if (!$dbReady): ?>
                <div class="notice warning">Sem base de dados, os exames ficam guardados só nesta sessão.</div>
            <?php elseif (!$currentUser): ?>
                <div class="notice warning">Entra no painel para guardar exames, notas e evolução.</div>
            <?php endif; ?>

            <section class="exam-layout">
                <aside class="draft-history exam-history">
                    <div class="section-heading">
                        <span class="eyebrow">Arquivo</span>
                        <h2>Exames</h2>
                    </div>
                    <?php if (!$sessions): ?>
                        <p>Ainda não há provas. Cria um exame curto e responde como se fosse uma avaliação real.</p>
                    <?php else: ?>
                        <div class="thread-list">
                            <?php foreach ($sessions as $item): ?>
                                <a class="thread-card <?= (int)$item['id'] === $selectedId ? 'is-active' : '' ?>" href="exame.php?id=<?= e($item['id']) ?>">
                                    <span><?= e($item['area']) ?> · <?= e($item['difficulty']) ?></span>
                                    <strong><?= e($item['title']) ?></strong>
                                    <small>
                                        <?php if (($item['score'] ?? null) !== null): ?>
                                            Nota <?= e($item['score']) ?>/20
                                        <?php else: ?>
                                            Por responder
                                        <?php endif; ?>
                                        · <?= e($modeLabel($item['ai_mode'] ?? null)) ?>
                                    </small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </aside>

                <section id="novo" class="tool-panel exam-command">
                    <span class="eyebrow">Nova prova</span>
                    <h2>Configurar exame</h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="tool_action" value="generate_exam">
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
                                <span>Dificuldade</span>
                                <select name="difficulty">
                                    <?php foreach ($difficulties as $difficulty): ?>
                                        <option value="<?= e($difficulty) ?>" <?= $difficulty === 'Médio' ? 'selected' : '' ?>><?= e($difficulty) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        <label>
                            <span>Foco opcional</span>
                            <input type="text" name="focus" placeholder="Ex: furto e estado de necessidade, despedimento ilícito, responsabilidade civil">
                        </label>
                        <button class="primary-btn" type="submit">Gerar exame</button>
                    </form>
                </section>

                <section class="exam-output">
                    <?php if (!$exam): ?>
                        <article class="tool-panel quiet-panel exam-empty">
                            <span class="eyebrow">Prova</span>
                            <h2>O exame aparece aqui.</h2>
                            <p>A diferença desta ferramenta é a correção por critérios. Ela não te diz só “está certo”; mostra onde perdeste pontos.</p>
                            <div class="research-method-strip exam-method-strip">
                                <span>Enunciado</span>
                                <span>Resposta</span>
                                <span>Grelha</span>
                                <span>Plano</span>
                            </div>
                        </article>
                    <?php else: ?>
                        <article class="library-detail exam-paper">
                            <div class="section-heading split">
                                <div>
                                    <span class="eyebrow"><?= e($exam['area']) ?> · <?= e($exam['difficulty']) ?> · <?= e($exam['time_limit']) ?> min</span>
                                    <h2><?= e($exam['title']) ?></h2>
                                    <?php if (!empty($exam['focus'])): ?><p>Foco: <?= e($exam['focus']) ?></p><?php endif; ?>
                                </div>
                                <span class="difficulty medium"><?= e($modeLabel($selected['ai_mode'] ?? ($exam['_mode'] ?? 'local'))) ?></span>
                            </div>

                            <section class="exam-scenario">
                                <span>Enunciado</span>
                                <p><?= nl2br(e($exam['scenario'])) ?></p>
                            </section>

                            <div class="exam-question-grid">
                                <article class="coach-output compact">
                                    <strong>Questões</strong>
                                    <ol class="result-list numbered">
                                        <?php foreach ((array)($exam['questions'] ?? []) as $question): ?>
                                            <li><?= e($question) ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                </article>
                                <article class="coach-output compact">
                                    <strong>Materiais permitidos</strong>
                                    <ul class="result-list">
                                        <?php foreach ((array)($exam['legal_materials'] ?? []) as $material): ?>
                                            <li><?= e($material) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </article>
                            </div>

                            <section class="exam-rubric-preview">
                                <span class="eyebrow">Grelha de correção</span>
                                <div>
                                    <?php foreach ((array)($exam['rubric'] ?? []) as $item): ?>
                                        <span><?= e($item['criterion'] ?? 'Critério') ?> · <?= e($item['max_points'] ?? 0) ?> pts</span>
                                    <?php endforeach; ?>
                                </div>
                            </section>

                            <?php if (!$evaluation): ?>
                                <form method="post" class="exam-answer-form">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="tool_action" value="evaluate_exam">
                                    <input type="hidden" name="exam_id" value="<?= e($selectedId) ?>">
                                    <label>
                                        <span>Resposta do aluno</span>
                                        <textarea name="answer" rows="12" placeholder="<?= e($exam['answer_method'] ?? 'Responde por IRAC.') ?>"><?= e($answer) ?></textarea>
                                    </label>
                                    <div class="top-actions">
                                        <a class="ghost-btn" href="foco.php">Abrir Modo Foco</a>
                                        <button class="primary-btn" type="submit">Corrigir resposta</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <section class="exam-correction">
                                    <div class="exam-score-card">
                                        <span>Nota</span>
                                        <strong><?= e($evaluation['score'] ?? 0) ?>/20</strong>
                                        <small><?= e($evaluation['grade_label'] ?? '') ?></small>
                                    </div>
                                    <div class="examiner-comment">
                                        <span class="eyebrow">Comentário do corretor</span>
                                        <p><?= e($evaluation['examiner_comment'] ?? '') ?></p>
                                    </div>
                                </section>

                                <div class="exam-rubric-grid">
                                    <?php foreach ((array)($evaluation['rubric'] ?? []) as $item): ?>
                                        <article>
                                            <span><?= e($item['criterion'] ?? 'Critério') ?></span>
                                            <strong><?= e($item['points'] ?? 0) ?>/<?= e($item['max_points'] ?? 0) ?></strong>
                                            <p><?= e($item['comment'] ?? '') ?></p>
                                        </article>
                                    <?php endforeach; ?>
                                </div>

                                <div class="case-tool-grid">
                                    <article class="coach-output compact">
                                        <strong>Pontos fortes</strong>
                                        <ul class="result-list">
                                            <?php foreach ((array)($evaluation['strengths'] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                        </ul>
                                    </article>
                                    <article class="coach-output compact">
                                        <strong>Falhas</strong>
                                        <ul class="result-list">
                                            <?php foreach ((array)($evaluation['gaps'] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                        </ul>
                                    </article>
                                </div>

                                <section class="draft-section exam-model-answer">
                                    <strong>Resposta-modelo</strong>
                                    <p><?= nl2br(e($evaluation['model_answer'] ?? '')) ?></p>
                                </section>

                                <div class="case-tool-grid">
                                    <article class="coach-output compact">
                                        <strong>Plano de melhoria</strong>
                                        <ul class="result-list">
                                            <?php foreach ((array)($evaluation['next_study_tasks'] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                        </ul>
                                    </article>
                                    <article class="coach-output compact">
                                        <strong>Flashcards sugeridos</strong>
                                        <ul class="result-list">
                                            <?php foreach ((array)($evaluation['flashcard_prompts'] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                        </ul>
                                    </article>
                                </div>

                                <?php if ($answer !== ''): ?>
                                    <details class="exam-answer-review">
                                        <summary>A tua resposta</summary>
                                        <p><?= nl2br(e($answer)) ?></p>
                                    </details>
                                <?php endif; ?>
                            <?php endif; ?>
                        </article>
                    <?php endif; ?>
                </section>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
