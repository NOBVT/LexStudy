<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$flash = consumeFlash();
$bootcamp = foundationBootcamp();
$selectedModule = getFoundationModule($_GET['modulo'] ?? null);
$foundationAnswer = null;
$teacherUsage = aiUsageSummary($userId, 'teacher_question');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['foundation_action'])) {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        setFlash('Sessão expirada. Tenta novamente.', 'error');
        header('Location: comecar.php');
        exit;
    }

    try {
        $selectedModule = getFoundationModule((string)($_POST['module_key'] ?? $selectedModule['key']));
        if ((string)$_POST['foundation_action'] === 'ask') {
            $foundationAnswer = askFoundationProfessor($userId, $selectedModule, (string)($_POST['foundation_question'] ?? ''));
            $teacherUsage = aiUsageSummary($userId, 'teacher_question');
        }
    } catch (Throwable $e) {
        setFlash($e->getMessage(), 'error');
        header('Location: comecar.php?modulo=' . rawurlencode($selectedModule['key']));
        exit;
    }
}
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Começar do Zero - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Entrada guiada para começar a estudar Direito do zero.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-foundation">
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
            <header class="topbar foundation-hero">
                <div>
                    <span class="eyebrow">Começar do Zero</span>
                    <h1>Aprende o método antes de te afogares em livros.</h1>
                    <p>Uma entrada curta para perceber o curso, ler leis com cabeça e resolver o primeiro caso sem decorar tudo à força.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="plano.php">Plano semanal</a>
                    <a class="ghost-btn" href="sala.php">Sala</a>
                    <a class="primary-btn" href="#professor-arranque">Professor IA</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            <?php endif; ?>

            <section class="foundation-grid" aria-label="Módulos de arranque">
                <?php foreach ($bootcamp as $key => $module): ?>
                    <a class="foundation-card <?= $key === $selectedModule['key'] ? 'is-active' : '' ?>" href="comecar.php?modulo=<?= e(rawurlencode($key)) ?>">
                        <span><?= e($module['tagline']) ?></span>
                        <strong><?= e($module['title']) ?></strong>
                        <small><?= e($module['promise']) ?></small>
                    </a>
                <?php endforeach; ?>
            </section>

            <section class="foundation-layout">
                <article class="foundation-panel">
                    <span class="eyebrow">Módulo ativo</span>
                    <h2><?= e($selectedModule['title']) ?></h2>
                    <p><?= e($selectedModule['promise']) ?></p>

                    <div class="foundation-steps">
                        <?php foreach ($selectedModule['steps'] as $index => $step): ?>
                            <div>
                                <span><?= e(str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                                <p><?= e($step) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="foundation-tags">
                        <?php foreach ($selectedModule['vocabulary'] as $word): ?>
                            <span><?= e($word) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="foundation-exercise">
                        <span class="eyebrow">Exercício</span>
                        <p><?= e($selectedModule['exercise']) ?></p>
                        <div class="button-row">
                            <a class="secondary-btn" href="<?= e($selectedModule['target']) ?>">Praticar agora</a>
                            <a class="ghost-btn" href="caderno.php">Guardar dificuldade</a>
                        </div>
                    </div>
                </article>

                <aside class="foundation-panel">
                    <span class="eyebrow">Erros típicos</span>
                    <h2>Evita isto desde cedo.</h2>
                    <ul class="clean-list">
                        <?php foreach ($selectedModule['mistakes'] as $mistake): ?>
                            <li><?= e($mistake) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </aside>
            </section>

            <section class="foundation-timeline" aria-label="Primeira semana de Direito">
                <?php foreach ([
                    ['Dia 1', 'Perceber o que é norma jurídica', 'comecar.php?modulo=mapa'],
                    ['Dia 2', 'Ler um artigo e separar requisitos', 'comecar.php?modulo=norma'],
                    ['Dia 3', 'Resolver um mini-caso com método', 'comecar.php?modulo=caso'],
                    ['Dia 4', 'Pesquisar lei e acórdão sem se perder', 'comecar.php?modulo=fontes'],
                    ['Dia 5', 'Transformar tudo em flashcards', 'flashcards.php'],
                ] as $item): ?>
                    <a href="<?= e($item[2]) ?>">
                        <span><?= e($item[0]) ?></span>
                        <strong><?= e($item[1]) ?></strong>
                    </a>
                <?php endforeach; ?>
            </section>

            <section id="professor-arranque" class="teacher-panel foundation-professor">
                <div class="section-heading">
                    <span class="eyebrow">Professor de Arranque</span>
                    <h2>Pergunta como se estivesses na primeira aula.</h2>
                    <p>Limite diário usado: <?= e($teacherUsage['used']) ?>/<?= e($teacherUsage['limit']) ?> perguntas.</p>
                </div>
                <form method="post" class="teacher-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="foundation_action" value="ask">
                    <input type="hidden" name="module_key" value="<?= e($selectedModule['key']) ?>">
                    <textarea name="foundation_question" rows="4" placeholder="Ex: como é que eu sei qual artigo aplicar num caso?" required><?= e((string)($_POST['foundation_question'] ?? '')) ?></textarea>
                    <button class="primary-btn" type="submit">Perguntar</button>
                </form>

                <?php if ($foundationAnswer): ?>
                    <article class="teacher-answer">
                        <span><?= e($foundationAnswer['ai_mode'] === 'gemini' ? 'Resposta da IA' : 'Resposta local') ?></span>
                        <p><?= nl2br(e($foundationAnswer['answer'])) ?></p>
                        <?php if ($foundationAnswer['example'] !== ''): ?><strong>Exemplo: <?= e($foundationAnswer['example']) ?></strong><?php endif; ?>
                        <small>Próximo passo: <?= e($foundationAnswer['next_step']) ?></small>
                        <small><?= e($foundationAnswer['warning']) ?></small>
                    </article>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
