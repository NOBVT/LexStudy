<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$curriculum = studyRoomCurriculum();
$lessons = flattenStudyLessons();
$selectedLesson = getStudyLesson($_GET['aula'] ?? null);
$canAccessSelectedLesson = canAccessStudyLesson($userId, $selectedLesson);
$teacherAnswer = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['study_room_action'])) {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        setFlash('Sessão expirada. Tenta novamente.', 'error');
        header('Location: sala.php?aula=' . rawurlencode((string)($_POST['lesson_id'] ?? $selectedLesson['id'])));
        exit;
    }

    try {
        $lesson = getStudyLesson((string)($_POST['lesson_id'] ?? $selectedLesson['id']));
        $action = (string)$_POST['study_room_action'];

        if ($action === 'complete_lesson') {
            $isNew = completeStudyLesson($userId, $lesson['id']);
            setFlash($isNew ? 'Aula concluída. Progresso atualizado.' : 'Esta aula já estava concluída.', 'success');
            header('Location: sala.php?aula=' . rawurlencode($lesson['id']));
            exit;
        }

        if ($action === 'ask_teacher') {
            $selectedLesson = $lesson;
            $canAccessSelectedLesson = canAccessStudyLesson($userId, $selectedLesson);
            $teacherAnswer = askStudyProfessor($userId, $lesson, (string)($_POST['teacher_question'] ?? ''));
        }
    } catch (Throwable $e) {
        setFlash($e->getMessage(), 'error');
        header('Location: sala.php?aula=' . rawurlencode((string)($_POST['lesson_id'] ?? $selectedLesson['id'])));
        exit;
    }
}

$progressMap = getStudyRoomProgress($userId);
$canAccessSelectedLesson = canAccessStudyLesson($userId, $selectedLesson);
$completedCount = 0;
foreach ($lessons as $lesson) {
    if (($progressMap[$lesson['id']]['status'] ?? '') === 'completed') {
        $completedCount++;
    }
}
$totalLessons = count($lessons);
$roomPercent = clampScore(($completedCount / max(1, $totalLessons)) * 100);
$subscription = currentUserSubscription($userId);
$teacherUsage = aiUsageSummary($userId, 'teacher_question');
$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$flash = consumeFlash();
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sala de Estudo - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Sala de Estudo com aulas de Direito do zero, recursos e professor IA.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-study-room">
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
                <small><?= e($completedCount) ?> / <?= e($totalLessons) ?> aulas</small>
            </div>
        </aside>

        <main class="workspace">
            <header class="topbar study-room-hero">
                <div>
                    <span class="eyebrow">Sala de Estudo</span>
                    <h1>Aulas para aprender Direito do zero.</h1>
                    <p>Matéria organizada, recursos para estudar, exercícios e um professor IA para explicar a aula como numa explicação particular.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="mentor.php">Mentor</a>
                    <a class="ghost-btn" href="flashcards.php">Revisão</a>
                    <a class="primary-btn" href="assistant.php">Professor livre</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            <?php endif; ?>

            <section class="study-room-dashboard">
                <article>
                    <span class="eyebrow">Percurso</span>
                    <strong><?= e($roomPercent) ?>%</strong>
                    <div class="meter"><span style="width: <?= e($roomPercent) ?>%"></span></div>
                </article>
                <article>
                    <span class="eyebrow">Aula aberta</span>
                    <strong><?= e($selectedLesson['title']) ?></strong>
                    <small><?= e($selectedLesson['module_title']) ?> · <?= e($selectedLesson['duration']) ?> min</small>
                </article>
                <article>
                    <span class="eyebrow">Método</span>
                    <strong>Ver, ler, aplicar</strong>
                    <small>Vídeo ou leitura, resumo da aula e exercício curto.</small>
                </article>
            </section>

            <section class="study-room-layout">
                <aside class="lesson-map">
                    <?php foreach ($curriculum as $module): ?>
                        <section class="lesson-module">
                            <span><?= e($module['level']) ?><?= !empty($module['plus_only']) ? ' · Plus' : '' ?></span>
                            <h2><?= e($module['title']) ?></h2>
                            <p><?= e($module['subtitle']) ?></p>
                            <div class="lesson-list">
                                <?php foreach ($module['lessons'] as $lesson): ?>
                                    <?php $isDone = ($progressMap[$lesson['id']]['status'] ?? '') === 'completed'; ?>
                                    <?php $isLocked = !canAccessStudyLesson($userId, getStudyLesson($lesson['id'])); ?>
                                    <a class="<?= $lesson['id'] === $selectedLesson['id'] ? 'is-active' : '' ?> <?= $isDone ? 'is-done' : '' ?> <?= $isLocked ? 'is-locked' : '' ?>" href="sala.php?aula=<?= e(rawurlencode($lesson['id'])) ?>">
                                        <strong><?= e($lesson['title']) ?></strong>
                                        <small><?= $isLocked ? 'Plus' : ($isDone ? 'Concluída' : e($lesson['duration'] . ' min')) ?></small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </aside>

                <article class="lesson-stage">
                    <div class="lesson-stage-head">
                        <div>
                            <span class="eyebrow"><?= e($selectedLesson['module_title']) ?></span>
                            <h2><?= e($selectedLesson['title']) ?></h2>
                            <p><?= e($selectedLesson['goal']) ?></p>
                        </div>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="study_room_action" value="complete_lesson">
                            <input type="hidden" name="lesson_id" value="<?= e($selectedLesson['id']) ?>">
                            <?php if ($canAccessSelectedLesson): ?>
                                <button class="primary-btn" type="submit">
                                    <?= (($progressMap[$selectedLesson['id']]['status'] ?? '') === 'completed') ? 'Aula concluída' : 'Marcar concluída' ?>
                                </button>
                            <?php else: ?>
                                <a class="primary-btn" href="billing.php">Desbloquear Plus</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <?php if (!$canAccessSelectedLesson): ?>
                        <section class="locked-panel">
                            <span class="eyebrow">Conteúdo Plus</span>
                            <h2>Esta aula faz parte da área paga.</h2>
                            <p>O plano Free deixa estudar os Fundamentos. O Plus desbloqueia as aulas por disciplina, maior limite de mensagens e professor IA com mais margem diária.</p>
                            <a class="primary-btn" href="billing.php">Ver planos</a>
                        </section>
                    <?php else: ?>
                        <div class="lesson-body">
                            <section>
                                <span class="eyebrow">Resumo da aula</span>
                                <p><?= e($selectedLesson['summary']) ?></p>
                            </section>

                            <section>
                                <span class="eyebrow">Quadro mental</span>
                                <ol class="lesson-steps">
                                    <?php foreach ($selectedLesson['steps'] as $step): ?>
                                        <li><?= e($step) ?></li>
                                    <?php endforeach; ?>
                                </ol>
                            </section>

                            <section class="lesson-exercise">
                                <span class="eyebrow">Exercício rápido</span>
                                <p><?= e($selectedLesson['exercise']) ?></p>
                                <a class="secondary-btn" href="mentor.php">Pedir correção no Mentor</a>
                            </section>
                        </div>

                        <div class="resource-grid">
                            <?php foreach ($selectedLesson['resources'] as $resource): ?>
                                <a class="resource-card" href="<?= e($resource['url']) ?>" <?= str_starts_with($resource['url'], 'http') ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                                    <span><?= e($resource['type']) ?></span>
                                    <strong><?= e($resource['label']) ?></strong>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($canAccessSelectedLesson): ?>
                    <section class="teacher-panel">
                        <div class="section-heading">
                            <span class="eyebrow">Professor IA</span>
                            <h2>Pergunta sobre esta aula.</h2>
                            <p>Usa isto para tirar dúvidas pequenas, pedir exemplos ou transformar a aula numa resposta de exame. Hoje: <?= e($teacherUsage['used']) ?>/<?= e($teacherUsage['limit']) ?> perguntas.</p>
                        </div>
                        <form method="post" class="teacher-form">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="study_room_action" value="ask_teacher">
                            <input type="hidden" name="lesson_id" value="<?= e($selectedLesson['id']) ?>">
                            <textarea name="teacher_question" rows="4" placeholder="Ex: explica isto como se eu nunca tivesse estudado Direito" required><?= e((string)($_POST['teacher_question'] ?? '')) ?></textarea>
                            <button class="primary-btn" type="submit">Perguntar ao professor</button>
                        </form>

                        <?php if ($teacherAnswer): ?>
                            <article class="teacher-answer">
                                <span><?= e($teacherAnswer['ai_mode'] === 'gemini' ? 'Resposta da IA' : 'Resposta local') ?></span>
                                <p><?= nl2br(e($teacherAnswer['answer'])) ?></p>
                                <ul>
                                    <?php foreach ($teacherAnswer['key_points'] as $point): ?>
                                        <li><?= e($point) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <strong>Mini exercício: <?= e($teacherAnswer['mini_exercise']) ?></strong>
                                <small><?= e($teacherAnswer['warning']) ?></small>
                            </article>
                        <?php endif; ?>
                    </section>
                    <?php endif; ?>
                </article>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
