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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tool_action'] ?? '') === 'generate_theses') {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        $error = 'Sessão expirada. Recarrega a página.';
    } else {
        try {
            $topic = trim((string)($_POST['topic'] ?? ''));
            $area = trim((string)($_POST['area'] ?? 'Geral'));
            $facts = trim((string)($_POST['facts'] ?? ''));
            $lab = generateLegalThesisLab($topic, $area, $facts);
            $selectedId = saveLegalThesisLab($userId, $lab);
            $notice = 'Laboratório criado.';
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$labs = getLegalThesisLabs($userId);
if ($selectedId === 0 && $labs) {
    $selectedId = (int)$labs[0]['id'];
}

$selected = $selectedId > 0 ? getLegalThesisLab($userId, $selectedId) : null;
$lab = $selected['lab'] ?? null;
$modeLabel = static fn(?string $mode): string => $mode === 'inteligencia' ? 'Inteligência ativa' : 'Modo local';
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratório de Teses - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Laboratório de teses jurídicas para treinar argumentos opostos e perguntas de juiz.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-theses">
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
            <header class="topbar theses-hero">
                <div>
                    <span class="eyebrow">Laboratório de Teses</span>
                    <h1>Aprende a pensar contra ti próprio.</h1>
                    <p>Coloca um tema ou factos e vê os dois lados: tese, contra-tese, pontos fracos, perguntas difíceis e treino rápido.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="pesquisa.php">Pesquisa</a>
                    <a class="ghost-btn" href="exame.php">Exame</a>
                    <a class="primary-btn" href="#novo">Criar teses</a>
                </div>
            </header>

            <?php if ($flash): ?><div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div><?php endif; ?>
            <?php if ($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
            <?php if (!$dbReady): ?>
                <div class="notice warning">Sem base de dados, os laboratórios ficam guardados só nesta sessão.</div>
            <?php elseif (!$currentUser): ?>
                <div class="notice warning">Entra no painel para guardar os teus laboratórios de argumentação.</div>
            <?php endif; ?>

            <section class="theses-layout">
                <aside class="draft-history theses-history">
                    <div class="section-heading">
                        <span class="eyebrow">Arquivo</span>
                        <h2>Teses</h2>
                    </div>
                    <?php if (!$labs): ?>
                        <p>Ainda não criaste teses. Começa com um tema disputável, não com uma pergunta óbvia.</p>
                    <?php else: ?>
                        <div class="thread-list">
                            <?php foreach ($labs as $item): ?>
                                <a class="thread-card <?= (int)$item['id'] === $selectedId ? 'is-active' : '' ?>" href="teses.php?id=<?= e($item['id']) ?>">
                                    <span><?= e($item['area']) ?></span>
                                    <strong><?= e($item['topic']) ?></strong>
                                    <small><?= e($modeLabel($item['ai_mode'] ?? null)) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </aside>

                <section id="novo" class="tool-panel theses-command">
                    <span class="eyebrow">Novo laboratório</span>
                    <h2>Qual é o conflito?</h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="tool_action" value="generate_theses">
                        <label>
                            <span>Tema</span>
                            <input type="text" name="topic" placeholder="Ex: despedimento ilícito, furto por necessidade, caução no arrendamento">
                        </label>
                        <label>
                            <span>Área</span>
                            <select name="area">
                                <?php foreach ($areas as $areaOption): ?>
                                    <option value="<?= e($areaOption) ?>"><?= e($areaOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            <span>Factos opcionais</span>
                            <textarea name="facts" rows="7" placeholder="Se tiveres factos, cola aqui. Se não tiveres, basta o tema."></textarea>
                        </label>
                        <button class="primary-btn" type="submit">Construir teses</button>
                    </form>
                </section>

                <section class="theses-output">
                    <?php if (!$lab): ?>
                        <article class="tool-panel quiet-panel theses-empty">
                            <span class="eyebrow">Argumentação</span>
                            <h2>As teses aparecem aqui.</h2>
                            <p>Esta ferramenta serve para treinar maturidade jurídica: saber defender uma posição e, logo a seguir, destruí-la com honestidade.</p>
                            <div class="research-method-strip">
                                <span>Tese</span>
                                <span>Contra-tese</span>
                                <span>Riscos</span>
                                <span>Juiz</span>
                            </div>
                        </article>
                    <?php else: ?>
                        <article class="library-detail theses-dossier">
                            <div class="section-heading split">
                                <div>
                                    <span class="eyebrow"><?= e($lab['area']) ?></span>
                                    <h2><?= e($lab['title']) ?></h2>
                                    <p><?= e($lab['issue']) ?></p>
                                </div>
                                <span class="difficulty medium"><?= e($modeLabel($selected['ai_mode'] ?? ($lab['_mode'] ?? 'local'))) ?></span>
                            </div>

                            <?php if (!empty($lab['facts'])): ?>
                                <section class="exam-scenario theses-facts">
                                    <span>Factos</span>
                                    <p><?= nl2br(e($lab['facts'])) ?></p>
                                </section>
                            <?php endif; ?>

                            <div class="theses-duel">
                                <?php foreach (['side_a', 'side_b'] as $sideKey): ?>
                                    <?php $side = (array)($lab[$sideKey] ?? []); ?>
                                    <article class="thesis-side <?= e($sideKey) ?>">
                                        <span><?= e($side['name'] ?? 'Tese') ?></span>
                                        <h3><?= e($side['position'] ?? '') ?></h3>
                                        <div class="case-tool-grid">
                                            <section>
                                                <strong>Argumentos</strong>
                                                <ul class="result-list">
                                                    <?php foreach ((array)($side['arguments'] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                                </ul>
                                            </section>
                                            <section>
                                                <strong>Factos úteis</strong>
                                                <ul class="result-list">
                                                    <?php foreach ((array)($side['facts_to_use'] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                                </ul>
                                            </section>
                                        </div>
                                        <div class="thesis-risk">
                                            <strong>Pontos fracos</strong>
                                            <ul class="result-list">
                                                <?php foreach ((array)($side['weak_points'] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <p class="thesis-reply"><strong>Melhor resposta:</strong> <?= e($side['best_reply'] ?? '') ?></p>
                                    </article>
                                <?php endforeach; ?>
                            </div>

                            <div class="case-tool-grid">
                                <article class="coach-output compact">
                                    <strong>Perguntas de juiz</strong>
                                    <ul class="result-list">
                                        <?php foreach ((array)($lab['judge_questions'] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                    </ul>
                                </article>
                                <article class="coach-output compact">
                                    <strong>Condições para ganhar</strong>
                                    <ul class="result-list">
                                        <?php foreach ((array)($lab['winning_conditions'] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                    </ul>
                                </article>
                            </div>

                            <section class="research-next theses-next">
                                <label>
                                    <span>Nota estratégica</span>
                                    <textarea readonly rows="3"><?= e($lab['strategy_note'] ?? '') ?></textarea>
                                </label>
                                <div class="top-actions">
                                    <a class="ghost-btn" href="pecas.php">Levar para peça</a>
                                    <a class="ghost-btn" href="exame.php">Treinar exame</a>
                                </div>
                            </section>

                            <section class="coach-output compact">
                                <strong>Exercícios rápidos</strong>
                                <ul class="result-list">
                                    <?php foreach ((array)($lab['study_drills'] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                                </ul>
                            </section>

                            <p class="draft-disclaimer"><?= e($lab['disclaimer']) ?></p>
                        </article>
                    <?php endif; ?>
                </section>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
