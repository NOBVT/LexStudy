<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();
ensureLearningTables();

$currentUser = currentUser();
$initialState = getUserStudyState($currentUser ? (int)$currentUser['id'] : null);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);
$error = null;
$notice = null;
$virtualCase = $_SESSION['virtual_case'] ?? null;
$judgeChat = $_SESSION['judge_chat'] ?? null;
$evaluation = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        $error = 'Sessão expirada. Recarrega a página.';
    } else {
        $action = $_POST['tool_action'] ?? '';

        try {
            if ($action === 'upload_source') {
                $title = trim((string)($_POST['source_title'] ?? 'Código sem título'));
                $area = trim((string)($_POST['source_area'] ?? 'Geral'));
                $content = trim((string)($_POST['source_content'] ?? ''));

                if (isset($_FILES['source_file']) && ($_FILES['source_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                    $doc = extractUploadedDocumentText($_FILES['source_file']);
                    $content = trim((string)$doc['text']);
                    $title = $title !== '' ? $title : $doc['name'];
                }

                if (mb_strlen($content, 'UTF-8') < 100) {
                    throw new RuntimeException('Adiciona pelo menos algum texto legal para o simulador trabalhar.');
                }

                $_SESSION['legal_source'] = ['title' => $title, 'area' => $area, 'content' => $content];
                saveLegalSource($currentUser ? (int)$currentUser['id'] : null, $title, $area, $content);
                $notice = 'Texto legal carregado.';
            }

            if ($action === 'load_source') {
                $source = getLegalSourceContent($currentUser ? (int)$currentUser['id'] : null, (int)($_POST['source_id'] ?? 0));
                if (!$source) {
                    throw new RuntimeException('Fonte não encontrada.');
                }
                $_SESSION['legal_source'] = ['title' => $source['title'], 'area' => $source['area'], 'content' => $source['content']];
                $notice = 'Fonte carregada.';
            }

            if ($action === 'generate_case') {
                $source = $_SESSION['legal_source'] ?? null;
                $area = trim((string)($_POST['case_area'] ?? ($source['area'] ?? 'Geral')));
                $content = (string)($source['content'] ?? $_POST['source_content'] ?? '');
                if (mb_strlen($content, 'UTF-8') < 100) {
                    throw new RuntimeException('Carrega primeiro um texto legal.');
                }
                $virtualCase = generateVirtualCase($area, $content);
                $_SESSION['virtual_case'] = $virtualCase;
            }

            if ($action === 'start_judge_chat') {
                $source = $_SESSION['legal_source'] ?? null;
                $area = trim((string)($_POST['case_area'] ?? ($source['area'] ?? 'Geral')));
                $content = (string)($source['content'] ?? $_POST['source_content'] ?? '');
                if (mb_strlen($content, 'UTF-8') < 100) {
                    throw new RuntimeException('Carrega primeiro um texto legal para o juiz criar o caso.');
                }
                $judgeChat = startVirtualJudgeChat(
                    $currentUser ? (int)$currentUser['id'] : null,
                    $area,
                    $content,
                    (string)($source['title'] ?? 'Fonte local')
                );
                $virtualCase = $judgeChat['case'];
                $_SESSION['virtual_case'] = $virtualCase;
                $notice = 'Sessão socrática iniciada.';
            }

            if ($action === 'start_demo_chat') {
                $demoSource = str_repeat(
                    'Art. 203.º do Código Penal: quem, com ilegítima intenção de apropriação para si ou para outra pessoa, subtrair coisa móvel ou animal alheios é punido por furto. Art. 34.º do Código Penal: não é ilícito o facto praticado como meio adequado para afastar perigo atual que ameace interesses juridicamente protegidos, quando se verifiquem os respetivos requisitos. ',
                    3
                );
                $_SESSION['legal_source'] = [
                    'title' => 'Exemplo rápido: Furto e estado de necessidade',
                    'area' => 'Direito Penal',
                    'content' => $demoSource,
                ];
                $judgeChat = startVirtualJudgeChat(
                    $currentUser ? (int)$currentUser['id'] : null,
                    'Direito Penal',
                    $demoSource,
                    'Exemplo rápido'
                );
                $virtualCase = $judgeChat['case'];
                $_SESSION['virtual_case'] = $virtualCase;
                $notice = 'Exemplo rápido iniciado.';
            }

            if ($action === 'send_judge_message') {
                $judgeChat = $_SESSION['judge_chat'] ?? null;
                if (!$judgeChat) {
                    throw new RuntimeException('Inicia primeiro uma sessão com o Juiz Virtual.');
                }
                $judgeChat = continueVirtualJudgeChat(
                    $currentUser ? (int)$currentUser['id'] : null,
                    $judgeChat,
                    (string)($_POST['judge_answer'] ?? '')
                );
                $virtualCase = $judgeChat['case'];
                $_SESSION['virtual_case'] = $virtualCase;
            }

            if ($action === 'reset_judge_chat') {
                unset($_SESSION['judge_chat']);
                $judgeChat = null;
                $notice = 'Conversa reiniciada.';
            }

            if ($action === 'evaluate_answer') {
                $virtualCase = $_SESSION['virtual_case'] ?? null;
                $answer = trim((string)($_POST['student_answer'] ?? ''));
                if (!$virtualCase || mb_strlen($answer, 'UTF-8') < 50) {
                    throw new RuntimeException('Gera um caso e escreve uma resposta mais completa.');
                }
                $evaluation = evaluateVirtualAnswer($virtualCase, $answer);
                if ($currentUser) {
                    writeActivityLog((int)$currentUser['id'], 'quiz_done', 'Resposta avaliada pelo juiz virtual', 0);
                }
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$sources = getLegalSources($currentUser ? (int)$currentUser['id'] : null);
$loadedSource = $_SESSION['legal_source'] ?? null;
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Juiz Virtual - <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-simulator">
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
                <small><?= e($initialState['xp']) ?> XP</small>
            </div>
        </aside>
        <main class="workspace">
            <header class="topbar">
                <div>
                    <span class="eyebrow">Juiz Virtual</span>
                    <h1>Conversa com um juiz-professor e constrói a resposta passo a passo.</h1>
                </div>
                <span class="difficulty medium"><?= e(GEMINI_API_KEY === '' ? 'Modo local' : 'Inteligência ativa') ?></span>
            </header>

            <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
            <?php if ($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>

            <section class="study-flow" aria-label="Como usar o Juiz Virtual">
                <article>
                    <span>1</span>
                    <strong>Escolhe a fonte</strong>
                    <p>Usa uma fonte guardada ou cola texto legal.</p>
                </article>
                <article>
                    <span>2</span>
                    <strong>Inicia o juiz</strong>
                    <p>Ele cria o caso e faz a primeira pergunta.</p>
                </article>
                <article>
                    <span>3</span>
                    <strong>Responde por etapas</strong>
                    <p>Factos, artigos, raciocínio e conclusão.</p>
                </article>
            </section>

            <details class="section-block setup-drawer" <?= $loadedSource ? '' : 'open' ?>>
                <summary>
                    <span>
                        <span class="eyebrow">1. Fonte legal</span>
                        <strong><?= e($loadedSource ? 'Fonte carregada: ' . $loadedSource['title'] : 'Adicionar ou escolher fonte') ?></strong>
                    </span>
                    <small><?= e($loadedSource ? $loadedSource['area'] : 'Necessário para criar o caso') ?></small>
                </summary>

                <div class="two-column compact">
                <form class="tool-panel" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="tool_action" value="upload_source">
                    <span class="eyebrow">Adicionar fonte</span>
                    <h2>Cola texto legal</h2>
                    <label><span>Título</span><input type="text" name="source_title" value="<?= e($loadedSource['title'] ?? 'Código Penal Português') ?>"></label>
                    <label><span>Área</span><input type="text" name="source_area" value="<?= e($loadedSource['area'] ?? 'Direito Penal') ?>"></label>
                    <label class="file-drop"><span>PDF/TXT opcional</span><input type="file" name="source_file" accept="application/pdf,text/plain,.pdf,.txt"></label>
                    <label><span>Texto legal</span><textarea name="source_content" rows="8"><?= e($loadedSource['content'] ?? '') ?></textarea></label>
                    <button class="primary-btn" type="submit">Guardar fonte</button>
                </form>

                <div class="tool-panel">
                    <span class="eyebrow">Fontes guardadas</span>
                    <h2>Escolhe uma base</h2>
                    <?php if (!$sources): ?>
                        <p>Ainda não tens fontes guardadas. Cola texto legal ou envia um ficheiro.</p>
                    <?php endif; ?>
                    <?php foreach ($sources as $source): ?>
                        <form method="post" class="source-row">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="tool_action" value="load_source">
                            <input type="hidden" name="source_id" value="<?= e($source['id']) ?>">
                            <span><?= e($source['area']) ?></span>
                            <strong><?= e($source['title']) ?></strong>
                            <button class="secondary-btn" type="submit">Usar</button>
                        </form>
                    <?php endforeach; ?>
                </div>
                </div>
            </details>

            <section class="section-block judge-chat-layout">
                <article class="judge-chat-panel">
                    <div class="section-heading split">
                        <div>
                            <span class="eyebrow">2. Conversa principal</span>
                            <h2><?= e($judgeChat['case']['titulo'] ?? 'Inicia uma conversa com o Juiz Virtual') ?></h2>
                        </div>
                        <?php if ($judgeChat): ?>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                <input type="hidden" name="tool_action" value="reset_judge_chat">
                                <button class="secondary-btn" type="submit">Reiniciar</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if (!$judgeChat): ?>
                        <div class="empty-chat-state">
                            <strong>O juiz ainda não começou.</strong>
                            <p><?= e($loadedSource ? 'A fonte já está pronta. Clica em “Iniciar conversa” no painel ao lado.' : 'Carrega uma fonte legal ou usa “Experimentar exemplo” no painel ao lado.') ?></p>
                        </div>
                    <?php else: ?>
                        <div class="judge-case-summary">
                            <span><?= e($judgeChat['case']['area']) ?></span>
                            <p><?= e($judgeChat['case']['enunciado']) ?></p>
                        </div>
                        <div class="chat-thread" aria-label="Conversa com o Juiz Virtual">
                            <?php foreach ($judgeChat['messages'] as $message): ?>
                                <div class="chat-message <?= e($message['role'] === 'student' ? 'is-student' : 'is-judge') ?>">
                                    <strong><?= e($message['role'] === 'student' ? 'Tu' : 'Juiz Virtual') ?></strong>
                                    <p><?= nl2br(e($message['content'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <form method="post" class="chat-compose">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="tool_action" value="send_judge_message">
                            <label>
                                <span>A tua resposta ao juiz</span>
                                <textarea name="judge_answer" rows="5" placeholder="Responde à pergunta anterior. Usa factos, artigos e conclusão."></textarea>
                            </label>
                            <button class="primary-btn" type="submit">Responder</button>
                        </form>
                    <?php endif; ?>
                </article>

                <form method="post" class="tool-panel">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="tool_action" value="start_judge_chat">
                    <span class="eyebrow">Ação</span>
                    <h2>Iniciar conversa</h2>
                    <p>Cria um caso prático e começa uma sequência de perguntas guiadas.</p>
                    <label><span>Área do caso</span><input type="text" name="case_area" value="<?= e($loadedSource['area'] ?? 'Direito Penal') ?>"></label>
                    <button class="primary-btn" type="submit">Iniciar conversa</button>
                    <button class="secondary-btn" name="tool_action" value="start_demo_chat" type="submit">Experimentar exemplo</button>
                </form>
            </section>

            <details class="section-block setup-drawer">
                <summary>
                    <span>
                        <span class="eyebrow">Opcional</span>
                        <strong>Modo clássico sem chat</strong>
                    </span>
                    <small>Gerar caso e corrigir resposta diretamente</small>
                </summary>

                <form method="post" class="tool-panel">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="tool_action" value="generate_case">
                    <span class="eyebrow">Modo clássico</span>
                    <h2><?= e($virtualCase['titulo'] ?? 'Gerar caso sem chat') ?></h2>
                    <label><span>Área do caso</span><input type="text" name="case_area" value="<?= e($loadedSource['area'] ?? 'Direito Penal') ?>"></label>
                    <button class="secondary-btn" type="submit">Gerar caso prático clássico</button>
                </form>

                <?php if ($virtualCase): ?>
                    <div class="case-tool-grid">
                        <article class="case-workbench">
                            <span class="eyebrow"><?= e($virtualCase['area']) ?></span>
                            <h2><?= e($virtualCase['titulo']) ?></h2>
                            <p><?= e($virtualCase['enunciado']) ?></p>
                            <div class="refs">
                                <?php foreach ($virtualCase['artigos_relevantes'] as $article): ?><span><?= e($article) ?></span><?php endforeach; ?>
                            </div>
                        </article>
                        <article class="coach-output">
                            <strong>Armadilhas</strong>
                            <ul class="result-list">
                                <?php foreach ($virtualCase['armadilhas'] as $trap): ?><li><?= e($trap) ?></li><?php endforeach; ?>
                            </ul>
                        </article>
                    </div>

                    <form method="post" class="tool-panel">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="tool_action" value="evaluate_answer">
                        <label><span>A tua resposta</span><textarea name="student_answer" rows="10" placeholder="Qualifica os factos, indica normas, discute exceções e conclui."></textarea></label>
                        <button class="primary-btn" type="submit">Corrigir resposta</button>
                    </form>
                <?php endif; ?>
            </details>

            <?php if ($evaluation): ?>
                <section class="section-block">
                    <div class="section-heading">
                        <span class="eyebrow">Correção</span>
                        <h2>Nota: <?= e($evaluation['nota_20']) ?>/20</h2>
                    </div>
                    <div class="case-tool-grid">
                        <?php foreach (['pontos_fortes', 'falhas', 'artigos_em_falta'] as $key): ?>
                            <article class="coach-output">
                                <strong><?= e(str_replace('_', ' ', $key)) ?></strong>
                                <ul class="result-list"><?php foreach ((array)$evaluation[$key] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <article class="case-report">
                        <h3>Feedback</h3>
                        <p><?= e($evaluation['feedback']) ?></p>
                        <h3>Resposta modelo curta</h3>
                        <p><?= e($evaluation['resposta_modelo_curta']) ?></p>
                    </article>
                </section>
            <?php endif; ?>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
