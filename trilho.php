<?php

require_once __DIR__ . '/config.php';

handleAuthRequest();

$currentUser = currentUser();
$initialState = getUserStudyState($currentUser ? (int)$currentUser['id'] : null);
$progress = getLevelProgress((int)$initialState['xp']);
$level = getLevel((int)$initialState['xp']);

$missions = [
    [
        'id' => 'pensar',
        'step' => '01',
        'title' => 'Aprender a pensar como jurista',
        'time' => '8 min',
        'promise' => 'Sais daqui a perceber a diferença entre opinião, facto e argumento jurídico.',
        'lesson' => 'Direito não é decorar frases bonitas. É pegar em factos, encontrar a regra aplicável, testar requisitos e concluir com rigor.',
        'moves' => ['Separar factos de emoções', 'Identificar o problema jurídico', 'Procurar a norma aplicável', 'Aplicar a norma aos factos', 'Concluir sem fugir ao problema'],
        'challenge' => 'Lê uma notícia curta e escreve: facto principal, conflito e possível consequência jurídica.',
    ],
    [
        'id' => 'mapa',
        'step' => '02',
        'title' => 'Mapa do curso de Direito',
        'time' => '10 min',
        'promise' => 'Percebes para que serve cada área antes de entrares em nomes difíceis.',
        'lesson' => 'Civil resolve relações entre pessoas. Penal responde a crimes. Constitucional explica o poder do Estado. Processo ensina como levar conflitos ao tribunal.',
        'moves' => ['Civil: contratos, família, responsabilidade', 'Penal: crime, culpa, pena', 'Constitucional: direitos fundamentais', 'Trabalho: relação trabalhador-empresa', 'Processo: caminho até à decisão'],
        'challenge' => 'Escolhe uma notícia e tenta dizer se parece Civil, Penal, Trabalho ou Constitucional.',
    ],
    [
        'id' => 'artigo',
        'step' => '03',
        'title' => 'Como ler um artigo de lei',
        'time' => '7 min',
        'promise' => 'Aprendes a desmontar artigos em hipótese, requisitos, exceção e consequência.',
        'lesson' => 'Um artigo quase sempre tem uma condição e uma consequência. A pergunta é: os factos preenchem todos os requisitos?',
        'moves' => ['Sublinhar a condição', 'Listar requisitos', 'Procurar exceções', 'Descobrir quem tem o ónus da prova', 'Escrever a consequência'],
        'challenge' => 'Pega num artigo simples e reescreve-o em linguagem normal: Se acontecer X, então acontece Y.',
    ],
    [
        'id' => 'caso',
        'step' => '04',
        'title' => 'Resolver o primeiro caso prático',
        'time' => '12 min',
        'promise' => 'Treinas a estrutura mínima para não ficares bloqueado num enunciado.',
        'lesson' => 'Um caso prático não se responde por intuição. Usa uma fórmula: problema, regra, aplicação e conclusão.',
        'moves' => ['Problema: qual é a pergunta?', 'Regra: que norma resolve?', 'Aplicação: que factos interessam?', 'Conclusão: quem tem razão e porquê?', 'Risco: que argumento contrário existe?'],
        'challenge' => 'Escreve uma resposta em 5 linhas usando exatamente esta ordem: problema, regra, aplicação, conclusão, risco.',
    ],
];

$terms = [
    ['term' => 'Facto', 'meaning' => 'Algo que aconteceu e pode ser provado.'],
    ['term' => 'Norma', 'meaning' => 'Regra jurídica que diz o que deve acontecer.'],
    ['term' => 'Ilicitude', 'meaning' => 'Contrariedade ao Direito.'],
    ['term' => 'Culpa', 'meaning' => 'Censura feita a quem podia agir de outra forma.'],
    ['term' => 'Nexo causal', 'meaning' => 'Ligação entre conduta e resultado.'],
    ['term' => 'Ónus da prova', 'meaning' => 'Quem tem de provar determinado facto.'],
];

$areas = [
    ['name' => 'Civil', 'use' => 'contratos, danos, família, propriedade', 'sample' => 'Alguém não cumpre um contrato.'],
    ['name' => 'Penal', 'use' => 'crimes, culpa, penas, defesa do arguido', 'sample' => 'Alguém furta um bem.'],
    ['name' => 'Constitucional', 'use' => 'direitos fundamentais e organização do poder', 'sample' => 'Uma lei limita uma liberdade.'],
    ['name' => 'Trabalho', 'use' => 'contrato laboral, despedimento, igualdade', 'sample' => 'Uma empresa despede um trabalhador.'],
];

$starterQuiz = [
    'question' => 'Num caso prático, o que deves fazer primeiro?',
    'options' => [
        'Começar logo pela conclusão.',
        'Separar os factos e perceber qual é o problema jurídico.',
        'Escrever todos os artigos que sabes.',
    ],
    'answer' => 1,
    'feedback' => 'Primeiro defines o problema. Sem problema jurídico claro, a resposta vira texto solto.',
];
?>
<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trilho Zero - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Trilho inicial para começar Direito com missões curtas, vocabulário essencial e mini-casos.">
    <link rel="icon" type="image/png" href="assets/parliament-logo.png">
    <link rel="apple-touch-icon" href="assets/parliament-logo.png">
    <meta name="theme-color" content="#071326">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="LexStudy">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="page-trail">
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
            <header class="topbar trail-hero">
                <div>
                    <span class="eyebrow">Trilho Zero</span>
                    <h1>Começa Direito sem te afogares em livros.</h1>
                    <p>Uma entrada curta, prática e guiada para quem ainda está a descobrir o curso.</p>
                </div>
                <div class="top-actions">
                    <a class="ghost-btn" href="conceitos.php">Pesquisar conceito</a>
                    <a class="primary-btn" href="#missao">Começar missão</a>
                </div>
            </header>

            <section class="trail-board" aria-label="Método inicial">
                <article class="trail-principle">
                    <span class="eyebrow">Regra do início</span>
                    <h2>Não estudes tudo. Estuda a próxima pergunta.</h2>
                    <p>Quem começa Direito costuma tentar ler demais. Aqui o método é diferente: micro-missão, exemplo, mini-caso, revisão.</p>
                    <div class="trail-loop">
                        <span>Missão</span>
                        <span>Exemplo</span>
                        <span>Caso</span>
                        <span>Revisão</span>
                    </div>
                </article>

                <article class="trail-quiz" data-quiz>
                    <span class="eyebrow">Pergunta de arranque</span>
                    <h2><?= e($starterQuiz['question']) ?></h2>
                    <div class="quiz-options trail-options">
                        <?php foreach ($starterQuiz['options'] as $index => $option): ?>
                            <button type="button" data-answer="<?= e($index) ?>"><?= e($option) ?></button>
                        <?php endforeach; ?>
                    </div>
                    <p data-quiz-feedback hidden><?= e($starterQuiz['feedback']) ?></p>
                </article>
            </section>

            <section id="missao" class="trail-layout">
                <aside class="trail-missions" aria-label="Missões iniciais">
                    <span class="eyebrow">Missões de 10 minutos</span>
                    <?php foreach ($missions as $index => $mission): ?>
                        <button class="<?= $index === 0 ? 'is-active' : '' ?>" type="button" data-mission="<?= e($mission['id']) ?>">
                            <span><?= e($mission['step']) ?> · <?= e($mission['time']) ?></span>
                            <strong><?= e($mission['title']) ?></strong>
                        </button>
                    <?php endforeach; ?>
                </aside>

                <article class="trail-stage">
                    <div class="section-heading split">
                        <div>
                            <span class="eyebrow" data-mission-step><?= e($missions[0]['step']) ?> · <?= e($missions[0]['time']) ?></span>
                            <h2 data-mission-title><?= e($missions[0]['title']) ?></h2>
                            <p data-mission-promise><?= e($missions[0]['promise']) ?></p>
                        </div>
                        <a class="secondary-btn" href="assistant.php">Perguntar ao assistente</a>
                    </div>
                    <div class="trail-lesson">
                        <strong>Aula relâmpago</strong>
                        <p data-mission-lesson><?= e($missions[0]['lesson']) ?></p>
                    </div>
                    <div class="trail-move-grid" data-mission-moves>
                        <?php foreach ($missions[0]['moves'] as $move): ?>
                            <span><?= e($move) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="trail-challenge">
                        <span class="eyebrow">Desafio prático</span>
                        <strong data-mission-challenge><?= e($missions[0]['challenge']) ?></strong>
                    </div>
                </article>
            </section>

            <section class="section-block">
                <div class="section-heading">
                    <span class="eyebrow">Vocabulário mínimo</span>
                    <h2>Se souberes estas palavras, já não entras às cegas.</h2>
                </div>
                <div class="trail-terms">
                    <?php foreach ($terms as $item): ?>
                        <article>
                            <strong><?= e($item['term']) ?></strong>
                            <p><?= e($item['meaning']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="section-block">
                <div class="section-heading">
                    <span class="eyebrow">Mapa rápido</span>
                    <h2>O curso começa a fazer sentido quando separas as áreas.</h2>
                </div>
                <div class="trail-areas">
                    <?php foreach ($areas as $area): ?>
                        <article>
                            <span><?= e($area['name']) ?></span>
                            <p><?= e($area['use']) ?></p>
                            <strong><?= e($area['sample']) ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>

    <script>
        window.LEXSTUDY_TRAIL = <?= jsData(['missions' => $missions, 'quiz' => $starterQuiz]) ?>;

        (() => {
            const missions = Object.fromEntries(window.LEXSTUDY_TRAIL.missions.map((mission) => [mission.id, mission]));
            const buttons = document.querySelectorAll('[data-mission]');
            const moves = document.querySelector('[data-mission-moves]');

            buttons.forEach((button) => {
                button.addEventListener('click', () => {
                    const mission = missions[button.dataset.mission];
                    if (!mission || !moves) return;

                    buttons.forEach((item) => item.classList.toggle('is-active', item === button));
                    document.querySelector('[data-mission-step]').textContent = `${mission.step} · ${mission.time}`;
                    document.querySelector('[data-mission-title]').textContent = mission.title;
                    document.querySelector('[data-mission-promise]').textContent = mission.promise;
                    document.querySelector('[data-mission-lesson]').textContent = mission.lesson;
                    document.querySelector('[data-mission-challenge]').textContent = mission.challenge;
                    moves.innerHTML = mission.moves.map((move) => `<span>${move}</span>`).join('');
                });
            });

            const feedback = document.querySelector('[data-quiz-feedback]');
            document.querySelectorAll('[data-answer]').forEach((button) => {
                button.addEventListener('click', () => {
                    const correct = Number(button.dataset.answer) === window.LEXSTUDY_TRAIL.quiz.answer;
                    document.querySelectorAll('[data-answer]').forEach((item) => {
                        item.classList.remove('correct', 'wrong');
                    });
                    button.classList.add(correct ? 'correct' : 'wrong');
                    if (feedback) {
                        feedback.hidden = false;
                        feedback.textContent = correct
                            ? window.LEXSTUDY_TRAIL.quiz.feedback
                            : 'Ainda não. Primeiro tens de descobrir o problema jurídico; depois escolhes regras e argumentos.';
                    }
                });
            });
        })();
    </script>
    <script src="assets/mobile.js" defer></script>
</body>
</html>
