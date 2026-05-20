<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$currentUser = currentUser();
$initialState = getUserStudyState($currentUser ? (int)$currentUser['id'] : null);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$decks = getFlashcardDecks($currentUser ? (int)$currentUser['id'] : null);
$deckId = isset($_GET['deck_id']) ? max(1, (int)$_GET['deck_id']) : null;
$notice = null;
$error = null;
$personalDecks = array_values(array_filter($decks, static fn(array $deck): bool => !empty($deck['user_id'])));
$coreDecks = array_values(array_filter($decks, static fn(array $deck): bool => empty($deck['user_id'])));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tool_action'] ?? '') === 'review_card') {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        $error = 'Sessão expirada. Recarrega a página.';
    } elseif (!$currentUser) {
        $notice = 'Modo local: cria conta para guardar repetição espaçada no servidor.';
    } else {
        try {
            $result = reviewFlashcard((int)$currentUser['id'], (int)$_POST['card_id'], (string)$_POST['grade']);
            $notice = $result['xp'] > 0 ? 'Revisão guardada. +' . $result['xp'] . ' XP.' : 'Revisão guardada. Esta carta volta já.';
            if (!empty($result['mistake_logged'])) {
                $notice .= ' Também ficou no Caderno de Erros.';
            }
        } catch (Throwable $e) {
            $error = 'Não consegui guardar a revisão.';
        }
    }
}

$card = getNextFlashcard($currentUser ? (int)$currentUser['id'] : null, $deckId);
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flashcards - <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-flashcards">
    <div class="app-shell">
        <aside class="sidebar" aria-label="Dock principal">
            <a class="brand" href="index.php"><span class="brand-mark"><img src="assets/lexstudy-mark.svg" alt=""></span><span><strong>LexStudy</strong><small>Direito aplicado</small></span></a>
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
                <small><?= e($initialState['masteredCards']) ?> cartas dominadas</small>
            </div>
        </aside>
        <main class="workspace">
            <header class="topbar">
                <div>
                    <span class="eyebrow">Repetição espaçada</span>
                    <h1>Revê conceitos até deixarem de te travar.</h1>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="conceitos.php">Criar por conceito</a>
                    <a class="ghost-btn" href="library.php">Criar por acórdão</a>
                    <a class="ghost-btn" href="caderno.php">Caderno de erros</a>
                    <a class="primary-btn" href="#rever">Rever agora</a>
                </div>
            </header>

            <?php if ($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>

            <section class="flash-hub">
                <article>
                    <span class="eyebrow">Como os baralhos nascem</span>
                    <h2>Memória ligada ao resto da plataforma.</h2>
                    <p>Os flashcards já não são só uma página isolada: podem nascer de conceitos explicados no Dicionário ou de acórdãos guardados na Biblioteca.</p>
                </article>
                <a href="conceitos.php">
                    <span>Dicionário</span>
                    <strong>Conceito → cartas</strong>
                    <small>Definição, requisitos, erro comum e pergunta de revisão.</small>
                </a>
                <a href="library.php">
                    <span>Biblioteca</span>
                    <strong>Acórdão → cartas</strong>
                    <small>Factos, decisão, ratio decidendi e perguntas de exame.</small>
                </a>
            </section>

            <section id="rever" class="section-block">
                <div class="section-heading split">
                    <div>
                        <span class="eyebrow">Baralhos</span>
                        <h2>Escolhe o que queres rever.</h2>
                    </div>
                    <a class="secondary-btn <?= $deckId ? '' : 'is-active' ?>" href="flashcards.php">Todas</a>
                </div>

                <?php if ($personalDecks): ?>
                    <div class="deck-group">
                        <span class="eyebrow">Criados por ti</span>
                        <div class="deck-filter">
                            <?php foreach ($personalDecks as $deck): ?>
                                <a class="<?= $deckId === (int)$deck['id'] ? 'is-active' : '' ?>" href="flashcards.php?deck_id=<?= e($deck['id']) ?>">
                                    <?= e($deck['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="deck-group">
                    <span class="eyebrow">Base do curso</span>
                    <div class="deck-filter">
                        <?php foreach ($coreDecks as $deck): ?>
                            <a class="<?= $deckId === (int)$deck['id'] ? 'is-active' : '' ?>" href="flashcards.php?deck_id=<?= e($deck['id']) ?>">
                                <?= e($deck['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section class="section-block two-column flash-review-panel">
                <div class="section-heading">
                    <span class="eyebrow">Carta atual</span>
                    <h2><?= e($card['deck_name'] ?? $card['area'] ?? 'Direito') ?></h2>
                    <p>Primeiro tenta responder sem ver. Depois vira a carta e classifica a dificuldade.</p>
                </div>

                <?php if ($card): ?>
                    <article class="flashcard study-card" data-study-card>
                        <span><?= e($card['area'] ?? 'Geral') ?> · <?= e($card['difficulty'] ?? 'Médio') ?></span>
                        <strong data-card-front><?= e($card['front']) ?></strong>
                        <p data-card-answer hidden><?= e($card['back']) ?></p>
                        <?php if (!empty($card['article_ref'])): ?><small><?= e($card['article_ref']) ?></small><?php endif; ?>
                        <div class="coach-actions">
                            <button class="secondary-btn" type="button" data-flip-card>Virar</button>
                        </div>
                        <form method="post" class="grade-actions">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="tool_action" value="review_card">
                            <input type="hidden" name="card_id" value="<?= e($card['id']) ?>">
                            <button class="secondary-btn" name="grade" value="again" type="submit">Errei</button>
                            <button class="secondary-btn" name="grade" value="hard" type="submit">Difícil</button>
                            <button class="primary-btn" name="grade" value="good" type="submit">Acertei</button>
                            <button class="primary-btn" name="grade" value="easy" type="submit">Fácil</button>
                        </form>
                    </article>
                <?php else: ?>
                    <article class="coach-output"><strong>Sem cartas</strong><p>Não encontrei cartas para este baralho.</p></article>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script>
        window.LEXSTUDY_FLASHCARD_CONTEXT = <?= jsData(['authenticated' => (bool)$currentUser]) ?>;
    </script>
    <script src="assets/flashcards.js"></script>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
