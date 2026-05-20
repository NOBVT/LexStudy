<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$profile = getStudyProfile($userId);
$flash = consumeFlash();
$disciplines = disciplineLibrary($userId, $initialState, $profile);
$selectedKey = normalizeDisciplineKey($_GET['disciplina'] ?? null);
$selected = $disciplines[$selectedKey] ?? reset($disciplines);
$selectedKey = $selected['key'] ?? 'base';
$disciplineAnswer = null;
$teacherUsage = aiUsageSummary($userId, 'teacher_question');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['discipline_action'])) {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        setFlash('Sessão expirada. Tenta novamente.', 'error');
        header('Location: disciplinas.php?disciplina=' . rawurlencode($selectedKey));
        exit;
    }

    $selectedKey = normalizeDisciplineKey($_POST['discipline_key'] ?? $selectedKey);
    $selected = $disciplines[$selectedKey] ?? $selected;

    try {
        if ((string)$_POST['discipline_action'] === 'ask_professor') {
            $disciplineAnswer = askDisciplineProfessor($userId, $selected, (string)($_POST['discipline_question'] ?? ''));
            $teacherUsage = aiUsageSummary($userId, 'teacher_question');
        }
    } catch (Throwable $e) {
        setFlash($e->getMessage(), 'error');
        header('Location: disciplinas.php?disciplina=' . rawurlencode($selectedKey) . '#professor-disciplina');
        exit;
    }
}

$selectedDecks = flashcardDecksForDiscipline($userId, $selectedKey);
$selectedMistakes = array_slice($selected['mistakes'] ?? [], 0, 4);
$disciplinePlan = disciplineStudyPlan($selected);
$materialRecommendations = disciplineMaterialRecommendations($selected, $selectedDecks, $selectedMistakes);
$practiceBrief = disciplinePracticeBrief($selected, $selectedMistakes);
$resourceLinks = [
    ['label' => 'Aulas', 'target' => 'sala.php', 'detail' => 'Matéria guiada com professor IA'],
    ['label' => 'Casos', 'target' => 'cases.php', 'detail' => 'Aplicar regras a factos'],
    ['label' => 'Exame', 'target' => 'exame.php', 'detail' => 'Resposta com grelha de avaliação'],
    ['label' => 'Acórdãos', 'target' => 'acordaos.php', 'detail' => 'Jurisprudência resumida'],
    ['label' => 'Flashcards', 'target' => 'flashcards.php', 'detail' => 'Memória ativa e repetição'],
    ['label' => 'Caderno', 'target' => 'caderno.php', 'detail' => 'Erros e dúvidas persistentes'],
];
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Disciplinas - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Biblioteca curricular LexStudy com disciplinas, progresso, recursos e próximas ações.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css?v=2">
</head>
<body class="page-disciplines">
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
            <header class="topbar disciplines-hero">
                <div>
                    <span class="eyebrow">Biblioteca curricular</span>
                    <h1>Estuda por cadeira, não por botões soltos.</h1>
                    <p>Cada disciplina junta aulas, conceitos, casos, acórdãos, flashcards, erros e uma próxima ação concreta.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="plano.php">Plano semanal</a>
                    <a class="ghost-btn" href="sala.php">Sala</a>
                    <a class="primary-btn" href="#disciplina-ativa">Ver disciplina</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            <?php endif; ?>

            <section class="discipline-grid" aria-label="Disciplinas">
                <?php foreach ($disciplines as $key => $discipline): ?>
                    <a class="discipline-card <?= $key === $selectedKey ? 'is-active' : '' ?>" href="disciplinas.php?disciplina=<?= e(rawurlencode($key)) ?>" style="--discipline-accent: <?= e($discipline['accent']) ?>">
                        <span><?= e($discipline['short']) ?></span>
                        <strong><?= e($discipline['name']) ?></strong>
                        <small><?= e($discipline['description']) ?></small>
                        <div class="meter"><span style="width: <?= e($discipline['score']) ?>%"></span></div>
                        <em><?= e($discipline['score']) ?>% de sinal útil</em>
                    </a>
                <?php endforeach; ?>
            </section>

            <section id="disciplina-ativa" class="discipline-layout">
                <article class="discipline-detail" style="--discipline-accent: <?= e($selected['accent']) ?>">
                    <div class="section-heading split">
                        <div>
                            <span class="eyebrow"><?= e($selected['short']) ?></span>
                            <h2><?= e($selected['name']) ?></h2>
                            <p><?= e($selected['description']) ?></p>
                        </div>
                        <a class="primary-btn" href="<?= e($selected['next_action']['target']) ?>"><?= e($selected['next_action']['label']) ?></a>
                    </div>

                    <div class="discipline-metrics">
                        <article><span>Aulas</span><strong><?= e($selected['completed_lessons']) ?>/<?= e($selected['total_lessons']) ?></strong></article>
                        <article><span>Flashcards</span><strong><?= e($selected['decks_count']) ?></strong></article>
                        <article><span>Fontes</span><strong><?= e($selected['sources_count']) ?></strong></article>
                        <article><span>Erros</span><strong><?= e(count($selected['mistakes'])) ?></strong></article>
                    </div>

                    <div class="discipline-next-card">
                        <span>Próxima ação</span>
                        <h3><?= e($selected['next_action']['title']) ?></h3>
                        <p><?= e($selected['next_action']['reason']) ?></p>
                    </div>

                    <div class="discipline-concepts">
                        <?php foreach ($selected['concepts'] as $concept): ?>
                            <span><?= e($concept) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="discipline-resource-grid">
                        <?php foreach ($resourceLinks as $resource): ?>
                            <a href="<?= e($resource['target']) ?>">
                                <strong><?= e($resource['label']) ?></strong>
                                <small><?= e($resource['detail']) ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </article>

                <aside class="discipline-side">
                    <section>
                        <span class="eyebrow">Falhas abertas</span>
                        <h2>O que merece revisão.</h2>
                        <?php if ($selectedMistakes): ?>
                            <div class="discipline-weakness-list">
                                <?php foreach ($selectedMistakes as $mistake): ?>
                                    <article>
                                        <strong><?= e($mistake['title'] ?? 'Erro registado') ?></strong>
                                        <small><?= e($mistake['source'] ?? 'Caderno') ?></small>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="muted-copy">Ainda não há erros marcados nesta disciplina. Resolve um caso ou guarda uma dúvida no caderno para o sistema começar a ser mais preciso.</p>
                        <?php endif; ?>
                    </section>

                    <section>
                        <span class="eyebrow">Baralhos ligados</span>
                        <h2>Memória ativa.</h2>
                        <?php if ($selectedDecks): ?>
                            <div class="discipline-weakness-list">
                                <?php foreach (array_slice($selectedDecks, 0, 3) as $deck): ?>
                                    <article>
                                        <strong><?= e($deck['name'] ?? 'Baralho da disciplina') ?></strong>
                                        <small><?= e((int)($deck['total_cards'] ?? 0)) ?> cartões · <?= e($deck['area'] ?? $selected['name']) ?></small>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="muted-copy">Sem baralho específico ainda. Cria flashcards desta cadeira quando terminares uma aula ou acórdão.</p>
                        <?php endif; ?>
                    </section>
                </aside>
            </section>

            <section class="discipline-studio" aria-label="Ficha operacional da disciplina">
                <article class="discipline-panel">
                    <span class="eyebrow">Roteiro da cadeira</span>
                    <h2>Do zero até resposta escrita.</h2>
                    <div class="discipline-plan-list">
                        <?php foreach ($disciplinePlan as $step): ?>
                            <a href="<?= e($step['target']) ?>">
                                <span><?= e($step['stage']) ?></span>
                                <strong><?= e($step['title']) ?></strong>
                                <small><?= e($step['task']) ?></small>
                                <em><?= e($step['output']) ?></em>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="discipline-panel">
                    <span class="eyebrow">Treino de exame</span>
                    <h2><?= e($practiceBrief['title']) ?></h2>
                    <p><?= e($practiceBrief['case']) ?></p>
                    <div class="discipline-method-list">
                        <?php foreach ($practiceBrief['method'] as $methodStep): ?>
                            <span><?= e($methodStep) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="discipline-warning"><?= e($practiceBrief['trap']) ?></div>
                    <a class="secondary-btn" href="cases.php">Resolver caso</a>
                </article>

                <article class="discipline-panel discipline-material-panel">
                    <span class="eyebrow">Materiais recomendados</span>
                    <h2>O que abrir a seguir.</h2>
                    <div class="discipline-material-list">
                        <?php foreach ($materialRecommendations as $material): ?>
                            <a href="<?= e($material['target']) ?>">
                                <span><?= e($material['kind']) ?></span>
                                <strong><?= e($material['title']) ?></strong>
                                <small><?= e($material['detail']) ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </article>
            </section>

            <section id="professor-disciplina" class="discipline-professor">
                <div class="section-heading split">
                    <div>
                        <span class="eyebrow">Professor IA da disciplina</span>
                        <h2>Pergunta dentro de <?= e($selected['name']) ?>.</h2>
                        <p>Limite diário usado: <?= e($teacherUsage['used']) ?>/<?= e($teacherUsage['limit']) ?> perguntas.</p>
                    </div>
                    <a class="ghost-btn" href="billing.php">Ver plano</a>
                </div>

                <form method="post" class="discipline-professor-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="discipline_action" value="ask_professor">
                    <input type="hidden" name="discipline_key" value="<?= e($selectedKey) ?>">
                    <textarea name="discipline_question" rows="4" placeholder="Ex: explica a diferença entre incumprimento e responsabilidade civil com um exemplo de exame." required><?= e((string)($_POST['discipline_question'] ?? '')) ?></textarea>
                    <button class="primary-btn" type="submit">Perguntar ao professor</button>
                </form>

                <?php if ($disciplineAnswer): ?>
                    <article class="discipline-answer">
                        <span><?= e($disciplineAnswer['ai_mode'] === 'gemini' ? 'Resposta da IA' : 'Resposta local') ?></span>
                        <p><?= nl2br(e($disciplineAnswer['answer'])) ?></p>
                        <div class="discipline-answer-grid">
                            <section>
                                <strong>Pontos-chave</strong>
                                <?php foreach ($disciplineAnswer['key_points'] as $point): ?>
                                    <small><?= e($point) ?></small>
                                <?php endforeach; ?>
                            </section>
                            <section>
                                <strong>Treino</strong>
                                <small><?= e($disciplineAnswer['study_drill']) ?></small>
                                <strong>Armadilha comum</strong>
                                <small><?= e($disciplineAnswer['common_trap']) ?></small>
                            </section>
                        </div>
                        <em><?= e($disciplineAnswer['next_step']) ?></em>
                    </article>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
