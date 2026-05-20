<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$data = appData();
$currentUser = currentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;
$initialState = getUserStudyState($userId);
$studyProfile = getStudyProfile($userId);
$domain = getStudyDomainMap($userId, $initialState, $studyProfile);
$report = weeklyStudyReport($userId, $initialState, $studyProfile);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);

$modules = [
    [
        'name' => 'Sala',
        'meta' => 'Aulas',
        'description' => 'Matéria guiada, recursos e professor IA para estudar do zero.',
        'target' => 'sala.php',
        'module' => 'sala',
        'icon' => 'assets/icons/book.svg',
        'accent' => '#ffd48a',
    ],
    [
        'name' => 'Assistente',
        'meta' => 'IA jurídica',
        'description' => 'Perguntas, histórico e explicações focadas em Direito português.',
        'target' => 'assistant.php',
        'module' => 'assistant',
        'icon' => 'assets/icons/lawyer.svg',
        'accent' => '#c9a84c',
    ],
    [
        'name' => 'Modo Foco',
        'meta' => 'Sessão',
        'description' => 'Temporizador, tarefas e notas para estudar sem dispersão.',
        'target' => 'foco.php',
        'module' => 'trilho',
        'icon' => 'assets/icons/compass.svg',
        'accent' => '#9fd2ff',
    ],
    [
        'name' => 'Relatório',
        'meta' => 'Progresso',
        'description' => 'Resumo semanal, riscos reais e plano da próxima semana.',
        'target' => 'relatorio.php',
        'module' => 'relatorio',
        'icon' => 'assets/icons/document.svg',
        'accent' => '#a8d8ff',
    ],
    [
        'name' => 'Domínio',
        'meta' => 'Competências',
        'description' => 'Mapa de capacidades: conceitos, fontes, casos, memória e exame.',
        'target' => 'dominio.php',
        'module' => 'trilho',
        'icon' => 'assets/icons/compass.svg',
        'accent' => '#75d6cf',
    ],
    [
        'name' => 'Caderno',
        'meta' => 'Erros',
        'description' => 'Falhas abertas e revisão dirigida para não repetir o mesmo erro.',
        'target' => 'caderno.php',
        'module' => 'pecas',
        'icon' => 'assets/icons/document.svg',
        'accent' => '#f0a060',
    ],
    [
        'name' => 'Acórdãos',
        'meta' => 'Fontes',
        'description' => 'Parser e raio-X de decisões para estudar casos reais.',
        'target' => 'acordaos.php',
        'module' => 'acordaos',
        'icon' => 'assets/icons/scales.svg',
        'accent' => '#d4a8ff',
    ],
    [
        'name' => 'Flashcards',
        'meta' => 'Memória',
        'description' => 'Revisão ativa de conceitos, artigos e diferenças essenciais.',
        'target' => 'flashcards.php',
        'module' => 'flashcard',
        'icon' => 'assets/icons/flashcards.svg',
        'accent' => '#ffa8c5',
    ],
];

$seatItems = [
    ['label' => 'Painel', 'target' => 'index.php', 'icon' => 'assets/icons/parliament.svg'],
    ['label' => 'Sala', 'target' => 'sala.php', 'icon' => 'assets/icons/book.svg'],
    ['label' => 'Foco', 'target' => 'foco.php', 'icon' => 'assets/icons/compass.svg'],
    ['label' => 'IA', 'target' => 'assistant.php', 'icon' => 'assets/icons/lawyer.svg'],
    ['label' => 'Relatório', 'target' => 'relatorio.php', 'icon' => 'assets/icons/document.svg'],
    ['label' => 'Acórdãos', 'target' => 'acordaos.php', 'icon' => 'assets/icons/scales.svg'],
    ['label' => 'Memória', 'target' => 'flashcards.php', 'icon' => 'assets/icons/flashcards.svg'],
];

$summary = $report['summary'];
$weakest = $domain['weakest'] ?? null;
$userInitial = $currentUser ? mb_strtoupper(mb_substr((string)$currentUser['name'], 0, 1, 'UTF-8'), 'UTF-8') : 'L';
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lexium Preview - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Protótipo visual isolado da estrutura Lexium com new.css.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="new.css">
</head>
<body class="page-lexium-preview" style="--page-accent:#c9a84c;--page-icon:url('assets/icons/parliament.svg');">
    <div class="app-shell">
        <nav class="judicial-bench" aria-label="Navegação experimental Lexium">
            <a class="bench-brand" href="index.php">
                <span class="bench-seal">LX</span>
                <span class="bench-name">
                    <strong>Lexium</strong>
                    <small>Auditório judicial</small>
                </span>
            </a>

            <div class="bench-seats">
                <?php foreach ($seatItems as $item): ?>
                    <a class="bench-seat <?= $item['target'] === 'lexium-preview.php' ? 'is-active' : '' ?>" href="<?= e($item['target']) ?>" style="--seat-icon:url('<?= e($item['icon']) ?>');">
                        <?= e($item['label']) ?>
                    </a>
                <?php endforeach; ?>
                <a class="bench-seat is-active" href="lexium-preview.php" style="--seat-icon:url('assets/icons/parliament.svg');">
                    Preview
                </a>
            </div>

            <div class="bench-status">
                <span class="session-pill">Experiência</span>
                <span class="bench-avatar"><?= e($userInitial) ?></span>
            </div>
        </nav>

        <main class="workspace">
            <header class="court-header">
                <div class="court-header-copy">
                    <span class="eyebrow">Protótipo visual isolado</span>
                    <h1>O tribunal como interface de estudo.</h1>
                    <p>Esta página testa a estrutura do <strong>new.css</strong> sem substituir o tema atual. Se funcionar, pode virar a base do novo painel.</p>
                </div>
                <div class="court-header-actions">
                    <a class="btn-ghost" href="index.php">Voltar ao painel</a>
                    <a class="btn-primary" href="foco.php">Entrar em foco</a>
                </div>
            </header>

            <section class="stats-row" aria-label="Métricas reais">
                <article class="stat-card">
                    <span class="eyebrow">XP total</span>
                    <strong><?= e($initialState['xp']) ?></strong>
                    <small><?= e(getLevelTitle($level)) ?> · <?= e($progress['percent']) ?>% para o próximo nível</small>
                </article>
                <article class="stat-card">
                    <span class="eyebrow">Domínio</span>
                    <strong><?= e($domain['average']) ?>%</strong>
                    <small><?= e($domain['level']) ?></small>
                </article>
                <article class="stat-card">
                    <span class="eyebrow">Consistência</span>
                    <strong><?= e($report['activity_score']) ?>%</strong>
                    <small><?= e($report['range_label']) ?></small>
                </article>
                <article class="stat-card">
                    <span class="eyebrow">Erros abertos</span>
                    <strong><?= e($summary['mistakes_open']) ?></strong>
                    <small><?= e($summary['mistakes_resolved']) ?> resolvidos esta semana</small>
                </article>
            </section>

            <section class="command-row" aria-label="Comando principal">
                <article class="dossier featured">
                    <span class="eyebrow">Próxima ação</span>
                    <h2><?= e($weakest['name'] ?? 'Orientação') ?></h2>
                    <p><?= e($weakest['next'] ?? 'Concluir uma aula introdutória e rever o diagnóstico.') ?></p>
                    <a class="btn-secondary" href="<?= e($weakest['target'] ?? 'sala.php') ?>">Abrir recomendação</a>
                </article>

                <article class="dossier lined">
                    <span class="eyebrow">Leitura do sistema</span>
                    <h2>O produto começa a parecer uma sala de trabalho.</h2>
                    <p>Esta estrutura troca a dock inferior por uma bancada superior e organiza o estudo em processos, métricas e audiência. É uma direção mais institucional e menos “app de estudante”.</p>
                </article>

                <article class="dossier">
                    <span class="eyebrow">Estado</span>
                    <h2>Preview seguro</h2>
                    <p>O CSS novo está isolado nesta página. Nenhuma página existente é redesenhada por este teste.</p>
                    <a class="btn-ghost" href="new.css">Ver CSS</a>
                </article>
            </section>

            <section class="section-band">
                <div>
                    <span class="eyebrow">Módulos reais</span>
                    <h2>Ferramentas dentro da nova linguagem visual.</h2>
                </div>
                <a class="btn-secondary" href="relatorio.php">Ver relatório atual</a>
            </section>

            <section class="module-grid">
                <?php foreach ($modules as $module): ?>
                    <a class="module-card" href="<?= e($module['target']) ?>" data-module="<?= e($module['module']) ?>" style="--mod-icon:url('<?= e($module['icon']) ?>');--mod-accent:<?= e($module['accent']) ?>;">
                        <span class="eyebrow"><?= e($module['meta']) ?></span>
                        <strong><?= e($module['name']) ?></strong>
                        <p><?= e($module['description']) ?></p>
                    </a>
                <?php endforeach; ?>
            </section>

            <section class="audience-layout mt-0" style="margin-top:22px;">
                <article class="court-chat">
                    <div class="chat-header">
                        <h2>Assistente Jurídico · Demonstração</h2>
                        <span class="badge teal">Mockup</span>
                    </div>
                    <div class="chat-body">
                        <div class="chat-bubble assistant">
                            <strong>Assistente</strong>
                            <p>Escolhe uma área e eu preparo uma explicação curta, um caso prático e uma pergunta de revisão.</p>
                        </div>
                        <div class="chat-bubble user">
                            <strong>Aluno</strong>
                            <p>Quero começar Direito Civil sem ficar perdido na teoria.</p>
                        </div>
                        <div class="chat-bubble assistant">
                            <strong>Assistente</strong>
                            <p>Começa por obrigações: sujeito, objeto, vínculo e incumprimento. Depois resolve um caso simples e só no fim revê artigos.</p>
                        </div>
                    </div>
                    <div class="chat-compose">
                        <div class="compose-shell">
                            <textarea disabled>Esta área é visual. O chat real continua em assistant.php.</textarea>
                            <a class="send-btn" href="assistant.php" aria-label="Abrir assistente real">↗</a>
                        </div>
                        <div class="quick-prompts">
                            <button type="button">Explicar conceito</button>
                            <button type="button">Gerar caso</button>
                            <button type="button">Corrigir resposta</button>
                        </div>
                    </div>
                </article>

                <aside class="rebuttal-panel">
                    <span class="eyebrow">Relatório semanal</span>
                    <div class="report-command" style="grid-template-columns:1fr; margin-top:12px;">
                        <article class="report-score">
                            <span class="eyebrow">Consistência</span>
                            <strong><?= e($report['activity_score']) ?>%</strong>
                            <small><?= e($report['range_label']) ?></small>
                        </article>
                        <article class="dossier dossier-sm">
                            <div class="kpi-row"><span>Aulas</span><strong><?= e($summary['lessons']) ?></strong></div>
                            <div class="kpi-row"><span>IA</span><strong><?= e($summary['assistant_messages'] + $summary['teacher_questions']) ?></strong></div>
                            <div class="kpi-row"><span>Treino</span><strong><?= e($summary['mentor_sessions'] + $summary['cases'] + $summary['quiz']) ?></strong></div>
                        </article>
                    </div>

                    <div class="plan-grid" style="grid-template-columns:1fr; margin-top:14px;">
                        <?php foreach (array_slice($report['next_week'], 0, 3) as $step): ?>
                            <a class="plan-item" href="<?= e($step['target']) ?>">
                                <span class="eyebrow"><?= e($step['label']) ?></span>
                                <strong><?= e($step['title']) ?></strong>
                                <p><?= e($step['detail']) ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </aside>
            </section>
        </main>
    </div>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
