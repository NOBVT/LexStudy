<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
handleMistakeNotebookRequest($userId);

$initialState = getUserStudyState($userId);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$mistakes = getStudyMistakes($userId);
$stats = studyMistakeStats($userId);
$flash = consumeFlash();
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Caderno de Erros - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Caderno de erros para transformar falhas em revisão jurídica orientada.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-errors">
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
                <small><?= e($stats['open']) ?> erros ativos</small>
            </div>
        </aside>

        <main class="workspace">
            <header class="topbar">
                <div>
                    <span class="eyebrow">Caderno de Erros</span>
                    <h1>Estuda primeiro aquilo que te fez falhar.</h1>
                    <p>O objetivo não é colecionar erros. É transformar cada falha numa próxima ação clara.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="flashcards.php">Rever cartas</a>
                    <a class="ghost-btn" href="cases.php">Treinar casos</a>
                    <a class="primary-btn" href="assistant.php">Pedir explicação</a>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="notice <?= e($flash['type'] ?? 'success') ?>"><?= e($flash['message'] ?? '') ?></div>
            <?php endif; ?>

            <?php if (!$currentUser): ?>
                <div class="notice warning">
                    Estás em modo local. Os erros de casos ficam neste browser; com conta, o caderno fica guardado na base de dados.
                </div>
            <?php endif; ?>

            <section class="error-dashboard">
                <article>
                    <span>Erros ativos</span>
                    <strong data-error-count><?= e($stats['open']) ?></strong>
                </article>
                <article>
                    <span>Já revistos</span>
                    <strong><?= e($stats['resolved']) ?></strong>
                </article>
                <article>
                    <span>Área crítica</span>
                    <strong data-error-area><?= e($stats['main_area']) ?></strong>
                </article>
            </section>

            <section class="error-layout">
                <article class="error-review-plan">
                    <span class="eyebrow">Método</span>
                    <h2>Revisão em três passos.</h2>
                    <ol class="study-steps">
                        <li>Lê o erro sem tentares justificar a tua resposta antiga.</li>
                        <li>Reescreve a resposta correta em linguagem simples.</li>
                        <li>Volta ao caso ou flashcard e testa sem olhar.</li>
                    </ol>
                    <a class="secondary-btn" href="index.php#diagnostico">Ajustar plano</a>
                </article>

                <div class="error-list" data-server-errors="<?= $mistakes ? '1' : '0' ?>">
                    <?php if ($mistakes): ?>
                        <?php foreach ($mistakes as $mistake): ?>
                            <article class="error-card">
                                <div>
                                    <span><?= e($mistake['area'] ?? 'Geral') ?> · <?= e($mistake['source_type'] ?? 'erro') ?></span>
                                    <h2><?= e($mistake['title']) ?></h2>
                                    <?php if (!empty($mistake['prompt'])): ?>
                                        <p><b>Resposta/pergunta:</b> <?= e($mistake['prompt']) ?></p>
                                    <?php endif; ?>
                                    <p><b>Correção:</b> <?= e($mistake['correction']) ?></p>
                                    <small><?= e($mistake['next_step'] ?? 'Rever e tentar novamente.') ?></small>
                                </div>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="mistake_action" value="resolve">
                                    <input type="hidden" name="mistake_id" value="<?= e($mistake['id']) ?>">
                                    <button class="secondary-btn" type="submit">Marcar revisto</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <article class="empty-errors" data-empty-errors>
                            <span class="eyebrow">Sem erros guardados</span>
                            <h2>Ainda não há falhas para rever.</h2>
                            <p>Resolve um caso e pede análise, ou marca um flashcard como difícil. O caderno começa a ganhar utilidade a partir daí.</p>
                            <div class="coach-actions">
                                <a class="primary-btn" href="cases.php">Resolver caso</a>
                                <a class="secondary-btn" href="flashcards.php">Rever flashcards</a>
                            </div>
                        </article>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <script>
        (() => {
            const list = document.querySelector('[data-server-errors="0"]');
            if (!list) return;

            let mistakes = [];
            try {
                mistakes = JSON.parse(localStorage.getItem('lexstudy.mistakes.v1') || '[]')
                    .filter((item) => (item.status || 'open') === 'open')
                    .slice(0, 24);
            } catch (error) {
                mistakes = [];
            }
            if (!mistakes.length) return;

            const escapeHtml = (value) => String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            list.innerHTML = mistakes.map((mistake) => `
                <article class="error-card">
                    <div>
                        <span>${escapeHtml(mistake.area || 'Geral')} · local</span>
                        <h2>${escapeHtml(mistake.title || 'Erro de estudo')}</h2>
                        ${mistake.prompt ? `<p><b>Resposta/pergunta:</b> ${escapeHtml(mistake.prompt)}</p>` : ''}
                        <p><b>Correção:</b> ${escapeHtml(mistake.correction || 'Rever este ponto.')}</p>
                        <small>${escapeHtml(mistake.next_step || 'Rever e tentar novamente.')}</small>
                    </div>
                    <button class="secondary-btn" type="button" data-local-resolve="${Number(mistake.id) || 0}">Marcar revisto</button>
                </article>
            `).join('');

            const count = document.querySelector('[data-error-count]');
            if (count) count.textContent = String(mistakes.length);
            const area = document.querySelector('[data-error-area]');
            if (area) area.textContent = mistakes[0]?.area || 'Sessão local';

            list.addEventListener('click', (event) => {
                const button = event.target.closest('[data-local-resolve]');
                if (!button) return;
                const id = Number(button.dataset.localResolve);
                const updated = mistakes.map((item) => Number(item.id) === id ? { ...item, status: 'resolved' } : item);
                localStorage.setItem('lexstudy.mistakes.v1', JSON.stringify(updated));
                button.closest('.error-card')?.remove();
                const remaining = list.querySelectorAll('.error-card').length;
                if (count) count.textContent = String(remaining);
            });
        })();
    </script>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
