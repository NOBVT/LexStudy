<?php

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function getLevel(int $xp): int
{
    $level = 1;
    foreach (LEVELS_XP as $i => $required) {
        if ($xp >= $required) {
            $level = $i + 1;
            continue;
        }
        break;
    }

    return min($level, count(LEVELS_XP));
}

function getLevelTitle(int $level): string
{
    return LEVELS_TITLE[min($level - 1, count(LEVELS_TITLE) - 1)] ?? 'Mestre';
}

function getLevelProgress(int $xp): array
{
    $currentLevelIndex = getLevel($xp) - 1;
    $currentXp = LEVELS_XP[$currentLevelIndex] ?? 0;
    $nextXp = LEVELS_XP[$currentLevelIndex + 1] ?? ($xp + 1);
    $required = max(1, $nextXp - $currentXp);
    $current = max(0, $xp - $currentXp);

    return [
        'current' => $current,
        'required' => $required,
        'percent' => min(100, round(($current / $required) * 100)),
    ];
}

function areaColor(string $areaKey): string
{
    return match ($areaKey) {
        'civil' => '#2f5f8f',
        'penal' => '#9b2f35',
        'trabalho' => '#2f7a5b',
        'constitucional' => '#5d4a8f',
        'consumidor' => '#a35c2f',
        'sucessoes' => '#8b6f2f',
        default => '#5f6874',
    };
}

function areaKeyFromArea(?string $area): string
{
    $area = mb_strtolower((string)$area, 'UTF-8');

    return match (true) {
        str_contains($area, 'civil') => 'civil',
        str_contains($area, 'penal') => 'penal',
        str_contains($area, 'trabalho') => 'trabalho',
        str_contains($area, 'constitucional') => 'constitucional',
        str_contains($area, 'consumidor') => 'consumidor',
        str_contains($area, 'sucess') => 'sucessoes',
        default => 'geral',
    };
}

function difficultyClass(string $difficulty): string
{
    return match ($difficulty) {
        'Fácil' => 'easy',
        'Difícil' => 'hard',
        default => 'medium',
    };
}

function appData(): array
{
    $payload = [
        'areas' => [
            [
                'key' => 'civil',
                'name' => 'Direito Civil',
                'focus' => 'Contratos, obrigações, responsabilidade, família e sucessões.',
                'progress' => 48,
                'modules' => ['Obrigações', 'Contratos', 'Responsabilidade civil', 'Arrendamento'],
            ],
            [
                'key' => 'penal',
                'name' => 'Direito Penal',
                'focus' => 'Tipicidade, ilicitude, culpa, penas e processo penal introdutório.',
                'progress' => 31,
                'modules' => ['Teoria do crime', 'Penas', 'Crimes patrimoniais', 'Defesa do arguido'],
            ],
            [
                'key' => 'trabalho',
                'name' => 'Direito do Trabalho',
                'focus' => 'Contrato laboral, despedimento, direitos do trabalhador e igualdade.',
                'progress' => 55,
                'modules' => ['Contrato', 'Despedimento', 'Tempo de trabalho', 'Discriminação'],
            ],
            [
                'key' => 'constitucional',
                'name' => 'Direito Constitucional',
                'focus' => 'Direitos fundamentais, organização do poder político e fiscalização.',
                'progress' => 27,
                'modules' => ['Direitos fundamentais', 'Órgãos de soberania', 'Fiscalização', 'Estado de emergência'],
            ],
        ],
        'methods' => [
            [
                'name' => 'Começar do Zero',
                'meta' => 'Entrada guiada',
                'description' => 'Aprende o essencial antes do curso: ler leis, perceber fontes e resolver o primeiro caso.',
                'target' => 'comecar.php',
            ],
            [
                'name' => 'Plano Semanal',
                'meta' => 'Agenda inteligente',
                'description' => 'Transforma horas disponíveis numa semana equilibrada de aulas, casos, revisão e memória ativa.',
                'target' => 'plano.php',
            ],
            [
                'name' => 'Disciplinas',
                'meta' => 'Biblioteca curricular',
                'description' => 'Organiza estudo por cadeira: aulas, casos, acórdãos, flashcards, falhas e próxima ação.',
                'target' => 'disciplinas.php',
            ],
            [
                'name' => 'Trilho Zero',
                'meta' => 'Começar Direito',
                'description' => 'Missões curtas para perceber o curso, aprender vocabulário base e treinar o primeiro caso.',
                'target' => 'trilho.php',
            ],
            [
                'name' => 'Sala de Estudo',
                'meta' => 'Aulas do zero',
                'description' => 'Percurso de aulas com matéria, recursos, livros, vídeos e Professor IA para explicar passo a passo.',
                'target' => 'sala.php',
            ],
            [
                'name' => 'Revisão do Dia',
                'meta' => 'Depois das aulas',
                'description' => 'Cola apontamentos ou envia PDF/TXT da aula e transforma o dia em resumo, perguntas, mini-caso e flashcards.',
                'target' => 'revisao.php',
            ],
            [
                'name' => 'Estúdio de Aula',
                'meta' => 'Importar matéria',
                'description' => 'Transforma apontamentos reais em resumo, perguntas, mini-caso, flashcards, biblioteca e revisão espaçada.',
                'target' => 'materia.php',
            ],
            [
                'name' => 'Mapa de Domínio',
                'meta' => 'Sistema de competências',
                'description' => 'Vê o teu progresso por competência jurídica: conceitos, fontes, casos, escrita, memória e exame.',
                'target' => 'dominio.php',
            ],
            [
                'name' => 'Relatório Semanal',
                'meta' => 'Plano e progresso',
                'description' => 'Resume a tua semana, mostra riscos reais e transforma atividade em plano de estudo concreto.',
                'target' => 'relatorio.php',
            ],
            [
                'name' => 'Modo Foco',
                'meta' => 'Sessão guiada',
                'description' => 'Temporizador, tarefas e apontamentos para estudar sem saltar entre ferramentas.',
                'target' => 'foco.php',
            ],
            [
                'name' => 'Mentor Jurídico',
                'meta' => 'Sessão guiada',
                'description' => 'Treina em modo professor particular: explicação curta, pergunta, correção e relatório final.',
                'target' => 'mentor.php',
            ],
            [
                'name' => 'Assistente jurídico',
                'meta' => 'Chat com histórico',
                'description' => 'Faz perguntas de Direito, revê conversas antigas e recebe respostas adaptadas ao teu estilo.',
                'target' => 'assistant.php',
            ],
            [
                'name' => 'Pesquisa jurídica',
                'meta' => 'Investigação guiada',
                'description' => 'Transforma um tema solto num roteiro de pesquisa com conceitos, fontes, jurisprudência e perguntas certas.',
                'target' => 'pesquisa.php',
            ],
            [
                'name' => 'Banca de Exame',
                'meta' => 'Simulação escrita',
                'description' => 'Cria um mini-exame, recebe a tua resposta e corrige com grelha, nota, falhas e plano de melhoria.',
                'target' => 'exame.php',
            ],
            [
                'name' => 'Laboratório de Teses',
                'meta' => 'Argumentos opostos',
                'description' => 'Constrói os dois lados de um problema jurídico: tese forte, contra-tese, riscos e perguntas de juiz.',
                'target' => 'teses.php',
            ],
            [
                'name' => 'Oficina de peças',
                'meta' => 'Escrita jurídica',
                'description' => 'Transforma factos em estruturas de petições, contestações, recursos, pareceres ou requerimentos.',
                'target' => 'pecas.php',
            ],
            [
                'name' => 'Dicionário jurídico',
                'meta' => 'Conceitos claros',
                'description' => 'Pesquisa conceitos jurídicos e recebe definição, requisitos, exemplo, erros comuns e pergunta de revisão.',
                'target' => 'conceitos.php',
            ],
            [
                'name' => 'Biblioteca jurídica',
                'meta' => 'Arquivo pessoal',
                'description' => 'Pesquisa acórdãos já analisados e recupera factos, normas, decisão e perguntas de exame.',
                'target' => 'library.php',
            ],
            [
                'name' => 'Parser de acórdãos',
                'meta' => 'Raio-X jurídico',
                'description' => 'Faz upload de PDF/TXT e extrai factos, questão jurídica, normas e decisão.',
                'target' => 'acordaos.php',
            ],
            [
                'name' => 'Juiz Virtual',
                'meta' => 'Exame prático',
                'description' => 'Gera casos com base num código e corrige a tua resposta com nota.',
                'target' => 'simulator.php',
            ],
            [
                'name' => 'Casos práticos',
                'meta' => 'Argumentação',
                'description' => 'Recebes factos, escolhes defesa ou acusação e treinas a construção da tese.',
                'target' => 'cases.php',
            ],
            [
                'name' => 'Flashcards',
                'meta' => 'Memória ativa',
                'description' => 'Perguntas curtas para decorar conceitos, artigos e diferenças essenciais.',
                'target' => 'flashcards.php',
            ],
            [
                'name' => 'Caderno de Erros',
                'meta' => 'Revisão inteligente',
                'description' => 'Transforma respostas fracas e cartas erradas numa lista clara do que tens de rever.',
                'target' => 'caderno.php',
            ],
        ],
        'cases' => [
            [
                'id' => 1,
                'title' => 'Caução Não Devolvida pelo Senhorio',
                'area' => 'Direito Civil',
                'area_key' => 'civil',
                'difficulty' => 'Fácil',
                'description' => 'Senhorio recusa devolver caução de 1.500 euros após fim do contrato de arrendamento, alegando danos contestados pelo inquilino.',
                'facts' => [
                    'Contrato de arrendamento por um ano em Lisboa.',
                    'Caução entregue: 1.500 euros.',
                    'O senhorio não fez vistoria documentada de entrada ou saída.',
                    'A inquilina tem fotografias datadas do início do contrato.',
                ],
                'legal_refs' => ['Código Civil: arrendamento', 'NRAU', 'Ónus da prova', 'Desgaste normal do imóvel'],
                'defense' => 'A defesa do inquilino deve atacar a falta de prova dos danos, separar desgaste normal de deterioração imputável e pedir restituição da caução.',
                'attack' => 'A posição do senhorio precisa demonstrar danos concretos, nexo causal com a conduta do inquilino e custo real de reparação.',
            ],
            [
                'id' => 2,
                'title' => 'Despedimento por Extinção do Posto',
                'area' => 'Direito do Trabalho',
                'area_key' => 'trabalho',
                'difficulty' => 'Médio',
                'description' => 'Trabalhadora despedida por reestruturação vê as mesmas funções atribuídas a trabalhador temporário dois meses depois.',
                'facts' => [
                    'Nove anos de antiguidade como gestora de projetos.',
                    'Empresa invoca extinção do posto por reestruturação.',
                    'Funções reaparecem com trabalhador temporário.',
                    'Não há prova clara de alternativas internas avaliadas.',
                ],
                'legal_refs' => ['Código do Trabalho', 'Extinção do posto', 'Critérios objetivos', 'Impugnação de despedimento'],
                'defense' => 'A defesa da trabalhadora deve mostrar que o posto não foi realmente extinto e que os requisitos formais e materiais falharam.',
                'attack' => 'A posição da empresa precisa provar motivo estrutural real, impossibilidade de subsistência do posto e cumprimento dos critérios legais.',
            ],
            [
                'id' => 3,
                'title' => 'Furto Qualificado em Residência',
                'area' => 'Direito Penal',
                'area_key' => 'penal',
                'difficulty' => 'Difícil',
                'description' => 'Arguido sem antecedentes é detido ao sair de residência com bens, confessando parcialmente e alegando desconhecer que era habitada.',
                'facts' => [
                    'Entrada por janela aberta.',
                    'Bens avaliados em 3.200 euros.',
                    'Sem antecedentes criminais.',
                    'Família dependente e arrependimento declarado.',
                ],
                'legal_refs' => ['Código Penal', 'Furto', 'Qualificação', 'Culpa', 'Suspensão da execução da pena'],
                'defense' => 'A defesa deve discutir a qualificação, explorar ausência de antecedentes, arrependimento, contexto social e eventual pena suspensa.',
                'attack' => 'A acusação deve reforçar o dolo, o valor dos bens, a violação do espaço habitacional e a proteção da propriedade privada.',
            ],
        ],
        'flashcards' => [
            [
                'front' => 'Quais são os pressupostos base da responsabilidade civil extracontratual?',
                'back' => 'Facto, ilicitude, culpa, dano e nexo de causalidade. Em estudo, não decore só a lista: aplique cada requisito aos factos.',
                'area' => 'Direito Civil',
                'difficulty' => 'Médio',
            ],
            [
                'front' => 'Qual é a diferença prática entre dolo eventual e negligência consciente?',
                'back' => 'No dolo eventual o agente conforma-se com o resultado possível. Na negligência consciente prevê o resultado, mas confia que ele não ocorre.',
                'area' => 'Direito Penal',
                'difficulty' => 'Médio',
            ],
            [
                'front' => 'Num despedimento por extinção do posto, que ponto costuma ser decisivo?',
                'back' => 'A existência real da extinção e o cumprimento dos critérios legais. Se as mesmas funções continuam, há matéria forte para impugnação.',
                'area' => 'Direito do Trabalho',
                'difficulty' => 'Médio',
            ],
            [
                'front' => 'Como se testa uma restrição a direitos fundamentais?',
                'back' => 'Pelo princípio da proporcionalidade: adequação, necessidade e proporcionalidade em sentido estrito.',
                'area' => 'Direito Constitucional',
                'difficulty' => 'Difícil',
            ],
        ],
        'quiz' => [
            [
                'question' => 'Num caso civil, o que liga juridicamente o facto ao dano?',
                'options' => ['Culpa', 'Nexo de causalidade', 'Mora', 'Legitimidade processual'],
                'answer' => 1,
                'explanation' => 'O nexo de causalidade é o elo entre o facto e o dano. Sem ele, mesmo existindo dano, a responsabilidade pode falhar.',
            ],
            [
                'question' => 'Numa defesa penal, a ausência de antecedentes é sobretudo relevante para quê?',
                'options' => ['Excluir sempre o crime', 'Determinar a medida da pena', 'Eliminar a ilicitude', 'Impedir a acusação'],
                'answer' => 1,
                'explanation' => 'A ausência de antecedentes não apaga o facto, mas pesa na medida concreta da pena e em soluções como pena suspensa.',
            ],
            [
                'question' => 'Se uma empresa extingue um posto mas mantém as mesmas funções, qual é o risco jurídico principal?',
                'options' => ['Contrato nulo', 'Despedimento ilícito', 'Caducidade automática', 'Renúncia salarial'],
                'answer' => 1,
                'explanation' => 'Se o posto não desapareceu de facto, a extinção pode ser apenas formal e o despedimento pode ser impugnável.',
            ],
        ],
        'dailyPlan' => [
            'Resolver um caso prático em 12 minutos.',
            'Rever 4 flashcards difíceis.',
            'Guardar um acórdão importante na biblioteca.',
            'Escrever uma tese jurídica em 5 linhas.',
        ],
        'xpRewards' => [
            'caseSolved' => XP_CASE_SOLVED,
            'coachFeedback' => XP_CASE_MSG,
            'quizCorrect' => XP_QUIZ_CORRECT,
            'cardReview' => XP_CARD_REVIEW,
        ],
        'levels' => LEVELS_XP,
        'levelTitles' => LEVELS_TITLE,
    ];

    return enrichAppDataFromDatabase($payload);
}

function enrichAppDataFromDatabase(array $payload): array
{
    if (!dbSchemaIsReady()) {
        return $payload;
    }

    $db = tryDB();
    if (!$db) {
        return $payload;
    }

    try {
        $cases = $db->query('SELECT id, title, area, area_key, difficulty, description, full_context, tags, legislation FROM cases WHERE active = 1 ORDER BY id ASC')->fetchAll();
        if ($cases) {
            $payload['cases'] = array_map(static function (array $case): array {
                $refs = array_values(array_unique(array_filter([
                    ...splitLegalList((string)($case['legislation'] ?? '')),
                    ...array_slice(splitLegalList((string)($case['tags'] ?? '')), 0, 3),
                ])));

                return [
                    'id' => (int)$case['id'],
                    'title' => $case['title'],
                    'area' => $case['area'],
                    'area_key' => $case['area_key'],
                    'difficulty' => $case['difficulty'],
                    'description' => $case['description'],
                    'facts' => extractFacts((string)$case['full_context']),
                    'legal_refs' => $refs ?: ['Identificar legislação aplicável', 'Fixar factos provados', 'Construir pedido'],
                    'defense' => 'A defesa deve atacar pressupostos, prova insuficiente, nexo causal e proporcionalidade da consequência jurídica.',
                    'attack' => 'A posição de ataque deve organizar factos provados, enquadramento legal, dano ou ilicitude e pedido final.',
                ];
            }, $cases);
        }

        ensureFlashcardPersonalColumns($db);
        $cards = $db->query(
            'SELECT flashcards.id, flashcards.front, flashcards.back, flashcards.difficulty, flashcard_decks.area
             FROM flashcards
             INNER JOIN flashcard_decks ON flashcard_decks.id = flashcards.deck_id
             WHERE flashcards.active = 1 AND flashcard_decks.active = 1 AND flashcard_decks.user_id IS NULL
             ORDER BY flashcard_decks.id ASC, flashcards.order_num ASC
             LIMIT 24'
        )->fetchAll();
        if ($cards) {
            $payload['flashcards'] = array_map(static fn(array $card): array => [
                'id' => (int)$card['id'],
                'front' => $card['front'],
                'back' => $card['back'],
                'area' => $card['area'],
                'difficulty' => $card['difficulty'],
            ], $cards);
        }

        $quiz = $db->query('SELECT id, question, option_a, option_b, option_c, option_d, correct_answer, explanation FROM quiz_questions WHERE active = 1 ORDER BY id ASC LIMIT 20')->fetchAll();
        if ($quiz) {
            $payload['quiz'] = array_map(static function (array $question): array {
                $answer = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3][strtoupper((string)$question['correct_answer'])] ?? 0;

                return [
                    'id' => (int)$question['id'],
                    'question' => $question['question'],
                    'options' => [$question['option_a'], $question['option_b'], $question['option_c'], $question['option_d']],
                    'answer' => $answer,
                    'explanation' => $question['explanation'],
                ];
            }, $quiz);
        }
    } catch (Throwable) {
        return $payload;
    }

    return $payload;
}

function splitLegalList(string $value): array
{
    return array_values(array_filter(array_map(
        static fn(string $item): string => trim($item),
        preg_split('/[,;]+/', $value) ?: []
    )));
}

function extractFacts(string $context): array
{
    $sentences = preg_split('/(?<=[.!?])\s+/u', trim($context)) ?: [];
    $facts = array_slice(array_filter(array_map(
        static fn(string $item): string => trim($item),
        $sentences
    )), 0, 4);

    return $facts ?: ['Lê o contexto completo.', 'Identifica os factos provados.', 'Separa factos de conclusões.', 'Escolhe a consequência jurídica.'];
}

function jsData(array $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
}

function findCaseById(int $id): ?array
{
    foreach (appData()['cases'] as $case) {
        if ((int)$case['id'] === $id) {
            return $case;
        }
    }

    return null;
}

function localCoachAnalysis(array $case, string $role, string $argument): string
{
    $words = preg_split('/\s+/', trim($argument), -1, PREG_SPLIT_NO_EMPTY);
    $wordCount = is_array($words) ? count($words) : 0;
    $lower = mb_strtolower($argument, 'UTF-8');
    $proofTerms = ['prova', 'document', 'testemun', 'fotografia', 'nexo', 'critério', 'dano'];
    $structureTerms = ['porque', 'logo', 'assim', 'por isso', 'requer', 'conclui'];
    $proofHits = 0;
    $structureHits = 0;

    foreach ($proofTerms as $term) {
        if (str_contains($lower, $term)) {
            $proofHits++;
        }
    }

    foreach ($structureTerms as $term) {
        if (str_contains($lower, $term)) {
            $structureHits++;
        }
    }

    $score = min(100, 32 + min($wordCount, 80) + $proofHits * 7 + $structureHits * 5);
    $level = $score >= 78 ? 'forte' : ($score >= 58 ? 'aproveitável' : 'fraca');
    $roleText = $role === 'attack' ? $case['attack'] : $case['defense'];
    $missing = [];

    if ($wordCount < 45) {
        $missing[] = 'desenvolver a tese com mais factos';
    }
    if ($proofHits < 2) {
        $missing[] = 'amarrar a argumentação a prova concreta';
    }
    if ($structureHits < 1) {
        $missing[] = 'fechar com pedido ou consequência jurídica';
    }

    $nextStep = $missing ? implode(', ', $missing) : 'antecipar a resposta da parte contrária';

    return '<strong>Análise: tese ' . e($level) . ' (' . e($score) . '/100)</strong>'
        . '<p>' . e($roleText) . '</p>'
        . '<p><b>Ponto vulnerável:</b> ' . e($nextStep) . '.</p>'
        . '<p><b>Contra-argumento provável:</b> a outra parte vai atacar a suficiência da prova e tentar deslocar a discussão para factos que ainda não demonstraste.</p>';
}

function callAnthropicAPI(array $messages, string $system = ''): ?string
{
    if (ANTHROPIC_API_KEY === '' || !function_exists('curl_init')) {
        return null;
    }

    $payload = [
        'model' => ANTHROPIC_MODEL,
        'max_tokens' => ANTHROPIC_MAX_TOKENS,
        'messages' => $messages,
    ];

    if ($system !== '') {
        $payload['system'] = $system;
    }

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . ANTHROPIC_API_KEY,
            'anthropic-version: 2023-06-01',
        ],
        CURLOPT_TIMEOUT => 45,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        return null;
    }

    $data = json_decode($response, true);
    return $data['content'][0]['text'] ?? null;
}

function ensureLearningTables(): void
{
    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        return;
    }

    $db->exec(
        "CREATE TABLE IF NOT EXISTS judgment_summaries (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            original_filename VARCHAR(255) NOT NULL,
            source_url VARCHAR(500) DEFAULT NULL,
            extracted_text LONGTEXT DEFAULT NULL,
            summary_json LONGTEXT NOT NULL,
            ai_mode VARCHAR(30) DEFAULT 'local',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS legal_sources (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            title VARCHAR(200) NOT NULL,
            area VARCHAR(100) DEFAULT 'Geral',
            content LONGTEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS virtual_judge_chats (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            source_title VARCHAR(200) DEFAULT NULL,
            area VARCHAR(100) DEFAULT 'Geral',
            case_json LONGTEXT NOT NULL,
            messages_json LONGTEXT NOT NULL,
            completed TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS legal_assistant_threads (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            title VARCHAR(180) NOT NULL DEFAULT 'Nova conversa',
            area VARCHAR(100) DEFAULT 'Geral',
            style_notes LONGTEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS legal_assistant_profiles (
            user_id INT UNSIGNED PRIMARY KEY,
            style_notes LONGTEXT DEFAULT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS legal_assistant_messages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            thread_id INT UNSIGNED NOT NULL,
            role VARCHAR(20) NOT NULL,
            content LONGTEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_thread_created (thread_id, created_at),
            FOREIGN KEY (thread_id) REFERENCES legal_assistant_threads(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS legal_drafts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            title VARCHAR(180) NOT NULL,
            piece_type VARCHAR(80) NOT NULL,
            area VARCHAR(100) DEFAULT 'Geral',
            goal VARCHAR(255) DEFAULT NULL,
            facts LONGTEXT NOT NULL,
            draft_json LONGTEXT NOT NULL,
            ai_mode VARCHAR(30) DEFAULT 'local',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS legal_concepts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            term VARCHAR(180) NOT NULL,
            area VARCHAR(100) DEFAULT 'Geral',
            concept_json LONGTEXT NOT NULL,
            ai_mode VARCHAR(30) DEFAULT 'local',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS legal_research_guides (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            topic VARCHAR(180) NOT NULL,
            area VARCHAR(100) DEFAULT 'Geral',
            objective VARCHAR(80) DEFAULT 'Estudo',
            guide_json LONGTEXT NOT NULL,
            ai_mode VARCHAR(30) DEFAULT 'local',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS legal_exam_sessions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            title VARCHAR(180) NOT NULL,
            area VARCHAR(100) DEFAULT 'Geral',
            difficulty VARCHAR(40) DEFAULT 'Médio',
            focus VARCHAR(180) DEFAULT NULL,
            exam_json LONGTEXT NOT NULL,
            answer LONGTEXT DEFAULT NULL,
            evaluation_json LONGTEXT DEFAULT NULL,
            score INT UNSIGNED DEFAULT NULL,
            ai_mode VARCHAR(30) DEFAULT 'local',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            evaluated_at DATETIME DEFAULT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS legal_thesis_labs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            topic VARCHAR(180) NOT NULL,
            area VARCHAR(100) DEFAULT 'Geral',
            facts LONGTEXT DEFAULT NULL,
            lab_json LONGTEXT NOT NULL,
            ai_mode VARCHAR(30) DEFAULT 'local',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS daily_study_reviews (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            class_date DATE DEFAULT NULL,
            discipline VARCHAR(120) DEFAULT 'Geral',
            topic VARCHAR(180) NOT NULL,
            raw_notes LONGTEXT NOT NULL,
            review_json LONGTEXT NOT NULL,
            ai_mode VARCHAR(30) DEFAULT 'local',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS daily_review_attempts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            review_id INT UNSIGNED NOT NULL,
            answer LONGTEXT NOT NULL,
            evaluation_json LONGTEXT NOT NULL,
            score INT UNSIGNED DEFAULT NULL,
            ai_mode VARCHAR(30) DEFAULT 'local',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_review_created (review_id, created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (review_id) REFERENCES daily_study_reviews(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS study_profiles (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            answers_json LONGTEXT NOT NULL,
            profile_json LONGTEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_study_profile_user (user_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS study_mistakes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            source_type VARCHAR(40) NOT NULL DEFAULT 'manual',
            source_id INT UNSIGNED DEFAULT NULL,
            area VARCHAR(100) DEFAULT 'Geral',
            title VARCHAR(180) NOT NULL,
            prompt LONGTEXT DEFAULT NULL,
            correction LONGTEXT NOT NULL,
            next_step VARCHAR(300) DEFAULT NULL,
            weight TINYINT UNSIGNED DEFAULT 2,
            status ENUM('open','resolved') DEFAULT 'open',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_status (user_id, status, created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS mentor_sessions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            area VARCHAR(100) DEFAULT 'Geral',
            title VARCHAR(180) NOT NULL,
            session_json LONGTEXT NOT NULL,
            score INT UNSIGNED DEFAULT 0,
            completed TINYINT(1) DEFAULT 0,
            ai_mode VARCHAR(30) DEFAULT 'local',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_completed (user_id, completed, created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS study_lesson_progress (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            lesson_id VARCHAR(120) NOT NULL,
            status ENUM('started','completed') DEFAULT 'completed',
            completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_user_lesson (user_id, lesson_id),
            INDEX idx_user_status (user_id, status, completed_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS user_subscriptions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            plan VARCHAR(30) NOT NULL DEFAULT 'free',
            status VARCHAR(40) NOT NULL DEFAULT 'inactive',
            stripe_customer_id VARCHAR(120) DEFAULT NULL,
            stripe_subscription_id VARCHAR(120) DEFAULT NULL,
            current_period_end DATETIME DEFAULT NULL,
            cancel_at_period_end TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_subscription_user (user_id),
            INDEX idx_stripe_subscription (stripe_subscription_id),
            INDEX idx_stripe_customer (stripe_customer_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS ai_usage_events (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            feature VARCHAR(60) NOT NULL,
            quantity INT UNSIGNED DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_feature_created (user_id, feature, created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    ensureFlashcardPersonalColumns($db);
}

function studyDiagnosticQuestions(): array
{
    return [
        [
            'key' => 'stage',
            'label' => 'Onde estás agora?',
            'options' => [
                'secundario' => 'Estou no secundário e quero perceber Direito',
                'inicio' => 'Vou começar ou comecei agora o curso',
                'exames' => 'Quero treinar para avaliações e exames',
            ],
        ],
        [
            'key' => 'goal',
            'label' => 'O que queres melhorar primeiro?',
            'options' => [
                'orientacao' => 'Saber por onde começar',
                'casos' => 'Resolver casos práticos',
                'memoria' => 'Memorizar conceitos e artigos',
                'escrita' => 'Escrever respostas mais jurídicas',
            ],
        ],
        [
            'key' => 'method',
            'label' => 'Como aprendes melhor?',
            'options' => [
                'guiado' => 'Passo a passo, com explicação curta',
                'pratico' => 'Com casos e problemas reais',
                'cartoes' => 'Com perguntas rápidas e repetição',
                'conversa' => 'A conversar com um assistente',
            ],
        ],
        [
            'key' => 'time',
            'label' => 'Quanto tempo tens por sessão?',
            'options' => [
                'curto' => '10 a 15 minutos',
                'medio' => '25 a 35 minutos',
                'longo' => '45 minutos ou mais',
            ],
        ],
        [
            'key' => 'area',
            'label' => 'Qual área te chama mais agora?',
            'options' => [
                'base' => 'Ainda não sei',
                'civil' => 'Direito Civil',
                'penal' => 'Direito Penal',
                'constitucional' => 'Direito Constitucional',
                'trabalho' => 'Direito do Trabalho',
            ],
        ],
        [
            'key' => 'confidence',
            'label' => 'Como te sentes com Direito hoje?',
            'options' => [
                'perdido' => 'Perdido, preciso de base',
                'curioso' => 'Curioso, mas ainda inseguro',
                'pronto' => 'Pronto para exercícios difíceis',
            ],
        ],
    ];
}

function normalizeDiagnosticAnswers(array $input): array
{
    $answers = [];

    foreach (studyDiagnosticQuestions() as $question) {
        $key = (string)$question['key'];
        $allowed = array_keys($question['options']);
        $value = is_string($input[$key] ?? null) ? trim($input[$key]) : '';
        $answers[$key] = in_array($value, $allowed, true) ? $value : $allowed[0];
    }

    return $answers;
}

function buildStudyProfile(array $answers): array
{
    $answers = normalizeDiagnosticAnswers($answers);
    $areaNames = [
        'base' => 'Fundamentos de Direito',
        'civil' => 'Direito Civil',
        'penal' => 'Direito Penal',
        'constitucional' => 'Direito Constitucional',
        'trabalho' => 'Direito do Trabalho',
    ];
    $timeNames = [
        'curto' => '12 minutos',
        'medio' => '30 minutos',
        'longo' => '50 minutos',
    ];

    $title = match ($answers['stage']) {
        'secundario' => 'Explorador jurídico',
        'exames' => 'Treino de exame',
        default => 'Arranque universitário',
    };

    if ($answers['confidence'] === 'perdido') {
        $title = 'Base guiada';
    }

    $focus = $areaNames[$answers['area']] ?? 'Fundamentos de Direito';
    $sessionTime = $timeNames[$answers['time']] ?? '30 minutos';
    $tone = match ($answers['method']) {
        'pratico' => 'casos primeiro',
        'cartoes' => 'memória ativa',
        'conversa' => 'mentor por conversa',
        default => 'passo a passo',
    };

    $mainTask = match ($answers['goal']) {
        'casos' => [
            'label' => 'Caso principal',
            'title' => 'Resolver um caso prático curto',
            'detail' => 'Escolhe defesa ou acusação, escreve uma tese em 8 linhas e pede feedback.',
            'target' => 'cases.php',
        ],
        'memoria' => [
            'label' => 'Memória',
            'title' => 'Rever conceitos essenciais',
            'detail' => 'Faz uma revisão curta e cria cartas a partir de um conceito que ainda falhe.',
            'target' => 'flashcards.php',
        ],
        'escrita' => [
            'label' => 'Escrita jurídica',
            'title' => 'Construir uma resposta estruturada',
            'detail' => 'Transforma factos em problema, regra, aplicação e conclusão.',
            'target' => 'pecas.php',
        ],
        default => [
            'label' => 'Orientação',
            'title' => 'Fazer uma missão do Trilho Zero',
            'detail' => 'Começa por separar factos, problema jurídico e consequência.',
            'target' => 'trilho.php#missao',
        ],
    };

    $areaTask = $answers['area'] === 'base'
        ? [
            'label' => 'Mapa',
            'title' => 'Perceber as áreas do curso',
            'detail' => 'Lê o mapa rápido e escolhe uma área para testar amanhã.',
            'target' => 'trilho.php',
        ]
        : [
            'label' => $focus,
            'title' => 'Pesquisar um conceito de ' . $focus,
            'detail' => 'Guarda o conceito e transforma-o em cartões se for difícil.',
            'target' => 'conceitos.php',
        ];

    $mentorQuestion = match ($answers['goal']) {
        'casos' => 'Dá-me um caso prático simples de ' . $focus . ' e corrige a minha resposta passo a passo.',
        'memoria' => 'Faz-me 5 perguntas rápidas sobre ' . $focus . ' e explica só o que eu errar.',
        'escrita' => 'Ajuda-me a escrever uma resposta jurídica usando problema, regra, aplicação e conclusão.',
        default => 'Explica-me por onde começar a estudar ' . $focus . ' sem me sobrecarregar.',
    };

    $plan = [
        [
            'label' => 'Depois da aula',
            'title' => 'Criar a revisão do dia',
            'detail' => 'Transforma os apontamentos de hoje em resumo, perguntas e treino ativo.',
            'target' => 'revisao.php',
        ],
        [
            'label' => 'Aquecimento',
            'title' => 'Ler uma explicação curta',
            'detail' => $answers['confidence'] === 'pronto'
                ? 'Revê só os requisitos e passa rapidamente ao exercício.'
                : 'Começa com uma explicação simples antes de tentares resolver.',
            'target' => 'trilho.php',
        ],
        $mainTask,
        $areaTask,
        [
            'label' => 'Mentor',
            'title' => 'Fazer uma pergunta ao Assistente',
            'detail' => $mentorQuestion,
            'target' => 'assistant.php',
        ],
    ];

    return [
        'title' => $title,
        'focus' => $focus,
        'tone' => $tone,
        'session_time' => $sessionTime,
        'summary' => 'Plano de ' . $sessionTime . ', focado em ' . $focus . ', com método de ' . $tone . '.',
        'mentor_question' => $mentorQuestion,
        'plan' => $plan,
        'answers' => $answers,
    ];
}

function getStudyProfile(?int $userId): ?array
{
    if ($userId && tryDB() instanceof PDO && dbSchemaIsReady()) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT profile_json FROM study_profiles WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $json = $stmt->fetchColumn();
        if (is_string($json) && $json !== '') {
            $profile = json_decode($json, true);
            return is_array($profile) ? $profile : null;
        }
    }

    $profile = $_SESSION['study_profile'] ?? null;
    return is_array($profile) ? $profile : null;
}

function saveStudyProfile(?int $userId, array $answers, array $profile): void
{
    if ($userId && tryDB() instanceof PDO && dbSchemaIsReady()) {
        ensureLearningTables();
        $stmt = getDB()->prepare(
            'INSERT INTO study_profiles (user_id, answers_json, profile_json) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE answers_json = VALUES(answers_json), profile_json = VALUES(profile_json), updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            $userId,
            json_encode($answers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    $_SESSION['study_profile'] = $profile;
}

function resetStudyProfile(?int $userId): void
{
    if ($userId && tryDB() instanceof PDO && dbSchemaIsReady()) {
        ensureLearningTables();
        $stmt = getDB()->prepare('DELETE FROM study_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
    }

    unset($_SESSION['study_profile']);
}

function recommendedDailyPlan(array $data, ?array $profile): array
{
    if (is_array($profile) && !empty($profile['plan']) && is_array($profile['plan'])) {
        return $profile['plan'];
    }

    return [
        [
            'label' => 'Depois da aula',
            'title' => 'Criar a revisão do dia',
            'detail' => 'Cola apontamentos ou envia PDF/TXT e transforma a matéria em resumo, perguntas e cartas.',
            'target' => 'revisao.php',
        ],
        [
            'label' => 'Começar',
            'title' => 'Entrar no Direito do zero',
            'detail' => 'Aprende como se lê uma norma, como se resolve um caso e que fontes usar.',
            'target' => 'comecar.php',
        ],
        [
            'label' => 'Semana',
            'title' => 'Montar um plano semanal',
            'detail' => 'Transforma o tempo disponível em aulas, casos, revisão e memória ativa.',
            'target' => 'plano.php',
        ],
        [
            'label' => 'Memória',
            'title' => 'Rever flashcards difíceis',
            'detail' => 'Fixa conceitos antes de avançares para matéria nova.',
            'target' => 'flashcards.php',
        ],
        [
            'label' => 'Assistente',
            'title' => 'Tirar uma dúvida específica',
            'detail' => 'Faz uma pergunta curta e pede um exemplo prático.',
            'target' => 'assistant.php',
        ],
    ];
}

function foundationBootcamp(): array
{
    return [
        'mapa' => [
            'title' => 'Mapa mental do curso',
            'tagline' => 'Antes de estudar artigos, percebe o jogo.',
            'promise' => 'Saber o que vais encontrar nas primeiras aulas e não confundir Direito com opinião.',
            'steps' => [
                'Direito é uma técnica para resolver conflitos com regras reconhecidas.',
                'A faculdade avalia raciocínio: factos, norma, aplicação e conclusão.',
                'Memorizar ajuda, mas só vale se souberes aplicar ao caso.',
            ],
            'vocabulary' => ['Norma jurídica', 'Facto', 'Fonte do Direito', 'Jurisprudência'],
            'mistakes' => ['Começar por decorar tudo', 'Responder com opinião moral', 'Ignorar os factos do enunciado'],
            'exercise' => 'Explica em quatro linhas a diferença entre uma regra moral e uma norma jurídica.',
            'target' => 'sala.php?aula=fundamentos-ordem-juridica',
        ],
        'norma' => [
            'title' => 'Como ler uma lei',
            'tagline' => 'A lei tem uma estrutura; lê-la sem método cansa.',
            'promise' => 'Aprender a separar hipótese, consequência e exceções.',
            'steps' => [
                'Procura o sujeito: quem está obrigado ou protegido?',
                'Procura a condição: quando é que a norma se aplica?',
                'Procura a consequência: o que acontece juridicamente?',
            ],
            'vocabulary' => ['Preceito', 'Estatuição', 'Requisito', 'Exceção'],
            'mistakes' => ['Ler só o título do artigo', 'Saltar requisitos', 'Não verificar exceções'],
            'exercise' => 'Escolhe um artigo curto de um código e sublinha sujeito, condição e consequência.',
            'target' => 'conceitos.php',
        ],
        'caso' => [
            'title' => 'Como resolver um caso prático',
            'tagline' => 'O exame não quer conversa bonita; quer método.',
            'promise' => 'Usar uma estrutura simples: problema, regra, aplicação e conclusão.',
            'steps' => [
                'Lista factos relevantes e ignora ruído.',
                'Formula a pergunta jurídica.',
                'Indica a regra e aplica requisito por requisito.',
                'Fecha com conclusão útil e consequência.',
            ],
            'vocabulary' => ['Questão jurídica', 'Subsunção', 'Ónus da prova', 'Conclusão'],
            'mistakes' => ['Responder sem conclusão', 'Citar regra sem aplicar aos factos', 'Tratar todos os factos como iguais'],
            'exercise' => 'Resolve este mini-caso: João prometeu vender o telemóvel a Ana, mas vendeu a Bruno no dia seguinte. Que perguntas jurídicas fazes?',
            'target' => 'cases.php',
        ],
        'fontes' => [
            'title' => 'Fontes e pesquisa',
            'tagline' => 'Saber onde procurar evita estudo perdido.',
            'promise' => 'Usar DRE, DGSI, códigos, manuais e apontamentos sem misturar pesos.',
            'steps' => [
                'Começa pela lei aplicável e confirma se está atualizada.',
                'Usa acórdãos para perceber aplicação prática.',
                'Usa doutrina para organizar argumentos e conceitos.',
            ],
            'vocabulary' => ['DRE', 'DGSI', 'Acórdão', 'Doutrina'],
            'mistakes' => ['Confiar em resumos soltos', 'Usar jurisprudência sem ler factos', 'Não confirmar atualização da lei'],
            'exercise' => 'Procura um acórdão no DGSI e identifica tribunal, data, questão e decisão.',
            'target' => 'acordaos.php',
        ],
    ];
}

function getFoundationModule(?string $moduleKey): array
{
    $bootcamp = foundationBootcamp();
    if ($moduleKey && isset($bootcamp[$moduleKey])) {
        return ['key' => $moduleKey, ...$bootcamp[$moduleKey]];
    }

    $key = array_key_first($bootcamp);
    return ['key' => $key, ...$bootcamp[$key]];
}

function askFoundationProfessor(?int $userId, array $module, string $question): array
{
    assertAiQuota($userId, 'teacher_question');
    $question = trim($question);
    if (mb_strlen($question, 'UTF-8') < 5) {
        throw new RuntimeException('Escreve uma dúvida concreta para o professor.');
    }

    $prompt = "Ajuda um aluno que ainda vai começar Direito em Portugal. Usa este módulo como contexto e responde de forma simples, sem perder rigor. Devolve apenas JSON válido com: answer, example, next_step, warning.\n\nMódulo:\n" . json_encode($module, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\nPergunta:\n" . $question;
    $decoded = decodeAiJson(callGeminiText($prompt, null, 'És um professor de Introdução ao Direito em Portugal. Não dês aconselhamento jurídico profissional; ensina método de estudo e raciocínio jurídico.'));
    recordAiUsage($userId, 'teacher_question');

    if ($decoded) {
        return [
            'answer' => trim((string)($decoded['answer'] ?? '')) ?: 'A resposta veio incompleta.',
            'example' => trim((string)($decoded['example'] ?? '')),
            'next_step' => trim((string)($decoded['next_step'] ?? 'Faz o exercício do módulo e compara com os passos.')),
            'warning' => trim((string)($decoded['warning'] ?? 'Confirma sempre com a lei e materiais da faculdade.')),
            'ai_mode' => 'gemini',
        ];
    }

    return [
        'answer' => 'Começa pelo método do módulo: ' . implode(' ', array_slice((array)$module['steps'], 0, 2)) . ' A tua dúvida deve ser tratada separando conceito, exemplo e aplicação.',
        'example' => 'Exemplo simples: pega numa regra do dia a dia, identifica quem ela vincula, quando se aplica e qual é a consequência.',
        'next_step' => $module['exercise'] ?? 'Faz um exercício curto e escreve a conclusão.',
        'warning' => 'Resposta local. Com IA ativa, a explicação fica mais adaptada à tua pergunta.',
        'ai_mode' => 'local',
    ];
}

function normalizeWeeklyPlanInput(array $input, ?array $profile): array
{
    $profileAnswers = is_array($profile) ? (array)($profile['answers'] ?? []) : [];
    $hours = max(2, min(20, (int)($input['hours'] ?? 5)));
    $days = max(2, min(7, (int)($input['days'] ?? 4)));
    $goal = (string)($input['goal'] ?? ($profileAnswers['goal'] ?? 'orientacao'));
    $area = (string)($input['area'] ?? ($profileAnswers['area'] ?? 'base'));
    $rhythm = (string)($input['rhythm'] ?? 'equilibrado');

    $allowedGoals = ['orientacao', 'casos', 'memoria', 'escrita', 'exames'];
    $allowedAreas = ['base', 'civil', 'penal', 'constitucional', 'trabalho'];
    $allowedRhythms = ['leve', 'equilibrado', 'intenso'];

    return [
        'hours' => $hours,
        'days' => $days,
        'goal' => in_array($goal, $allowedGoals, true) ? $goal : 'orientacao',
        'area' => in_array($area, $allowedAreas, true) ? $area : 'base',
        'rhythm' => in_array($rhythm, $allowedRhythms, true) ? $rhythm : 'equilibrado',
    ];
}

function buildSmartWeeklyPlan(?int $userId, array $input, ?array $profile, array $domain): array
{
    $input = normalizeWeeklyPlanInput($input, $profile);
    $areaNames = [
        'base' => 'Fundamentos de Direito',
        'civil' => 'Direito Civil',
        'penal' => 'Direito Penal',
        'constitucional' => 'Direito Constitucional',
        'trabalho' => 'Direito do Trabalho',
    ];
    $goalNames = [
        'orientacao' => 'orientação e método',
        'casos' => 'casos práticos',
        'memoria' => 'memória ativa',
        'escrita' => 'escrita jurídica',
        'exames' => 'treino de exame',
    ];

    $duration = max(25, (int)floor(($input['hours'] * 60) / $input['days']));
    $weekDays = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
    $weakest = $domain['weakest']['name'] ?? 'Orientação';
    $area = $areaNames[$input['area']];
    $goal = $goalNames[$input['goal']];

    $taskBank = [
        'orientacao' => [
            ['title' => 'Começar do Zero', 'target' => 'comecar.php', 'output' => '1 mapa mental em 5 linhas', 'kind' => 'Base'],
            ['title' => 'Aula guiada', 'target' => 'sala.php', 'output' => '1 aula marcada como concluída', 'kind' => 'Aula'],
            ['title' => 'Pergunta ao Assistente', 'target' => 'assistant.php', 'output' => '1 dúvida transformada em exemplo', 'kind' => 'IA'],
        ],
        'casos' => [
            ['title' => 'Caso prático curto', 'target' => 'cases.php', 'output' => 'tese em 8 linhas', 'kind' => 'Caso'],
            ['title' => 'Juiz Virtual', 'target' => 'simulator.php', 'output' => 'correção com nota', 'kind' => 'Exame'],
            ['title' => 'Caderno de Erros', 'target' => 'caderno.php', 'output' => '1 falha registada', 'kind' => 'Correção'],
        ],
        'memoria' => [
            ['title' => 'Flashcards', 'target' => 'flashcards.php', 'output' => '12 cartões revistos', 'kind' => 'Memória'],
            ['title' => 'Dicionário jurídico', 'target' => 'conceitos.php', 'output' => '2 conceitos clarificados', 'kind' => 'Conceitos'],
            ['title' => 'Revisão do Dia', 'target' => 'revisao.php', 'output' => 'cartões novos a partir da aula', 'kind' => 'Revisão'],
        ],
        'escrita' => [
            ['title' => 'Oficina de peças', 'target' => 'pecas.php', 'output' => 'estrutura de resposta', 'kind' => 'Escrita'],
            ['title' => 'Laboratório de Teses', 'target' => 'teses.php', 'output' => 'tese e contra-tese', 'kind' => 'Argumento'],
            ['title' => 'Banca de Exame', 'target' => 'exame.php', 'output' => 'resposta corrigida', 'kind' => 'Exame'],
        ],
        'exames' => [
            ['title' => 'Banca de Exame', 'target' => 'exame.php', 'output' => 'mini-exame com grelha', 'kind' => 'Exame'],
            ['title' => 'Juiz Virtual', 'target' => 'simulator.php', 'output' => 'caso corrigido', 'kind' => 'Caso'],
            ['title' => 'Relatório Semanal', 'target' => 'relatorio.php', 'output' => 'pontos fracos revistos', 'kind' => 'Relatório'],
        ],
    ];

    $baseTasks = $taskBank[$input['goal']];
    $supportTasks = [
        ['title' => 'Revisão do Dia', 'target' => 'revisao.php', 'output' => 'resumo + perguntas', 'kind' => 'Revisão'],
        ['title' => 'Modo Foco', 'target' => 'foco.php', 'output' => 'sessão sem distrações', 'kind' => 'Foco'],
        ['title' => 'Mapa de Domínio', 'target' => 'dominio.php', 'output' => 'fraqueza atual confirmada', 'kind' => 'Diagnóstico'],
    ];

    $days = [];
    for ($i = 0; $i < $input['days']; $i++) {
        $source = $i % 3 === 2 ? $supportTasks : $baseTasks;
        $task = $source[$i % count($source)];
        $days[] = [
            'day' => $weekDays[$i],
            'duration' => $duration,
            'title' => $task['title'],
            'kind' => $task['kind'],
            'target' => $task['target'],
            'output' => $task['output'],
            'reason' => $i === 0
                ? 'Começa pela base para reduzir confusão.'
                : 'Mantém alternância entre aprendizagem, aplicação e revisão.',
        ];
    }

    $reviewMinutes = max(10, min(25, (int)round($duration * .35)));
    return [
        'input' => $input,
        'title' => 'Semana de ' . $goal . ' em ' . $area,
        'summary' => $input['hours'] . 'h distribuídas por ' . $input['days'] . ' dias. Ponto fraco a observar: ' . $weakest . '.',
        'area' => $area,
        'goal' => $goal,
        'weakest' => $weakest,
        'duration' => $duration,
        'review_minutes' => $reviewMinutes,
        'days' => $days,
        'rules' => [
            'Antes de cada sessão, escreve o objetivo numa frase.',
            'Durante a sessão, produz sempre algo: resposta, cartão, resumo ou pergunta.',
            'No fim, regista uma falha no Caderno de Erros se ainda houver confusão.',
        ],
    ];
}

function weeklyPlanCoach(?int $userId, array $plan): array
{
    assertAiQuota($userId, 'teacher_question');
    $prompt = "Revê este plano semanal de estudo jurídico para um aluno iniciante. Devolve apenas JSON válido com: coach_note, risks, adjustment, first_action.\n\nPlano:\n" . json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $decoded = decodeAiJson(callGeminiText($prompt, null, 'És um professor de metodologia de estudo jurídico em Portugal. Sê direto, prático e realista.'));
    recordAiUsage($userId, 'teacher_question');

    if ($decoded) {
        return [
            'coach_note' => trim((string)($decoded['coach_note'] ?? 'Plano equilibrado.')),
            'risks' => array_values(array_filter(array_map('strval', (array)($decoded['risks'] ?? [])))) ?: ['Evita acumular sessões longas sem revisão.'],
            'adjustment' => trim((string)($decoded['adjustment'] ?? 'Mantém revisão curta no fim de cada sessão.')),
            'first_action' => trim((string)($decoded['first_action'] ?? 'Começa pela primeira sessão do plano.')),
            'ai_mode' => 'gemini',
        ];
    }

    return [
        'coach_note' => 'Plano utilizável. O ponto principal é não deixar sessões sem produto final.',
        'risks' => ['Tentar estudar tudo no mesmo dia.', 'Fazer leitura passiva sem exercício.'],
        'adjustment' => 'Reduz matéria nova e aumenta revisão se falhares dois exercícios seguidos.',
        'first_action' => 'Abre a primeira sessão e escreve o objetivo em uma frase.',
        'ai_mode' => 'local',
    ];
}

function disciplineBlueprints(): array
{
    return [
        'base' => [
            'name' => 'Introdução ao Direito',
            'short' => 'Base',
            'accent' => '#e8c86a',
            'description' => 'Norma jurídica, fontes, interpretação, método e primeira leitura de casos.',
            'lesson_modules' => ['fundamentos'],
            'concepts' => ['Norma jurídica', 'Fontes do Direito', 'Hierarquia normativa', 'Interpretação jurídica'],
            'starter' => 'Começa por distinguir factos, normas e opinião.',
        ],
        'constitucional' => [
            'name' => 'Direito Constitucional',
            'short' => 'Constitucional',
            'accent' => '#c7b8ff',
            'description' => 'Constituição, direitos fundamentais, órgãos de soberania e fiscalização.',
            'lesson_modules' => ['constitucional'],
            'concepts' => ['Direitos fundamentais', 'Proporcionalidade', 'Fiscalização da constitucionalidade', 'Separação de poderes'],
            'starter' => 'Treina sempre proporcionalidade: adequação, necessidade e equilíbrio.',
        ],
        'civil' => [
            'name' => 'Direito Civil',
            'short' => 'Civil',
            'accent' => '#9fd2ff',
            'description' => 'Obrigações, contratos, responsabilidade civil e relações privadas.',
            'lesson_modules' => ['civil'],
            'concepts' => ['Contrato', 'Responsabilidade civil', 'Incumprimento', 'Nexo de causalidade'],
            'starter' => 'Não avances sem separar facto, dano, culpa e nexo.',
        ],
        'penal' => [
            'name' => 'Direito Penal',
            'short' => 'Penal',
            'accent' => '#ffb4a8',
            'description' => 'Tipicidade, ilicitude, culpa, penas e raciocínio de crime.',
            'lesson_modules' => ['penal'],
            'concepts' => ['Tipicidade', 'Ilicitude', 'Culpa', 'Dolo e negligência'],
            'starter' => 'Resolve por camadas: tipo, ilicitude, culpa e consequência.',
        ],
        'trabalho' => [
            'name' => 'Direito do Trabalho',
            'short' => 'Trabalho',
            'accent' => '#75d6cf',
            'description' => 'Contrato de trabalho, subordinação, despedimento e proteção laboral.',
            'lesson_modules' => ['trabalho'],
            'concepts' => ['Contrato de trabalho', 'Subordinação', 'Despedimento ilícito', 'Tempo de trabalho'],
            'starter' => 'A pergunta base é se há subordinação e se o procedimento foi cumprido.',
        ],
    ];
}

function normalizeDisciplineKey(?string $key): string
{
    $key = (string)$key;
    return array_key_exists($key, disciplineBlueprints()) ? $key : 'base';
}

function disciplineKeyFromLabel(string $value): string
{
    $lower = mb_strtolower($value, 'UTF-8');
    return match (true) {
        str_contains($lower, 'constitucional') => 'constitucional',
        str_contains($lower, 'civil') => 'civil',
        str_contains($lower, 'penal') => 'penal',
        str_contains($lower, 'trabalho') || str_contains($lower, 'laboral') => 'trabalho',
        str_contains($lower, 'introdu') || str_contains($lower, 'fundamento') || str_contains($lower, 'geral') => 'base',
        default => areaKeyFromArea($value) === 'geral' ? 'base' : areaKeyFromArea($value),
    };
}

function countUserRowsForDiscipline(?int $userId, string $table, string $column, string $disciplineKey, string $where = ''): int
{
    if (!$userId || !(tryDB() instanceof PDO) || !dbSchemaIsReady()) {
        return 0;
    }

    $blueprint = disciplineBlueprints()[$disciplineKey] ?? disciplineBlueprints()['base'];
    $needles = array_values(array_unique(array_filter([$blueprint['name'], $blueprint['short'], $disciplineKey === 'base' ? 'Geral' : ''])));

    try {
        ensureLearningTables();
        $parts = [];
        $params = [$userId];
        foreach ($needles as $needle) {
            $parts[] = $column . ' LIKE ?';
            $params[] = '%' . $needle . '%';
        }
        $sql = 'SELECT COUNT(*) FROM ' . $table . ' WHERE user_id = ? AND (' . implode(' OR ', $parts) . ')';
        if ($where !== '') {
            $sql .= ' AND ' . $where;
        }
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    } catch (Throwable) {
        return 0;
    }
}

function completedLessonsForDiscipline(?int $userId, string $disciplineKey): int
{
    if (!$userId || !(tryDB() instanceof PDO) || !dbSchemaIsReady()) {
        return 0;
    }

    $moduleKeys = disciplineBlueprints()[$disciplineKey]['lesson_modules'] ?? [];
    $lessonIds = [];
    foreach (studyRoomCurriculum() as $moduleKey => $module) {
        if (!in_array($moduleKey, $moduleKeys, true)) {
            continue;
        }
        foreach ($module['lessons'] as $lesson) {
            $lessonIds[] = $lesson['id'];
        }
    }

    if (!$lessonIds) {
        return 0;
    }

    try {
        $placeholders = implode(',', array_fill(0, count($lessonIds), '?'));
        $stmt = getDB()->prepare("SELECT COUNT(*) FROM study_lesson_progress WHERE user_id = ? AND status = 'completed' AND lesson_id IN ({$placeholders})");
        $stmt->execute([$userId, ...$lessonIds]);
        return (int)$stmt->fetchColumn();
    } catch (Throwable) {
        return 0;
    }
}

function totalLessonsForDiscipline(string $disciplineKey): int
{
    $moduleKeys = disciplineBlueprints()[$disciplineKey]['lesson_modules'] ?? [];
    $total = 0;
    foreach (studyRoomCurriculum() as $moduleKey => $module) {
        if (in_array($moduleKey, $moduleKeys, true)) {
            $total += count($module['lessons']);
        }
    }

    return $total;
}

function flashcardDecksForDiscipline(?int $userId, string $disciplineKey): array
{
    return array_values(array_filter(
        getFlashcardDecks($userId),
        static fn(array $deck): bool => disciplineKeyFromLabel((string)($deck['area_key'] ?? $deck['area'] ?? '')) === $disciplineKey
            || ($disciplineKey === 'base' && in_array((string)($deck['area_key'] ?? ''), ['', 'geral'], true))
    ));
}

function disciplineLibrary(?int $userId, array $state, ?array $profile): array
{
    $domain = getStudyDomainMap($userId, $state, $profile);
    $domainItems = [];
    foreach ($domain['items'] as $item) {
        $domainItems[$item['key']] = $item;
    }

    $mistakes = getStudyMistakes($userId, 80, 'open');
    $cards = getFlashcardDecks($userId);
    $result = [];

    foreach (disciplineBlueprints() as $key => $blueprint) {
        $completedLessons = completedLessonsForDiscipline($userId, $key);
        $totalLessons = totalLessonsForDiscipline($key);
        $concepts = countUserRowsForDiscipline($userId, 'legal_concepts', 'area', $key);
        $sources = countUserRowsForDiscipline($userId, 'legal_sources', 'area', $key);
        $drafts = countUserRowsForDiscipline($userId, 'legal_drafts', 'area', $key);
        $reviews = countUserRowsForDiscipline($userId, 'daily_study_reviews', 'discipline', $key);
        $exams = countUserRowsForDiscipline($userId, 'legal_exam_sessions', 'area', $key);
        $cases = countUserRowsForDiscipline($userId, 'case_sessions', 'area', $key, 'completed = 1');
        $deckCount = count(array_filter(
            $cards,
            static fn(array $deck): bool => disciplineKeyFromLabel((string)($deck['area_key'] ?? $deck['area'] ?? '')) === $key
        ));
        $mistakeItems = array_values(array_filter(
            $mistakes,
            static fn(array $mistake): bool => disciplineKeyFromLabel((string)($mistake['area'] ?? '')) === $key
        ));
        $lessonScore = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 35 : 0;
        $artifactScore = min(35, ($concepts + $sources + $drafts + $reviews + $exams + $cases + $deckCount) * 5);
        $domainScore = (($domainItems['concepts']['score'] ?? 0) + ($domainItems['cases']['score'] ?? 0) + ($domainItems['memory']['score'] ?? 0)) / 12;
        $penalty = min(18, count($mistakeItems) * 4);
        $score = clampScore(15 + $lessonScore + $artifactScore + $domainScore - $penalty);

        $result[$key] = [
            ...$blueprint,
            'key' => $key,
            'score' => $score,
            'level' => domainLevelFromScore($score),
            'completed_lessons' => $completedLessons,
            'total_lessons' => $totalLessons,
            'concepts_count' => $concepts,
            'sources_count' => $sources,
            'drafts_count' => $drafts,
            'reviews_count' => $reviews,
            'exams_count' => $exams,
            'cases_count' => $cases,
            'decks_count' => $deckCount,
            'mistakes' => array_slice($mistakeItems, 0, 4),
            'next_action' => disciplineNextAction($key, $score, count($mistakeItems), $completedLessons, $totalLessons),
        ];
    }

    return $result;
}

function disciplineNextAction(string $disciplineKey, int $score, int $mistakeCount, int $completedLessons, int $totalLessons): array
{
    if ($completedLessons < $totalLessons) {
        return [
            'label' => 'Aula em falta',
            'title' => 'Concluir próxima aula da disciplina',
            'reason' => 'Sem base mínima, casos e acórdãos ficam mais lentos e confusos.',
            'target' => 'sala.php',
        ];
    }
    if ($mistakeCount > 0) {
        return [
            'label' => 'Fraqueza aberta',
            'title' => 'Rever erros desta disciplina',
            'reason' => 'Erros registados são melhor matéria de estudo do que leitura genérica.',
            'target' => 'caderno.php',
        ];
    }
    if ($score < 50) {
        return [
            'label' => 'Fundação',
            'title' => 'Criar conceitos e flashcards',
            'reason' => 'A disciplina ainda precisa de vocabulário técnico antes de treino pesado.',
            'target' => 'conceitos.php',
        ];
    }
    if ($score < 75) {
        return [
            'label' => 'Aplicação',
            'title' => 'Resolver caso prático',
            'reason' => 'Já há base suficiente para testar raciocínio jurídico em factos concretos.',
            'target' => 'cases.php',
        ];
    }

    return [
        'label' => 'Consolidação',
        'title' => 'Fazer mini-exame',
        'reason' => 'Quando a base está estável, a melhor evolução vem de resposta escrita avaliada.',
        'target' => 'exame.php',
    ];
}

function disciplineStudyPlan(array $discipline): array
{
    $concepts = array_values((array)($discipline['concepts'] ?? []));
    $firstConcept = $concepts[0] ?? 'conceito central';

    return [
        [
            'stage' => '01',
            'title' => 'Base da aula',
            'task' => 'Ler a aula principal e escrever a regra em linguagem simples.',
            'output' => 'Resumo de 5 linhas sobre ' . $firstConcept . '.',
            'target' => 'sala.php',
        ],
        [
            'stage' => '02',
            'title' => 'Mapa de conceitos',
            'task' => 'Transformar cada conceito em pergunta curta.',
            'output' => count($concepts) . ' perguntas de revisão ativa.',
            'target' => 'conceitos.php',
        ],
        [
            'stage' => '03',
            'title' => 'Caso prático',
            'task' => 'Resolver um caso usando factos, norma, aplicação e conclusão.',
            'output' => 'Resposta escrita em 12 a 18 linhas.',
            'target' => 'cases.php',
        ],
        [
            'stage' => '04',
            'title' => 'Consolidação',
            'task' => 'Guardar erro, criar flashcards e testar no dia seguinte.',
            'output' => 'Um erro no caderno ou três flashcards novos.',
            'target' => 'flashcards.php',
        ],
    ];
}

function disciplineMaterialRecommendations(array $discipline, array $decks, array $mistakes): array
{
    $items = [
        [
            'kind' => 'Aula',
            'title' => 'Aula orientada de ' . $discipline['short'],
            'detail' => $discipline['starter'],
            'target' => 'sala.php',
        ],
        [
            'kind' => 'Prática',
            'title' => 'Caso prático curto',
            'detail' => 'Treina a resposta por requisitos, não por opinião.',
            'target' => 'cases.php',
        ],
    ];

    if ($decks) {
        $deck = $decks[0];
        $items[] = [
            'kind' => 'Memória',
            'title' => (string)($deck['name'] ?? $deck['title'] ?? 'Baralho da disciplina'),
            'detail' => (int)($deck['total_cards'] ?? $deck['card_count'] ?? 0) . ' cartões disponíveis para revisão.',
            'target' => 'flashcards.php',
        ];
    } else {
        $items[] = [
            'kind' => 'Memória',
            'title' => 'Criar baralho da cadeira',
            'detail' => 'Depois da próxima aula, cria 5 cartões com conceitos e requisitos.',
            'target' => 'flashcards.php',
        ];
    }

    if ($mistakes) {
        $items[] = [
            'kind' => 'Correção',
            'title' => 'Rever erro prioritário',
            'detail' => (string)($mistakes[0]['title'] ?? 'Erro registado no caderno.'),
            'target' => 'caderno.php',
        ];
    } else {
        $items[] = [
            'kind' => 'Jurisprudência',
            'title' => 'Ligar teoria a acórdão',
            'detail' => 'Usa um acórdão para perceber como a regra aparece num caso real.',
            'target' => 'acordaos.php',
        ];
    }

    return $items;
}

function disciplinePracticeBrief(array $discipline, array $mistakes): array
{
    $name = (string)($discipline['name'] ?? 'Direito');
    $concepts = array_values((array)($discipline['concepts'] ?? []));
    $main = $concepts[0] ?? 'conceito principal';
    $secondary = $concepts[1] ?? 'requisito secundário';
    $knownMistake = $mistakes[0]['title'] ?? '';

    return [
        'title' => 'Treino rápido de ' . $name,
        'case' => 'Imagina um caso simples onde tens de decidir se há ' . mb_strtolower($main, 'UTF-8') . ' e justificar a resposta usando também ' . mb_strtolower($secondary, 'UTF-8') . '.',
        'method' => [
            '1. Identifica os factos juridicamente relevantes.',
            '2. Diz a regra ou requisito aplicável.',
            '3. Aplica a regra aos factos sem saltar passos.',
            '4. Fecha com uma conclusão curta.',
        ],
        'trap' => $knownMistake !== '' ? 'Cuidado com esta falha já registada: ' . $knownMistake : 'Cuidado com respostas vagas sem requisitos.',
    ];
}

function askDisciplineProfessor(?int $userId, array $discipline, string $question): array
{
    assertAiQuota($userId, 'teacher_question');
    $question = trim($question);
    if (mb_strlen($question, 'UTF-8') < 5) {
        throw new RuntimeException('Escreve uma dúvida concreta da disciplina.');
    }

    $context = [
        'discipline' => $discipline['name'] ?? 'Direito',
        'description' => $discipline['description'] ?? '',
        'concepts' => $discipline['concepts'] ?? [],
        'starter' => $discipline['starter'] ?? '',
        'current_score' => $discipline['score'] ?? 0,
        'open_mistakes' => array_map(
            static fn(array $mistake): string => (string)($mistake['title'] ?? ''),
            array_slice((array)($discipline['mistakes'] ?? []), 0, 4)
        ),
    ];
    $prompt = "Responde a esta dúvida como professor de Direito português numa ficha de disciplina. Devolve apenas JSON válido com: answer, key_points, study_drill, common_trap, next_step.\n\nContexto:\n"
        . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . "\n\nDúvida do aluno:\n" . $question;
    $decoded = decodeAiJson(callGeminiText($prompt, null, 'És um professor de Direito em Portugal. Explica com rigor, linguagem clara e método de exame. Não prestes aconselhamento jurídico profissional.'));
    recordAiUsage($userId, 'teacher_question');

    if ($decoded) {
        $studyDrill = $decoded['study_drill'] ?? 'Escreve uma resposta de 8 linhas e compara com os requisitos da cadeira.';
        $commonTrap = $decoded['common_trap'] ?? 'Evita decorar definições sem aplicar aos factos.';
        $nextStep = $decoded['next_step'] ?? 'Transforma esta dúvida num flashcard.';

        return [
            'answer' => trim((string)($decoded['answer'] ?? '')) ?: 'A resposta veio incompleta. Reformula a dúvida com mais contexto.',
            'key_points' => array_values(array_filter(array_map('strval', (array)($decoded['key_points'] ?? [])))) ?: ['Identificar factos', 'Escolher norma', 'Aplicar por requisitos', 'Concluir'],
            'study_drill' => trim(is_array($studyDrill) ? implode(' ', array_map('strval', $studyDrill)) : (string)$studyDrill),
            'common_trap' => trim(is_array($commonTrap) ? implode(' ', array_map('strval', $commonTrap)) : (string)$commonTrap),
            'next_step' => trim(is_array($nextStep) ? implode(' ', array_map('strval', $nextStep)) : (string)$nextStep),
            'ai_mode' => 'gemini',
        ];
    }

    return [
        'answer' => 'Nesta disciplina, começa pela regra central: ' . ($discipline['starter'] ?? 'identifica a norma, aplica-a aos factos e conclui.'),
        'key_points' => array_values((array)($discipline['concepts'] ?? ['Conceito', 'Requisito', 'Aplicação', 'Conclusão'])),
        'study_drill' => 'Cria um mini-caso com dois factos e resolve em quatro passos: factos, regra, aplicação e conclusão.',
        'common_trap' => 'A falha típica é responder por intuição e não por requisitos jurídicos.',
        'next_step' => 'Abre Casos ou Caderno e transforma esta dúvida em treino.',
        'ai_mode' => 'local',
    ];
}

function handleStudyProfileRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['study_action'])) {
        return;
    }

    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        setFlash('Sessão expirada. Tenta novamente.', 'error');
        header('Location: index.php#diagnostico');
        exit;
    }

    $user = currentUser();
    $userId = $user ? (int)$user['id'] : null;
    $action = (string)$_POST['study_action'];

    if ($action === 'reset_diagnostic') {
        resetStudyProfile($userId);
        setFlash('Diagnóstico limpo. Podes responder de novo.', 'success');
        header('Location: index.php#diagnostico');
        exit;
    }

    if ($action !== 'save_diagnostic') {
        setFlash('Ação de diagnóstico inválida.', 'error');
        header('Location: index.php#diagnostico');
        exit;
    }

    $answers = normalizeDiagnosticAnswers($_POST);
    $profile = buildStudyProfile($answers);
    saveStudyProfile($userId, $answers, $profile);

    $assistantNotes = 'Perfil de estudo: ' . $profile['title'] . '. Foco: ' . $profile['focus']
        . '. Método preferido: ' . $profile['tone'] . '. Sessões de ' . $profile['session_time'] . '.';
    saveLegalAssistantProfile($userId, $assistantNotes);

    setFlash('Diagnóstico guardado. O plano do dia foi ajustado ao teu perfil.', 'success');
    header('Location: index.php#plano-inteligente');
    exit;
}

function normalizeMistakePayload(array $payload): array
{
    $title = trim((string)($payload['title'] ?? 'Erro de estudo'));
    $correction = trim((string)($payload['correction'] ?? 'Rever este ponto com mais atenção.'));

    return [
        'source_type' => mb_substr(trim((string)($payload['source_type'] ?? 'manual')), 0, 40, 'UTF-8'),
        'source_id' => isset($payload['source_id']) ? max(0, (int)$payload['source_id']) : null,
        'area' => mb_substr(trim((string)($payload['area'] ?? 'Geral')), 0, 100, 'UTF-8') ?: 'Geral',
        'title' => mb_substr($title !== '' ? $title : 'Erro de estudo', 0, 180, 'UTF-8'),
        'prompt' => mb_substr(trim((string)($payload['prompt'] ?? '')), 0, 4000, 'UTF-8'),
        'correction' => mb_substr($correction !== '' ? $correction : 'Rever este ponto com mais atenção.', 0, 4000, 'UTF-8'),
        'next_step' => mb_substr(trim((string)($payload['next_step'] ?? 'Rever e tentar novamente amanhã.')), 0, 300, 'UTF-8'),
        'weight' => max(1, min(5, (int)($payload['weight'] ?? 2))),
    ];
}

function logStudyMistake(?int $userId, array $payload): void
{
    $mistake = normalizeMistakePayload($payload);

    if ($userId && tryDB() instanceof PDO && dbSchemaIsReady()) {
        ensureLearningTables();
        $stmt = getDB()->prepare(
            'INSERT INTO study_mistakes (user_id, source_type, source_id, area, title, prompt, correction, next_step, weight)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $mistake['source_type'],
            $mistake['source_id'] ?: null,
            $mistake['area'],
            $mistake['title'],
            $mistake['prompt'],
            $mistake['correction'],
            $mistake['next_step'],
            $mistake['weight'],
        ]);
        return;
    }

    if (!isset($_SESSION['study_mistakes']) || !is_array($_SESSION['study_mistakes'])) {
        $_SESSION['study_mistakes'] = [];
    }

    array_unshift($_SESSION['study_mistakes'], [
        ...$mistake,
        'id' => time(),
        'status' => 'open',
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    $_SESSION['study_mistakes'] = array_slice($_SESSION['study_mistakes'], 0, 40);
}

function getStudyMistakes(?int $userId, int $limit = 24, string $status = 'open'): array
{
    $status = $status === 'resolved' ? 'resolved' : 'open';

    if ($userId && tryDB() instanceof PDO && dbSchemaIsReady()) {
        ensureLearningTables();
        $stmt = getDB()->prepare(
            'SELECT *
             FROM study_mistakes
             WHERE user_id = ? AND status = ?
             ORDER BY weight DESC, created_at DESC
             LIMIT ' . max(1, $limit)
        );
        $stmt->execute([$userId, $status]);
        return $stmt->fetchAll();
    }

    $items = array_values(array_filter(
        $_SESSION['study_mistakes'] ?? [],
        static fn(array $item): bool => ($item['status'] ?? 'open') === $status
    ));

    return array_slice($items, 0, $limit);
}

function studyMistakeStats(?int $userId): array
{
    $open = getStudyMistakes($userId, 200, 'open');
    $resolved = getStudyMistakes($userId, 200, 'resolved');
    $areas = [];

    foreach ($open as $item) {
        $area = (string)($item['area'] ?? 'Geral');
        $areas[$area] = ($areas[$area] ?? 0) + 1;
    }

    arsort($areas);

    return [
        'open' => count($open),
        'resolved' => count($resolved),
        'main_area' => array_key_first($areas) ?: 'Sem área crítica',
    ];
}

function countUserRows(?int $userId, string $table, string $where = ''): int
{
    if (!$userId || !(tryDB() instanceof PDO) || !dbSchemaIsReady()) {
        return 0;
    }

    try {
        ensureLearningTables();
        $sql = 'SELECT COUNT(*) FROM ' . $table . ' WHERE user_id = ?';
        if ($where !== '') {
            $sql .= ' AND ' . $where;
        }
        $stmt = getDB()->prepare($sql);
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    } catch (Throwable) {
        return 0;
    }
}

function legalCompetencyFramework(): array
{
    return [
        [
            'key' => 'orientation',
            'name' => 'Orientação',
            'promise' => 'Saber por onde começar, que área estudar e que tarefa fazer agora.',
            'target' => 'sala.php',
            'next' => 'Abrir a Sala de Estudo e concluir uma aula introdutória.',
        ],
        [
            'key' => 'concepts',
            'name' => 'Conceitos',
            'promise' => 'Explicar institutos jurídicos sem decorar frases vazias.',
            'target' => 'conceitos.php',
            'next' => 'Pesquisar um conceito difícil e criar flashcards a partir dele.',
        ],
        [
            'key' => 'sources',
            'name' => 'Fontes',
            'promise' => 'Ler artigos, acórdãos e materiais sem perder a questão jurídica.',
            'target' => 'acordaos.php',
            'next' => 'Resumir um acórdão e guardar a estrutura na biblioteca.',
        ],
        [
            'key' => 'cases',
            'name' => 'Casos',
            'promise' => 'Transformar factos em problema, regra, aplicação e conclusão.',
            'target' => 'cases.php',
            'next' => 'Resolver um caso curto e pedir análise da tese.',
        ],
        [
            'key' => 'writing',
            'name' => 'Escrita',
            'promise' => 'Escrever como jurista: claro, ordenado, com pedido ou conclusão.',
            'target' => 'pecas.php',
            'next' => 'Gerar uma peça ou parecer com factos bem separados.',
        ],
        [
            'key' => 'memory',
            'name' => 'Memória',
            'promise' => 'Rever artigos e conceitos no momento certo.',
            'target' => 'flashcards.php',
            'next' => 'Rever cartas difíceis antes de estudar matéria nova.',
        ],
        [
            'key' => 'exam',
            'name' => 'Exame',
            'promise' => 'Treinar sob pressão com correção e critérios.',
            'target' => 'simulator.php',
            'next' => 'Fazer um caso no Juiz Virtual e comparar com a correção.',
        ],
    ];
}

function clampScore(int|float $score): int
{
    return max(0, min(100, (int)round($score)));
}

function domainLevelFromScore(int $score): string
{
    return match (true) {
        $score >= 76 => 'Autónomo',
        $score >= 52 => 'Em treino',
        $score >= 26 => 'Fundação',
        default => 'Inicial',
    };
}

function getStudyDomainMap(?int $userId, array $state, ?array $profile): array
{
    $mistakes = studyMistakeStats($userId);
    $profileReady = is_array($profile) ? 1 : 0;

    $concepts = countUserRows($userId, 'legal_concepts');
    $judgments = countUserRows($userId, 'judgment_summaries');
    $sources = countUserRows($userId, 'legal_sources');
    $drafts = countUserRows($userId, 'legal_drafts');
    $researchGuides = countUserRows($userId, 'legal_research_guides');
    $thesisLabs = countUserRows($userId, 'legal_thesis_labs');
    $dailyReviews = countUserRows($userId, 'daily_study_reviews');
    $virtualCases = countUserRows($userId, 'virtual_judge_chats');
    $completedVirtual = countUserRows($userId, 'virtual_judge_chats', 'completed = 1');
    $mentorSessions = countUserRows($userId, 'mentor_sessions');
    $completedMentor = countUserRows($userId, 'mentor_sessions', 'completed = 1');
    $completedLessons = countUserRows($userId, 'study_lesson_progress', 'status = "completed"');
    $examSessions = countUserRows($userId, 'legal_exam_sessions');
    $completedExams = countUserRows($userId, 'legal_exam_sessions', 'evaluated_at IS NOT NULL');
    $caseSessions = countUserRows($userId, 'case_sessions', 'completed = 1');
    $quizAttempts = countUserRows($userId, 'quiz_attempts');
    $reviewedCards = countUserRows($userId, 'flashcard_progress');

    $solvedCases = max((int)($state['solvedCases'] ?? 0), $caseSessions);
    $masteredCards = (int)($state['masteredCards'] ?? 0);
    $quizScore = (int)($state['quizScore'] ?? 0);
    $xp = (int)($state['xp'] ?? 0);

    $scores = [
        'orientation' => clampScore(18 + $profileReady * 35 + min(16, $xp / 40) + min(12, (int)($state['streak'] ?? 0) * 3) + min(18, $completedLessons * 4) + min(10, $dailyReviews * 3) + min(8, $mentorSessions * 4)),
        'concepts' => clampScore(12 + min(30, $concepts * 13) + min(12, $dailyReviews * 3) + min(22, $masteredCards * 3) + min(16, $quizScore * 2) + min(12, $completedMentor * 4) + min(8, $completedLessons * 2)),
        'sources' => clampScore(10 + min(40, ($judgments + $sources + $researchGuides) * 12) + min(22, $concepts * 5) + min(12, $completedLessons * 3)),
        'cases' => clampScore(14 + min(44, $solvedCases * 16) + min(18, $completedMentor * 7) + min(18, max(0, $xp - 100) / 35) - min(14, $mistakes['open'] * 2)),
        'writing' => clampScore(10 + min(38, $drafts * 14) + min(18, $thesisLabs * 8) + min(18, $solvedCases * 5) + min(18, $concepts * 4) + min(10, $completedMentor * 3)),
        'memory' => clampScore(12 + min(45, $reviewedCards * 7) + min(35, $masteredCards * 5) - min(12, $mistakes['open'])),
        'exam' => clampScore(8 + min(30, $completedVirtual * 15) + min(18, $virtualCases * 7) + min(22, $completedExams * 12) + min(18, $quizAttempts * 5) + min(10, $solvedCases * 3) + min(10, $completedMentor * 4)),
    ];

    $framework = legalCompetencyFramework();
    $items = array_map(static function (array $item) use ($scores): array {
        $score = $scores[$item['key']] ?? 0;
        return [
            ...$item,
            'score' => $score,
            'level' => domainLevelFromScore($score),
        ];
    }, $framework);

    usort($items, static fn(array $a, array $b): int => $a['score'] <=> $b['score']);
    $weakest = $items[0] ?? null;
    $orderedByFramework = array_map(static function (array $item) use ($scores): array {
        $score = $scores[$item['key']] ?? 0;
        return [
            ...$item,
            'score' => $score,
            'level' => domainLevelFromScore($score),
        ];
    }, $framework);
    $average = clampScore(array_sum($scores) / max(1, count($scores)));

    return [
        'average' => $average,
        'level' => domainLevelFromScore($average),
        'weakest' => $weakest,
        'items' => $orderedByFramework,
        'evidence' => [
            'concepts' => $concepts,
            'judgments' => $judgments,
            'sources' => $sources,
            'research_guides' => $researchGuides,
            'thesis_labs' => $thesisLabs,
            'daily_reviews' => $dailyReviews,
            'drafts' => $drafts,
            'virtual_cases' => $virtualCases,
            'completed_virtual' => $completedVirtual,
            'mentor_sessions' => $mentorSessions,
            'completed_mentor' => $completedMentor,
            'completed_lessons' => $completedLessons,
            'exam_sessions' => $examSessions,
            'completed_exams' => $completedExams,
            'case_sessions' => $solvedCases,
            'quiz_attempts' => $quizAttempts,
            'reviewed_cards' => $reviewedCards,
            'open_mistakes' => $mistakes['open'],
            'resolved_mistakes' => $mistakes['resolved'],
        ],
    ];
}

function countUserRowsSince(?int $userId, string $table, string $dateColumn = 'created_at', string $where = '', int $days = 7): int
{
    $allowed = [
        'study_lesson_progress' => ['completed_at', 'updated_at'],
        'mentor_sessions' => ['created_at', 'updated_at'],
        'case_sessions' => ['created_at', 'updated_at'],
        'quiz_attempts' => ['created_at'],
        'study_mistakes' => ['created_at', 'updated_at'],
        'flashcard_progress' => ['last_reviewed'],
        'legal_concepts' => ['created_at'],
        'judgment_summaries' => ['created_at'],
        'legal_sources' => ['created_at'],
        'legal_drafts' => ['created_at'],
        'legal_research_guides' => ['created_at'],
        'legal_thesis_labs' => ['created_at'],
        'daily_study_reviews' => ['created_at'],
        'daily_review_attempts' => ['created_at'],
        'virtual_judge_chats' => ['created_at', 'updated_at'],
        'legal_exam_sessions' => ['created_at', 'updated_at', 'evaluated_at'],
    ];

    if (!$userId || !(tryDB() instanceof PDO) || !dbSchemaIsReady() || !isset($allowed[$table]) || !in_array($dateColumn, $allowed[$table], true)) {
        return 0;
    }

    try {
        ensureLearningTables();
        $daysSql = max(1, (int)$days);
        $sql = 'SELECT COUNT(*) FROM ' . $table . ' WHERE user_id = ? AND ' . $dateColumn . ' >= DATE_SUB(NOW(), INTERVAL ' . $daysSql . ' DAY)';
        if ($where !== '') {
            $sql .= ' AND ' . $where;
        }
        $stmt = getDB()->prepare($sql);
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    } catch (Throwable) {
        return 0;
    }
}

function countAiUsageSince(?int $userId, string $feature, int $days = 7): int
{
    if ($userId && tryDB() instanceof PDO && dbSchemaIsReady()) {
        try {
            ensureLearningTables();
            $daysSql = max(1, (int)$days);
            $stmt = getDB()->prepare(
                'SELECT COALESCE(SUM(quantity), 0)
                 FROM ai_usage_events
                 WHERE user_id = ? AND feature = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ' . $daysSql . ' DAY)'
            );
            $stmt->execute([$userId, $feature]);
            return (int)$stmt->fetchColumn();
        } catch (Throwable) {
            return 0;
        }
    }

    $today = date('Y-m-d') . ':' . $feature;
    return (int)($_SESSION['ai_usage_events'][$today] ?? 0);
}

function countAssistantMessagesSince(?int $userId, int $days = 7): int
{
    $usage = countAiUsageSince($userId, 'assistant_message', $days);
    if (!$userId || !(tryDB() instanceof PDO) || !dbSchemaIsReady()) {
        return $usage;
    }

    try {
        ensureLearningTables();
        $daysSql = max(1, (int)$days);
        $stmt = getDB()->prepare(
            "SELECT COUNT(*)
             FROM legal_assistant_messages m
             INNER JOIN legal_assistant_threads t ON t.id = m.thread_id
             WHERE t.user_id = ? AND m.role = 'user' AND m.created_at >= DATE_SUB(NOW(), INTERVAL " . $daysSql . " DAY)"
        );
        $stmt->execute([$userId]);
        return max($usage, (int)$stmt->fetchColumn());
    } catch (Throwable) {
        return $usage;
    }
}

function completedLessonsSince(?int $userId, int $days = 7): int
{
    if ($userId && tryDB() instanceof PDO && dbSchemaIsReady()) {
        return countUserRowsSince($userId, 'study_lesson_progress', 'completed_at', 'status = "completed"', $days);
    }

    $limit = time() - (max(1, $days) * 86400);
    return count(array_filter($_SESSION['study_lesson_progress'] ?? [], static function (array $item) use ($limit): bool {
        return ($item['status'] ?? 'completed') === 'completed' && strtotime((string)($item['completed_at'] ?? '')) >= $limit;
    }));
}

function weeklyStudyReport(?int $userId, array $state, ?array $profile): array
{
    $days = 7;
    $domain = getStudyDomainMap($userId, $state, $profile);
    $mistakes = studyMistakeStats($userId);
    $assistantToday = aiUsageSummary($userId, 'assistant_message');
    $teacherToday = aiUsageSummary($userId, 'teacher_question');
    $lessonsWeek = completedLessonsSince($userId, $days);
    $assistantWeek = countAssistantMessagesSince($userId, $days);
    $teacherWeek = countAiUsageSince($userId, 'teacher_question', $days);
    $mentorWeek = countUserRowsSince($userId, 'mentor_sessions', 'created_at', '', $days);
    $examWeek = countUserRowsSince($userId, 'legal_exam_sessions', 'evaluated_at', 'evaluated_at IS NOT NULL', $days);
    $casesWeek = countUserRowsSince($userId, 'case_sessions', 'updated_at', 'completed = 1', $days);
    $quizWeek = countUserRowsSince($userId, 'quiz_attempts', 'created_at', '', $days);
    $cardsWeek = countUserRowsSince($userId, 'flashcard_progress', 'last_reviewed', 'last_reviewed IS NOT NULL', $days);
    $mistakesWeek = countUserRowsSince($userId, 'study_mistakes', 'created_at', '', $days);
    $resolvedWeek = countUserRowsSince($userId, 'study_mistakes', 'updated_at', 'status = "resolved"', $days);
    $productionWeek = countUserRowsSince($userId, 'legal_drafts', 'created_at', '', $days)
        + countUserRowsSince($userId, 'legal_concepts', 'created_at', '', $days)
        + countUserRowsSince($userId, 'judgment_summaries', 'created_at', '', $days)
        + countUserRowsSince($userId, 'legal_sources', 'created_at', '', $days)
        + countUserRowsSince($userId, 'legal_research_guides', 'created_at', '', $days)
        + countUserRowsSince($userId, 'legal_thesis_labs', 'created_at', '', $days)
        + countUserRowsSince($userId, 'daily_study_reviews', 'created_at', '', $days)
        + countUserRowsSince($userId, 'daily_review_attempts', 'created_at', '', $days);

    $wins = [];
    if ($lessonsWeek > 0) {
        $wins[] = 'Concluíste ' . $lessonsWeek . ' aula' . ($lessonsWeek === 1 ? '' : 's') . '. A base teórica está a crescer.';
    }
    if ($assistantWeek + $teacherWeek > 0) {
        $wins[] = 'Usaste IA ' . ($assistantWeek + $teacherWeek) . ' vez' . (($assistantWeek + $teacherWeek) === 1 ? '' : 'es') . ' para esclarecer matéria.';
    }
    if ($mentorWeek + $casesWeek + $quizWeek + $examWeek > 0) {
        $wins[] = 'Houve treino ativo: mentor, casos ou quiz. Isto é melhor do que só ler.';
    }
    if ($resolvedWeek > 0) {
        $wins[] = 'Fechaste ' . $resolvedWeek . ' erro' . ($resolvedWeek === 1 ? '' : 's') . ' no caderno.';
    }
    if (!$wins) {
        $wins[] = 'Ainda há pouca atividade recente. O sistema precisa de dados para ser realmente inteligente.';
    }

    $risks = [];
    if (!$profile) {
        $risks[] = 'Sem diagnóstico inicial, o plano ainda trabalha às cegas.';
    }
    if ($lessonsWeek === 0) {
        $risks[] = 'Não há aulas concluídas nos últimos 7 dias. Falta base antes do treino pesado.';
    }
    if ($mistakes['open'] >= 4) {
        $risks[] = 'Tens vários erros abertos. Se não os fechares, o sistema vira arquivo e não método.';
    }
    if ($assistantToday['remaining'] <= 1 && $assistantToday['plan'] === 'free') {
        $risks[] = 'O limite diário de IA está quase gasto. Usa as próximas mensagens para dúvidas concretas.';
    }
    if (($casesWeek + $quizWeek + $cardsWeek) === 0) {
        $risks[] = 'Faltou recuperação ativa: casos, quiz ou flashcards.';
    }
    if (!$risks) {
        $risks[] = 'Risco baixo. Mantém a semana equilibrada entre teoria, prática e revisão.';
    }

    $weakest = $domain['weakest'] ?? [
        'name' => 'Orientação',
        'target' => 'sala.php',
        'next' => 'Começa por uma aula introdutória.',
    ];

    $nextWeek = [
        [
            'label' => 'Foco fraco',
            'title' => (string)($weakest['name'] ?? 'Orientação'),
            'detail' => (string)($weakest['next'] ?? 'Treinar a competência mais fraca.'),
            'target' => (string)($weakest['target'] ?? 'sala.php'),
        ],
        [
            'label' => 'Aula',
            'title' => 'Concluir 2 aulas curtas',
            'detail' => 'Uma aula para base teórica e outra para consolidar com exercício.',
            'target' => 'sala.php',
        ],
        [
            'label' => 'Revisão',
            'title' => $mistakes['open'] > 0 ? 'Fechar erros abertos' : 'Criar revisão ativa',
            'detail' => $mistakes['open'] > 0 ? 'Revê o caderno antes de consumir matéria nova.' : 'Gera flashcards ou resolve um caso para criar material de revisão.',
            'target' => $mistakes['open'] > 0 ? 'caderno.php' : 'flashcards.php',
        ],
    ];

    $activityScore = clampScore(
        min(30, $lessonsWeek * 12)
        + min(24, ($casesWeek + $quizWeek + $mentorWeek) * 10)
        + min(18, $cardsWeek * 4)
        + min(16, ($assistantWeek + $teacherWeek) * 2)
        + min(12, $productionWeek * 4)
        + min(10, $resolvedWeek * 5)
        - min(18, $mistakes['open'] * 2)
    );

    return [
        'range_label' => 'Últimos 7 dias',
        'domain_average' => $domain['average'],
        'domain_level' => $domain['level'],
        'weakest' => $weakest,
        'activity_score' => $activityScore,
        'summary' => [
            'lessons' => $lessonsWeek,
            'assistant_messages' => $assistantWeek,
            'teacher_questions' => $teacherWeek,
            'mentor_sessions' => $mentorWeek,
            'cases' => $casesWeek,
            'quiz' => $quizWeek,
            'cards' => $cardsWeek,
            'production' => $productionWeek,
            'mistakes_new' => $mistakesWeek,
            'mistakes_open' => $mistakes['open'],
            'mistakes_resolved' => $resolvedWeek,
        ],
        'wins' => $wins,
        'risks' => $risks,
        'next_week' => $nextWeek,
        'usage' => [
            'assistant_today' => $assistantToday,
            'teacher_today' => $teacherToday,
        ],
    ];
}

function resolveStudyMistake(?int $userId, int $mistakeId): void
{
    if ($userId && tryDB() instanceof PDO && dbSchemaIsReady()) {
        ensureLearningTables();
        $stmt = getDB()->prepare('UPDATE study_mistakes SET status = "resolved", updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
        $stmt->execute([$mistakeId, $userId]);
        return;
    }

    if (!isset($_SESSION['study_mistakes']) || !is_array($_SESSION['study_mistakes'])) {
        return;
    }

    foreach ($_SESSION['study_mistakes'] as &$item) {
        if ((int)($item['id'] ?? 0) === $mistakeId) {
            $item['status'] = 'resolved';
            break;
        }
    }
    unset($item);
}

function handleMistakeNotebookRequest(?int $userId): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['mistake_action'])) {
        return;
    }

    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        setFlash('Sessão expirada. Tenta novamente.', 'error');
        header('Location: caderno.php');
        exit;
    }

    if ((string)$_POST['mistake_action'] === 'resolve') {
        resolveStudyMistake($userId, max(1, (int)($_POST['mistake_id'] ?? 0)));
        setFlash('Erro marcado como revisto.', 'success');
    }

    header('Location: caderno.php');
    exit;
}

function billingPlans(): array
{
    return [
        'free' => [
            'name' => 'Free',
            'price' => '0€',
            'period' => 'para começar',
            'assistant_limit' => FREE_ASSISTANT_MESSAGES_DAILY,
            'features' => [
                'Acesso ao painel, diagnóstico e Trilho Zero',
                'Aulas introdutórias da Sala de Estudo',
                FREE_ASSISTANT_MESSAGES_DAILY . ' mensagens por dia no Assistente',
                'Flashcards e casos práticos base',
            ],
        ],
        'plus' => [
            'name' => 'Plus',
            'price' => '7,99€',
            'period' => 'por mês',
            'assistant_limit' => PLUS_ASSISTANT_MESSAGES_DAILY,
            'features' => [
                'Sala de Estudo completa',
                PLUS_ASSISTANT_MESSAGES_DAILY . ' mensagens por dia no Assistente',
                'Professor IA nas aulas com maior limite',
                'Mais treino com Mentor, casos e revisão',
            ],
        ],
    ];
}

function stripeBillingIsConfigured(): bool
{
    return STRIPE_SECRET_KEY !== '' && STRIPE_PRICE_PLUS_MONTHLY !== '' && function_exists('curl_init');
}

function currentUserSubscription(?int $userId): array
{
    $fallback = [
        'plan' => 'free',
        'status' => 'inactive',
        'is_plus' => false,
        'current_period_end' => null,
        'cancel_at_period_end' => false,
    ];

    if (!$userId || !(tryDB() instanceof PDO) || !dbSchemaIsReady()) {
        return $fallback;
    }

    try {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT * FROM user_subscriptions WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return $fallback;
        }

        $activeStatus = in_array((string)$row['status'], ['active', 'trialing'], true);
        $periodEnd = $row['current_period_end'] ?? null;
        $periodOk = !$periodEnd || strtotime((string)$periodEnd) >= time();
        $isPlus = (string)$row['plan'] === 'plus' && $activeStatus && $periodOk;

        return [
            ...$fallback,
            ...$row,
            'is_plus' => $isPlus,
            'cancel_at_period_end' => !empty($row['cancel_at_period_end']),
        ];
    } catch (Throwable) {
        return $fallback;
    }
}

function userHasPlus(?int $userId): bool
{
    return currentUserSubscription($userId)['is_plus'] === true;
}

function aiUsageLimit(?int $userId, string $feature = 'assistant_message'): int
{
    return userHasPlus($userId) ? PLUS_ASSISTANT_MESSAGES_DAILY : FREE_ASSISTANT_MESSAGES_DAILY;
}

function aiUsageCountToday(?int $userId, string $feature = 'assistant_message'): int
{
    if ($userId && tryDB() instanceof PDO && dbSchemaIsReady()) {
        try {
            ensureLearningTables();
            $stmt = getDB()->prepare('SELECT COALESCE(SUM(quantity), 0) FROM ai_usage_events WHERE user_id = ? AND feature = ? AND DATE(created_at) = CURDATE()');
            $stmt->execute([$userId, $feature]);
            return (int)$stmt->fetchColumn();
        } catch (Throwable) {
            return 0;
        }
    }

    $today = date('Y-m-d');
    $key = $today . ':' . $feature;
    return (int)($_SESSION['ai_usage_events'][$key] ?? 0);
}

function aiUsageSummary(?int $userId, string $feature = 'assistant_message'): array
{
    $limit = aiUsageLimit($userId, $feature);
    $used = aiUsageCountToday($userId, $feature);

    return [
        'used' => $used,
        'limit' => $limit,
        'remaining' => max(0, $limit - $used),
        'percent' => clampScore(($used / max(1, $limit)) * 100),
        'plan' => userHasPlus($userId) ? 'plus' : 'free',
    ];
}

function assertAiQuota(?int $userId, string $feature = 'assistant_message'): void
{
    $summary = aiUsageSummary($userId, $feature);
    if ($summary['used'] >= $summary['limit']) {
        throw new RuntimeException('Atingiste o limite diário do plano Free. Abre Planos para aumentar o limite de mensagens.');
    }
}

function recordAiUsage(?int $userId, string $feature = 'assistant_message', int $quantity = 1): void
{
    $quantity = max(1, $quantity);
    if ($userId && tryDB() instanceof PDO && dbSchemaIsReady()) {
        try {
            ensureLearningTables();
            $stmt = getDB()->prepare('INSERT INTO ai_usage_events (user_id, feature, quantity) VALUES (?, ?, ?)');
            $stmt->execute([$userId, $feature, $quantity]);
            return;
        } catch (Throwable) {
            return;
        }
    }

    $today = date('Y-m-d');
    $key = $today . ':' . $feature;
    if (!isset($_SESSION['ai_usage_events']) || !is_array($_SESSION['ai_usage_events'])) {
        $_SESSION['ai_usage_events'] = [];
    }
    $_SESSION['ai_usage_events'][$key] = (int)($_SESSION['ai_usage_events'][$key] ?? 0) + $quantity;
}

function appUrl(string $path = ''): string
{
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

function stripeApiRequest(string $path, array $params): array
{
    if (!stripeBillingIsConfigured()) {
        throw new RuntimeException('Stripe ainda não está configurado. Define STRIPE_SECRET_KEY e STRIPE_PRICE_PLUS_MONTHLY.');
    }

    $ch = curl_init('https://api.stripe.com/v1/' . ltrim($path, '/'));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . STRIPE_SECRET_KEY,
            'Content-Type: application/x-www-form-urlencoded',
        ],
        CURLOPT_TIMEOUT => 45,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new RuntimeException('Erro cURL ao ligar ao Stripe: ' . $curlError);
    }

    $decoded = json_decode($response, true);
    if ($httpCode < 200 || $httpCode >= 300) {
        $message = is_array($decoded) ? (string)($decoded['error']['message'] ?? 'Erro no Stripe.') : 'Erro no Stripe.';
        throw new RuntimeException($message);
    }

    return is_array($decoded) ? $decoded : [];
}

function createStripeCheckoutSession(int $userId): string
{
    $user = currentUser();
    if (!$user || (int)$user['id'] !== $userId) {
        throw new RuntimeException('Tens de iniciar sessão para subscrever o Plus.');
    }

    $session = stripeApiRequest('checkout/sessions', [
        'mode' => 'subscription',
        'client_reference_id' => (string)$userId,
        'customer_email' => (string)$user['email'],
        'line_items[0][price]' => STRIPE_PRICE_PLUS_MONTHLY,
        'line_items[0][quantity]' => '1',
        'allow_promotion_codes' => 'true',
        'metadata[user_id]' => (string)$userId,
        'metadata[plan]' => 'plus',
        'subscription_data[metadata][user_id]' => (string)$userId,
        'subscription_data[metadata][plan]' => 'plus',
        'success_url' => appUrl('billing.php?checkout=success'),
        'cancel_url' => appUrl('billing.php?checkout=cancelled'),
    ]);

    $url = (string)($session['url'] ?? '');
    if ($url === '') {
        throw new RuntimeException('O Stripe não devolveu URL de checkout.');
    }

    return $url;
}

function stripeSignatureIsValid(string $payload, string $signatureHeader): bool
{
    if (STRIPE_WEBHOOK_SECRET === '' || $signatureHeader === '') {
        return false;
    }

    $timestamp = null;
    $signatures = [];
    foreach (explode(',', $signatureHeader) as $part) {
        [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');
        if ($key === 't') {
            $timestamp = $value;
        } elseif ($key === 'v1') {
            $signatures[] = $value;
        }
    }

    if (!$timestamp || !$signatures) {
        return false;
    }

    if (abs(time() - (int)$timestamp) > 300) {
        return false;
    }

    $expected = hash_hmac('sha256', $timestamp . '.' . $payload, STRIPE_WEBHOOK_SECRET);
    foreach ($signatures as $signature) {
        if (hash_equals($expected, $signature)) {
            return true;
        }
    }

    return false;
}

function upsertSubscription(array $data): void
{
    $userId = (int)($data['user_id'] ?? 0);
    if ($userId <= 0 || !(tryDB() instanceof PDO) || !dbSchemaIsReady()) {
        return;
    }

    ensureLearningTables();
    $periodEnd = isset($data['current_period_end']) && (int)$data['current_period_end'] > 0
        ? date('Y-m-d H:i:s', (int)$data['current_period_end'])
        : null;

    $stmt = getDB()->prepare(
        "INSERT INTO user_subscriptions (user_id, plan, status, stripe_customer_id, stripe_subscription_id, current_period_end, cancel_at_period_end)
         VALUES (?, 'plus', ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            plan = VALUES(plan),
            status = VALUES(status),
            stripe_customer_id = COALESCE(VALUES(stripe_customer_id), stripe_customer_id),
            stripe_subscription_id = COALESCE(VALUES(stripe_subscription_id), stripe_subscription_id),
            current_period_end = VALUES(current_period_end),
            cancel_at_period_end = VALUES(cancel_at_period_end)"
    );
    $stmt->execute([
        $userId,
        (string)($data['status'] ?? 'active'),
        $data['stripe_customer_id'] ?? null,
        $data['stripe_subscription_id'] ?? null,
        $periodEnd,
        !empty($data['cancel_at_period_end']) ? 1 : 0,
    ]);
}

function handleStripeWebhookEvent(array $event): void
{
    $type = (string)($event['type'] ?? '');
    $object = (array)($event['data']['object'] ?? []);

    if ($type === 'checkout.session.completed' && ($object['mode'] ?? '') === 'subscription') {
        upsertSubscription([
            'user_id' => (int)($object['client_reference_id'] ?? ($object['metadata']['user_id'] ?? 0)),
            'status' => 'active',
            'stripe_customer_id' => $object['customer'] ?? null,
            'stripe_subscription_id' => $object['subscription'] ?? null,
        ]);
        return;
    }

    if (str_starts_with($type, 'customer.subscription.')) {
        $subscriptionId = (string)($object['id'] ?? '');
        if ($subscriptionId === '' || !(tryDB() instanceof PDO) || !dbSchemaIsReady()) {
            return;
        }

        ensureLearningTables();
        $userId = (int)($object['metadata']['user_id'] ?? 0);
        if ($userId <= 0) {
            $stmt = getDB()->prepare('SELECT user_id FROM user_subscriptions WHERE stripe_subscription_id = ? LIMIT 1');
            $stmt->execute([$subscriptionId]);
            $userId = (int)($stmt->fetch()['user_id'] ?? 0);
        }

        if ($userId <= 0) {
            return;
        }

        upsertSubscription([
            'user_id' => $userId,
            'status' => $type === 'customer.subscription.deleted' ? 'canceled' : (string)($object['status'] ?? 'active'),
            'stripe_customer_id' => $object['customer'] ?? null,
            'stripe_subscription_id' => $subscriptionId,
            'current_period_end' => $object['current_period_end'] ?? null,
            'cancel_at_period_end' => $object['cancel_at_period_end'] ?? false,
        ]);
    }
}

function studyRoomCurriculum(): array
{
    return [
        'fundamentos' => [
            'title' => 'Fundamentos do Direito',
            'subtitle' => 'Antes de decorar artigos, percebe para que serve o Direito.',
            'level' => 'Iniciante',
            'plus_only' => false,
            'lessons' => [
                [
                    'id' => 'fundamentos-ordem-juridica',
                    'title' => 'O que é uma ordem jurídica?',
                    'duration' => 18,
                    'goal' => 'Distinguir regras sociais, moral e normas jurídicas.',
                    'summary' => 'O Direito organiza a vida em sociedade através de normas externas, gerais e coercivas. Uma resposta jurídica começa por separar opinião, moral e regra aplicável.',
                    'steps' => ['Identificar a regra', 'Perceber quem a aplica', 'Separar sanção social de sanção jurídica'],
                    'exercise' => 'Explica por que razão uma promessa entre amigos pode não ser juridicamente exigível, mas um contrato pode ser.',
                    'resources' => [
                        ['type' => 'Vídeo', 'label' => 'Pesquisar aulas de Introdução ao Direito', 'url' => 'https://www.youtube.com/results?search_query=introducao+ao+direito+portugues'],
                        ['type' => 'Livro', 'label' => 'Procurar manuais de Introdução ao Direito', 'url' => 'https://www.google.com/search?tbm=bks&q=Introducao+ao+Direito+Portugal'],
                        ['type' => 'Fonte oficial', 'label' => 'Diário da República', 'url' => 'https://dre.pt/'],
                    ],
                ],
                [
                    'id' => 'fundamentos-fontes',
                    'title' => 'Fontes do Direito e hierarquia',
                    'duration' => 22,
                    'goal' => 'Perceber lei, Constituição, jurisprudência, costume e doutrina.',
                    'summary' => 'Nem todas as fontes têm o mesmo peso. A Constituição está no topo, a lei ordinária deve respeitá-la e a jurisprudência ajuda a interpretar casos concretos.',
                    'steps' => ['Começar pela Constituição', 'Descer para leis e códigos', 'Usar jurisprudência para ver aplicação prática'],
                    'exercise' => 'Ordena estes elementos por força jurídica: acórdão, Constituição, contrato, lei ordinária.',
                    'resources' => [
                        ['type' => 'Fonte oficial', 'label' => 'Assembleia da República', 'url' => 'https://www.parlamento.pt/'],
                        ['type' => 'Jurisprudência', 'label' => 'DGSI - bases de dados jurídicas', 'url' => 'https://www.dgsi.pt/'],
                        ['type' => 'Vídeo', 'label' => 'Pesquisar hierarquia das fontes do Direito', 'url' => 'https://www.youtube.com/results?search_query=fontes+do+direito+hierarquia+portugal'],
                    ],
                ],
                [
                    'id' => 'fundamentos-metodo-caso',
                    'title' => 'Método para resolver casos práticos',
                    'duration' => 25,
                    'goal' => 'Aprender a estrutura facto, regra, aplicação e conclusão.',
                    'summary' => 'O erro típico é despejar teoria. Num caso prático, cada frase deve ligar um facto a uma regra e terminar numa consequência.',
                    'steps' => ['Sublinhar factos relevantes', 'Escolher a regra', 'Aplicar requisito a requisito', 'Concluir sem fugir ao problema'],
                    'exercise' => 'Transforma uma discussão de vizinhos num problema jurídico: que factos importam e que regra procurarias?',
                    'resources' => [
                        ['type' => 'Treino', 'label' => 'Abrir Casos Práticos da plataforma', 'url' => 'cases.php'],
                        ['type' => 'Professor IA', 'label' => 'Treinar no Mentor Jurídico', 'url' => 'mentor.php'],
                        ['type' => 'Vídeo', 'label' => 'Pesquisar método IRAC em Direito', 'url' => 'https://www.youtube.com/results?search_query=metodo+IRAC+direito+casos+praticos'],
                    ],
                ],
            ],
        ],
        'constitucional' => [
            'title' => 'Direito Constitucional',
            'subtitle' => 'O mapa do Estado, dos direitos fundamentais e dos limites do poder.',
            'level' => 'Base universitária',
            'plus_only' => true,
            'lessons' => [
                [
                    'id' => 'constitucional-estado',
                    'title' => 'Estado, Constituição e órgãos de soberania',
                    'duration' => 24,
                    'goal' => 'Perceber quem faz leis, quem governa e quem julga.',
                    'summary' => 'Direito Constitucional dá o desenho do poder político. Sem esta base, os outros ramos ficam soltos.',
                    'steps' => ['Identificar órgão competente', 'Separar função legislativa, executiva e jurisdicional', 'Ver limites constitucionais'],
                    'exercise' => 'Explica por que razão um tribunal não cria uma lei geral, mas pode interpretar a lei num caso.',
                    'resources' => [
                        ['type' => 'Fonte oficial', 'label' => 'Constituição e Parlamento', 'url' => 'https://www.parlamento.pt/Legislacao/Paginas/ConstituicaoRepublicaPortuguesa.aspx'],
                        ['type' => 'Vídeo', 'label' => 'Pesquisar órgãos de soberania Portugal', 'url' => 'https://www.youtube.com/results?search_query=orgaos+de+soberania+portugal+direito+constitucional'],
                        ['type' => 'Livro', 'label' => 'Procurar manuais de Direito Constitucional', 'url' => 'https://www.google.com/search?tbm=bks&q=Direito+Constitucional+Portugal+manual'],
                    ],
                ],
                [
                    'id' => 'constitucional-direitos',
                    'title' => 'Direitos fundamentais',
                    'duration' => 26,
                    'goal' => 'Perceber direitos, restrições e proporcionalidade.',
                    'summary' => 'Quando o Estado limita uma liberdade, tens de perguntar se há base legal e se a medida é adequada, necessária e proporcional.',
                    'steps' => ['Identificar o direito afetado', 'Confirmar base legal', 'Testar proporcionalidade'],
                    'exercise' => 'Analisa uma proibição total de manifestações perto de tribunais. Que teste aplicas?',
                    'resources' => [
                        ['type' => 'Fonte oficial', 'label' => 'Constituição - direitos fundamentais', 'url' => 'https://www.parlamento.pt/Legislacao/Paginas/ConstituicaoRepublicaPortuguesa.aspx'],
                        ['type' => 'Vídeo', 'label' => 'Pesquisar proporcionalidade direitos fundamentais', 'url' => 'https://www.youtube.com/results?search_query=proporcionalidade+direitos+fundamentais+direito+constitucional'],
                        ['type' => 'Treino', 'label' => 'Abrir Mentor Constitucional', 'url' => 'mentor.php'],
                    ],
                ],
            ],
        ],
        'civil' => [
            'title' => 'Direito Civil',
            'subtitle' => 'Obrigações, contratos, responsabilidade e vida privada.',
            'level' => 'Base universitária',
            'plus_only' => true,
            'lessons' => [
                [
                    'id' => 'civil-obrigacoes',
                    'title' => 'Obrigações: credor, devedor e prestação',
                    'duration' => 23,
                    'goal' => 'Ler uma relação obrigacional sem confundir sujeitos e deveres.',
                    'summary' => 'Uma obrigação liga credor e devedor em torno de uma prestação. O primeiro passo é sempre saber quem pode exigir e quem tem de cumprir.',
                    'steps' => ['Identificar credor', 'Identificar devedor', 'Definir prestação', 'Ver incumprimento'],
                    'exercise' => 'Num contrato de compra e venda, separa as obrigações do comprador e do vendedor.',
                    'resources' => [
                        ['type' => 'Fonte oficial', 'label' => 'PGDL - legislação civil', 'url' => 'https://www.pgdlisboa.pt/leis/lei_main.php'],
                        ['type' => 'Vídeo', 'label' => 'Pesquisar Direito das Obrigações', 'url' => 'https://www.youtube.com/results?search_query=direito+das+obrigacoes+portugal'],
                        ['type' => 'Livro', 'label' => 'Procurar manuais de Direito Civil', 'url' => 'https://www.google.com/search?tbm=bks&q=Direito+Civil+Obriga%C3%A7%C3%B5es+Portugal'],
                    ],
                ],
                [
                    'id' => 'civil-responsabilidade',
                    'title' => 'Responsabilidade civil',
                    'duration' => 28,
                    'goal' => 'Aplicar facto, ilicitude, culpa, dano e nexo causal.',
                    'summary' => 'Não há responsabilidade civil só porque alguém sofreu dano. É preciso demonstrar requisitos e ligar juridicamente a conduta ao prejuízo.',
                    'steps' => ['Facto', 'Ilicitude', 'Culpa', 'Dano', 'Nexo causal'],
                    'exercise' => 'Um condutor bate num carro parado em dia de chuva. Aplica os cinco requisitos.',
                    'resources' => [
                        ['type' => 'Treino', 'label' => 'Resolver caso prático civil', 'url' => 'cases.php'],
                        ['type' => 'Vídeo', 'label' => 'Pesquisar responsabilidade civil Portugal', 'url' => 'https://www.youtube.com/results?search_query=responsabilidade+civil+portugal+direito'],
                        ['type' => 'Jurisprudência', 'label' => 'Pesquisar acórdãos no DGSI', 'url' => 'https://www.dgsi.pt/'],
                    ],
                ],
            ],
        ],
        'penal' => [
            'title' => 'Direito Penal',
            'subtitle' => 'Crime, pena, culpa e limites do poder punitivo.',
            'level' => 'Base universitária',
            'plus_only' => true,
            'lessons' => [
                [
                    'id' => 'penal-teoria-crime',
                    'title' => 'Teoria do crime em quatro blocos',
                    'duration' => 30,
                    'goal' => 'Separar tipicidade, ilicitude, culpa e punibilidade.',
                    'summary' => 'Em Penal, a ordem interessa. Primeiro perguntas se a conduta cabe no tipo; depois se é ilícita; depois se o agente é culpável.',
                    'steps' => ['Tipo objetivo', 'Tipo subjetivo', 'Ilicitude', 'Culpa'],
                    'exercise' => 'João furta comida por fome. Que blocos tens de analisar antes de concluir?',
                    'resources' => [
                        ['type' => 'Fonte oficial', 'label' => 'PGDL - legislação penal', 'url' => 'https://www.pgdlisboa.pt/leis/lei_main.php'],
                        ['type' => 'Vídeo', 'label' => 'Pesquisar teoria do crime Direito Penal', 'url' => 'https://www.youtube.com/results?search_query=teoria+do+crime+direito+penal+portugal'],
                        ['type' => 'Professor IA', 'label' => 'Abrir Juiz Virtual', 'url' => 'simulator.php'],
                    ],
                ],
                [
                    'id' => 'penal-dolo-negligencia',
                    'title' => 'Dolo e negligência',
                    'duration' => 24,
                    'goal' => 'Distinguir intenção, conformação e descuido.',
                    'summary' => 'O elemento subjetivo decide muita coisa. Dolo direto, necessário e eventual não são o mesmo que negligência consciente.',
                    'steps' => ['Ver previsão do resultado', 'Ver vontade ou conformação', 'Comparar com dever de cuidado'],
                    'exercise' => 'Cria dois exemplos: um de dolo eventual e outro de negligência consciente.',
                    'resources' => [
                        ['type' => 'Vídeo', 'label' => 'Pesquisar dolo eventual negligência consciente', 'url' => 'https://www.youtube.com/results?search_query=dolo+eventual+negligencia+consciente+direito+penal'],
                        ['type' => 'Flashcards', 'label' => 'Rever flashcards de Penal', 'url' => 'flashcards.php'],
                        ['type' => 'Livro', 'label' => 'Procurar manuais de Direito Penal', 'url' => 'https://www.google.com/search?tbm=bks&q=Direito+Penal+Portugal+teoria+do+crime'],
                    ],
                ],
            ],
        ],
        'trabalho' => [
            'title' => 'Direito do Trabalho',
            'subtitle' => 'Contrato, subordinação, despedimento e proteção do trabalhador.',
            'level' => 'Base prática',
            'plus_only' => true,
            'lessons' => [
                [
                    'id' => 'trabalho-contrato',
                    'title' => 'Contrato de trabalho',
                    'duration' => 22,
                    'goal' => 'Reconhecer subordinação jurídica e prestação de trabalho.',
                    'summary' => 'A pergunta central é se há trabalho prestado por conta de outrem, com remuneração e subordinação. A forma do contrato nem sempre decide tudo.',
                    'steps' => ['Prestação de atividade', 'Remuneração', 'Subordinação', 'Inserção na organização'],
                    'exercise' => 'Distingue trabalhador subordinado de prestador independente num exemplo concreto.',
                    'resources' => [
                        ['type' => 'Fonte oficial', 'label' => 'ACT - Autoridade para as Condições do Trabalho', 'url' => 'https://portal.act.gov.pt/'],
                        ['type' => 'Vídeo', 'label' => 'Pesquisar contrato de trabalho Portugal', 'url' => 'https://www.youtube.com/results?search_query=contrato+de+trabalho+portugal+direito'],
                        ['type' => 'Livro', 'label' => 'Procurar manuais de Direito do Trabalho', 'url' => 'https://www.google.com/search?tbm=bks&q=Direito+do+Trabalho+Portugal+manual'],
                    ],
                ],
                [
                    'id' => 'trabalho-despedimento',
                    'title' => 'Despedimento e ilicitude',
                    'duration' => 28,
                    'goal' => 'Separar motivo, procedimento e prova.',
                    'summary' => 'Num despedimento, não basta a empresa dizer que há motivo. Tens de verificar procedimento, fundamentos reais e consequências.',
                    'steps' => ['Qualificar modalidade', 'Ver procedimento', 'Avaliar prova', 'Concluir efeitos'],
                    'exercise' => 'Uma empresa extingue um posto e contrata alguém para funções parecidas. Que perguntas fazes?',
                    'resources' => [
                        ['type' => 'Treino', 'label' => 'Resolver caso laboral', 'url' => 'cases.php'],
                        ['type' => 'Vídeo', 'label' => 'Pesquisar despedimento ilícito Portugal', 'url' => 'https://www.youtube.com/results?search_query=despedimento+ilicito+portugal+direito+trabalho'],
                        ['type' => 'Fonte oficial', 'label' => 'ACT - despedimento', 'url' => 'https://portal.act.gov.pt/'],
                    ],
                ],
            ],
        ],
    ];
}

function flattenStudyLessons(): array
{
    $flat = [];
    foreach (studyRoomCurriculum() as $moduleKey => $module) {
        foreach ($module['lessons'] as $index => $lesson) {
            $flat[$lesson['id']] = [
                ...$lesson,
                'module_key' => $moduleKey,
                'module_title' => $module['title'],
                'module_subtitle' => $module['subtitle'],
                'module_level' => $module['level'],
                'plus_only' => !empty($module['plus_only']) || !empty($lesson['plus_only']),
                'index' => $index + 1,
            ];
        }
    }

    return $flat;
}

function getStudyLesson(?string $lessonId): array
{
    $lessons = flattenStudyLessons();
    if ($lessonId && isset($lessons[$lessonId])) {
        return $lessons[$lessonId];
    }

    return reset($lessons);
}

function canAccessStudyLesson(?int $userId, array $lesson): bool
{
    return empty($lesson['plus_only']) || userHasPlus($userId);
}

function getStudyRoomProgress(?int $userId): array
{
    if ($userId && tryDB() instanceof PDO && dbSchemaIsReady()) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT lesson_id, status, completed_at FROM study_lesson_progress WHERE user_id = ?');
        $stmt->execute([$userId]);
        $progress = [];
        foreach ($stmt->fetchAll() as $row) {
            $progress[$row['lesson_id']] = $row;
        }
        return $progress;
    }

    if (!isset($_SESSION['study_lesson_progress']) || !is_array($_SESSION['study_lesson_progress'])) {
        $_SESSION['study_lesson_progress'] = [];
    }

    return $_SESSION['study_lesson_progress'];
}

function completeStudyLesson(?int $userId, string $lessonId): bool
{
    $lessons = flattenStudyLessons();
    if (!isset($lessons[$lessonId])) {
        throw new RuntimeException('Aula não encontrada.');
    }
    if (!canAccessStudyLesson($userId, $lessons[$lessonId])) {
        throw new RuntimeException('Esta aula faz parte do plano Plus.');
    }

    if ($userId && tryDB() instanceof PDO && dbSchemaIsReady()) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT id FROM study_lesson_progress WHERE user_id = ? AND lesson_id = ? LIMIT 1');
        $stmt->execute([$userId, $lessonId]);
        $alreadyDone = (bool)$stmt->fetch();

        $save = getDB()->prepare(
            "INSERT INTO study_lesson_progress (user_id, lesson_id, status, completed_at)
             VALUES (?, ?, 'completed', NOW())
             ON DUPLICATE KEY UPDATE status = 'completed', updated_at = NOW()"
        );
        $save->execute([$userId, $lessonId]);

        if (!$alreadyDone) {
            $xp = 35;
            $xpStmt = getDB()->prepare('SELECT xp FROM users WHERE id = ?');
            $xpStmt->execute([$userId]);
            $currentXp = (int)($xpStmt->fetch()['xp'] ?? 0);
            getDB()->prepare('UPDATE users SET xp = xp + ?, level = ? WHERE id = ?')->execute([$xp, getLevel($currentXp + $xp), $userId]);
            writeActivityLog($userId, 'lesson_completed', 'Aula concluída: ' . $lessons[$lessonId]['title'], $xp);
        }

        return !$alreadyDone;
    }

    if (!isset($_SESSION['study_lesson_progress']) || !is_array($_SESSION['study_lesson_progress'])) {
        $_SESSION['study_lesson_progress'] = [];
    }

    $alreadyDone = isset($_SESSION['study_lesson_progress'][$lessonId]);
    $_SESSION['study_lesson_progress'][$lessonId] = [
        'lesson_id' => $lessonId,
        'status' => 'completed',
        'completed_at' => date('Y-m-d H:i:s'),
    ];

    return !$alreadyDone;
}

function askStudyProfessor(?int $userId, array $lesson, string $question): array
{
    if (!canAccessStudyLesson($userId, $lesson)) {
        throw new RuntimeException('Esta aula faz parte do plano Plus.');
    }

    assertAiQuota($userId, 'teacher_question');
    $question = trim($question);
    if (mb_strlen($question, 'UTF-8') < 5) {
        throw new RuntimeException('Escreve uma pergunta concreta para o professor.');
    }

    $prompt = "Responde como professor de Direito português para um aluno no início do curso. Usa a aula como contexto e devolve apenas JSON válido com: answer, key_points, mini_exercise, warning.\n\nAula:\n" . json_encode($lesson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\nPergunta do aluno:\n" . $question;
    $decoded = decodeAiJson(callGeminiText($prompt, null, 'És um professor de Direito em Portugal. Explica com rigor, linguagem simples e foco em estudo. Não dês aconselhamento jurídico profissional.'));

    if ($decoded) {
        recordAiUsage($userId, 'teacher_question');
        return [
            'answer' => trim((string)($decoded['answer'] ?? '')) ?: 'A resposta foi gerada, mas veio incompleta.',
            'key_points' => array_values(array_filter(array_map('strval', (array)($decoded['key_points'] ?? [])))) ?: ['Volta aos requisitos da aula e aplica-os por ordem.'],
            'mini_exercise' => trim((string)($decoded['mini_exercise'] ?? 'Resume a ideia em três frases.')),
            'warning' => trim((string)($decoded['warning'] ?? 'Confirma sempre a matéria com legislação e materiais da faculdade.')),
            'ai_mode' => 'gemini',
        ];
    }

    recordAiUsage($userId, 'teacher_question');

    return [
        'answer' => 'Começa pela ideia central da aula: ' . ($lesson['summary'] ?? 'identifica a regra, aplica aos factos e conclui.') . ' A tua pergunta deve ser resolvida separando conceito, requisito e exemplo.',
        'key_points' => (array)($lesson['steps'] ?? ['Identificar conceito', 'Aplicar a um exemplo', 'Concluir']),
        'mini_exercise' => $lesson['exercise'] ?? 'Cria um exemplo simples e resolve em quatro linhas.',
        'warning' => 'Resposta local. Com IA configurada, o professor consegue adaptar melhor a explicação à tua dúvida.',
        'ai_mode' => 'local',
    ];
}

function mentorAreas(): array
{
    return [
        'civil' => [
            'area' => 'Direito Civil',
            'concept' => 'responsabilidade civil',
            'lesson' => 'A responsabilidade civil pergunta se alguém deve reparar um dano. O raciocínio base passa por facto, ilicitude, culpa, dano e nexo causal. Num caso prático, não basta dizer que houve dano: tens de ligar o dano a uma conduta juridicamente relevante.',
            'case' => 'Um condutor distraído embate num veículo parado e causa danos de 1.200 euros. O condutor diz que a estrada estava molhada.',
            'expected' => ['facto', 'ilicitude', 'culpa', 'dano', 'nexo causal', 'indemnização'],
        ],
        'penal' => [
            'area' => 'Direito Penal',
            'concept' => 'furto e estado de necessidade',
            'lesson' => 'Em Penal, primeiro qualificas a conduta: tipo objetivo, tipo subjetivo, ilicitude e culpa. Mesmo quando parece haver crime, tens de testar causas de exclusão como estado de necessidade.',
            'case' => 'João tira alimentos de um supermercado sem pagar porque não tem dinheiro e diz que o filho estava sem comer.',
            'expected' => ['furto', 'subtração', 'dolo', 'ilicitude', 'estado de necessidade', 'culpa'],
        ],
        'constitucional' => [
            'area' => 'Direito Constitucional',
            'concept' => 'restrição de direitos fundamentais',
            'lesson' => 'Uma restrição a direitos fundamentais exige base constitucional ou legal e deve passar pelo teste da proporcionalidade: adequação, necessidade e proporcionalidade em sentido estrito.',
            'case' => 'Uma lei proíbe manifestações junto a tribunais durante todo o ano para proteger a tranquilidade pública.',
            'expected' => ['direito fundamental', 'restrição', 'lei', 'adequação', 'necessidade', 'proporcionalidade'],
        ],
        'trabalho' => [
            'area' => 'Direito do Trabalho',
            'concept' => 'despedimento ilícito',
            'lesson' => 'No despedimento, separa motivo, procedimento e prova. Mesmo quando a empresa invoca reorganização, é preciso confirmar se o posto desapareceu mesmo e se os critérios legais foram cumpridos.',
            'case' => 'Uma empresa extingue o posto de Ana, mas dois meses depois contrata outra pessoa para tarefas muito parecidas.',
            'expected' => ['despedimento', 'posto', 'procedimento', 'prova', 'ilicitude', 'reintegração'],
        ],
    ];
}

function localMentorSession(string $areaKey): array
{
    $areas = mentorAreas();
    $seed = $areas[$areaKey] ?? $areas['civil'];

    return [
        'id' => bin2hex(random_bytes(4)),
        'area_key' => $areaKey,
        'area' => $seed['area'],
        'title' => 'Mentor: ' . $seed['concept'],
        'concept' => $seed['concept'],
        'lesson' => $seed['lesson'],
        'case' => $seed['case'],
        'question' => 'Como resolverias este caso? Responde em 5 a 8 linhas usando factos, regra, aplicação e conclusão.',
        'expected' => $seed['expected'],
        'turns' => [],
        'step' => 1,
        'max_steps' => 3,
        'completed' => false,
        'score' => 0,
        'ai_mode' => 'local',
        'created_at' => date('Y-m-d H:i:s'),
    ];
}

function startMentorSession(?int $userId, string $areaKey): array
{
    $areaKey = array_key_exists($areaKey, mentorAreas()) ? $areaKey : 'civil';
    $base = localMentorSession($areaKey);

    $prompt = "Cria uma sessão curta de Mentor Jurídico para estudante de Direito em Portugal. Devolve apenas JSON válido com: title, area, concept, lesson, case, question, expected. A lesson deve ter no máximo 6 linhas. A question deve obrigar a aplicar factos à regra.\n\nBase: " . json_encode($base, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $decoded = decodeAiJson(callGeminiText($prompt, null, 'És um professor de Direito português, exigente e claro. Não dês aconselhamento jurídico profissional; foca estudo.'));

    if ($decoded) {
        $base['title'] = trim((string)($decoded['title'] ?? $base['title'])) ?: $base['title'];
        $base['area'] = trim((string)($decoded['area'] ?? $base['area'])) ?: $base['area'];
        $base['concept'] = trim((string)($decoded['concept'] ?? $base['concept'])) ?: $base['concept'];
        $base['lesson'] = trim((string)($decoded['lesson'] ?? $base['lesson'])) ?: $base['lesson'];
        $base['case'] = trim((string)($decoded['case'] ?? $base['case'])) ?: $base['case'];
        $base['question'] = trim((string)($decoded['question'] ?? $base['question'])) ?: $base['question'];
        $expected = array_values(array_filter(array_map('strval', (array)($decoded['expected'] ?? []))));
        if ($expected) {
            $base['expected'] = $expected;
        }
        $base['ai_mode'] = 'gemini';
    }

    $_SESSION['mentor_session'] = $base;
    return $base;
}

function evaluateMentorAnswer(?int $userId, array $session, string $answer): array
{
    $answer = trim($answer);
    if (mb_strlen($answer, 'UTF-8') < 10) {
        throw new RuntimeException('Escreve uma resposta concreta antes de enviar.');
    }

    $prompt = "Avalia a resposta do estudante nesta sessão de Mentor Jurídico. Devolve apenas JSON válido com: score_20, feedback, strengths, gaps, next_question, completed. Não sejas genérico: diz o que faltou no raciocínio.\n\nSessão:\n" . json_encode($session, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\nResposta:\n" . $answer;
    $decoded = decodeAiJson(callGeminiText($prompt, null, 'És um professor de Direito português. Corrige para fins de estudo, com rigor e objetividade.'));

    $evaluation = $decoded ? normalizeMentorEvaluation($decoded) : localMentorEvaluation($session, $answer);
    $turn = [
        'answer' => $answer,
        'evaluation' => $evaluation,
        'created_at' => date('Y-m-d H:i:s'),
    ];

    $session['turns'][] = $turn;
    $session['step'] = count($session['turns']) + 1;
    $session['score'] = max((int)($session['score'] ?? 0), (int)$evaluation['score_20']);
    $session['completed'] = (bool)$evaluation['completed'] || count($session['turns']) >= (int)($session['max_steps'] ?? 3);

    if ((int)$evaluation['score_20'] < 12) {
        logStudyMistake($userId, [
            'source_type' => 'mentor',
            'area' => $session['area'] ?? 'Geral',
            'title' => 'Mentor: ' . ($session['concept'] ?? 'raciocínio jurídico'),
            'prompt' => $answer,
            'correction' => $evaluation['feedback'],
            'next_step' => $evaluation['next_question'],
            'weight' => 4,
        ]);
    }

    if ($session['completed']) {
        saveMentorSession($userId, $session);
    }

    $_SESSION['mentor_session'] = $session;
    return $session;
}

function normalizeMentorEvaluation(array $data): array
{
    $score = max(0, min(20, (int)($data['score_20'] ?? 10)));
    $strengths = array_values(array_filter(array_map('strval', (array)($data['strengths'] ?? []))));
    $gaps = array_values(array_filter(array_map('strval', (array)($data['gaps'] ?? []))));

    return [
        'score_20' => $score,
        'feedback' => trim((string)($data['feedback'] ?? 'Resposta avaliada.')) ?: 'Resposta avaliada.',
        'strengths' => $strengths ?: ['Tens uma tentativa de enquadramento.'],
        'gaps' => $gaps ?: ['Falta aplicar melhor a regra aos factos.'],
        'next_question' => trim((string)($data['next_question'] ?? 'Reescreve a resposta separando factos, regra, aplicação e conclusão.')),
        'completed' => (bool)($data['completed'] ?? ($score >= 16)),
    ];
}

function localMentorEvaluation(array $session, string $answer): array
{
    $lower = mb_strtolower($answer, 'UTF-8');
    $hits = 0;
    foreach ((array)($session['expected'] ?? []) as $term) {
        if ($term !== '' && str_contains($lower, mb_strtolower((string)$term, 'UTF-8'))) {
            $hits++;
        }
    }

    $words = count(preg_split('/\s+/', trim($answer), -1, PREG_SPLIT_NO_EMPTY) ?: []);
    $structureHits = 0;
    foreach (['facto', 'regra', 'art', 'porque', 'logo', 'conclus', 'aplica'] as $term) {
        if (str_contains($lower, $term)) {
            $structureHits++;
        }
    }

    $score = max(5, min(18, 6 + $hits * 2 + min(4, (int)floor($words / 35)) + min(4, $structureHits)));
    $gaps = [];
    if ($hits < 2) {
        $gaps[] = 'Ainda não usaste os conceitos centrais da matéria.';
    }
    if ($structureHits < 3) {
        $gaps[] = 'A resposta precisa de uma estrutura mais visível: factos, regra, aplicação e conclusão.';
    }
    if ($words < 55) {
        $gaps[] = 'Está curta para uma resposta de exame.';
    }

    return [
        'score_20' => $score,
        'feedback' => $score >= 14
            ? 'A resposta já tem base. Agora melhora a ligação entre cada facto e a consequência jurídica.'
            : 'A resposta ainda está incompleta. Tens de nomear a regra, aplicar aos factos e fechar com conclusão.',
        'strengths' => $structureHits > 0 ? ['Tentaste construir uma resposta jurídica.'] : ['Identificaste pelo menos parte do problema.'],
        'gaps' => $gaps ?: ['Falta antecipar um contra-argumento.'],
        'next_question' => 'Reescreve em quatro frases: facto relevante, regra jurídica, aplicação ao caso e conclusão.',
        'completed' => $score >= 16,
    ];
}

function saveMentorSession(?int $userId, array $session): void
{
    if (!(tryDB() instanceof PDO) || !dbSchemaIsReady()) {
        return;
    }

    ensureLearningTables();
    $stmt = getDB()->prepare(
        'INSERT INTO mentor_sessions (user_id, area, title, session_json, score, completed, ai_mode)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId,
        $session['area'] ?? 'Geral',
        $session['title'] ?? 'Sessão Mentor',
        json_encode($session, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        (int)($session['score'] ?? 0),
        !empty($session['completed']) ? 1 : 0,
        $session['ai_mode'] ?? 'local',
    ]);
}

function mentorReport(array $session): array
{
    $turns = (array)($session['turns'] ?? []);
    $last = $turns ? $turns[array_key_last($turns)] : null;
    $evaluation = is_array($last) ? (array)($last['evaluation'] ?? []) : [];

    return [
        'title' => $session['title'] ?? 'Sessão Mentor',
        'area' => $session['area'] ?? 'Geral',
        'score' => (int)($session['score'] ?? ($evaluation['score_20'] ?? 0)),
        'learned' => $session['concept'] ?? 'raciocínio jurídico',
        'review' => $evaluation['gaps'] ?? ['Rever a estrutura da resposta.'],
        'next' => $evaluation['next_question'] ?? 'Repetir o exercício amanhã.',
    ];
}

function tableColumnExists(PDO $db, string $table, string $column): bool
{
    $stmt = $db->prepare(
        'SELECT COUNT(*)
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

function ensureFlashcardPersonalColumns(?PDO $db = null): void
{
    $db ??= tryDB();
    if (!$db || !dbSchemaIsReady()) {
        return;
    }

    try {
        $exists = $db->query("SHOW TABLES LIKE 'flashcard_decks'")->fetchColumn();
        if (!$exists) {
            return;
        }

        if (!tableColumnExists($db, 'flashcard_decks', 'user_id')) {
            $db->exec('ALTER TABLE flashcard_decks ADD COLUMN user_id INT UNSIGNED DEFAULT NULL AFTER id');
        }
        if (!tableColumnExists($db, 'flashcard_decks', 'source_type')) {
            $db->exec("ALTER TABLE flashcard_decks ADD COLUMN source_type VARCHAR(40) DEFAULT 'core' AFTER active");
        }
        if (!tableColumnExists($db, 'flashcard_decks', 'source_id')) {
            $db->exec('ALTER TABLE flashcard_decks ADD COLUMN source_id INT UNSIGNED DEFAULT NULL AFTER source_type');
        }
    } catch (Throwable) {
        return;
    }
}

function callGeminiText(string $prompt, ?array $inlineFile = null, string $systemInstruction = ''): ?string
{
    if (GEMINI_API_KEY === '') {
        $_SESSION['last_gemini_error'] = 'GEMINI_API_KEY não está configurada.';
        return null;
    }

    if (!function_exists('curl_init')) {
        $_SESSION['last_gemini_error'] = 'A extensão cURL não está ativa no PHP do XAMPP.';
        return null;
    }

    $parts = [];
    if ($inlineFile) {
        $parts[] = [
            'inline_data' => [
                'mime_type' => $inlineFile['mime_type'],
                'data' => base64_encode($inlineFile['bytes']),
            ],
        ];
    }
    $parts[] = ['text' => $prompt];

    $payload = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => $parts,
            ],
        ],
        'generationConfig' => [
            'temperature' => 0.25,
            'responseMimeType' => 'application/json',
        ],
    ];

    if ($systemInstruction !== '') {
        $payload['systemInstruction'] = [
            'parts' => [
                ['text' => $systemInstruction],
            ],
        ];
    }

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode(GEMINI_MODEL) . ':generateContent';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-goog-api-key: ' . GEMINI_API_KEY,
        ],
        CURLOPT_TIMEOUT => 90,
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        $_SESSION['last_gemini_error'] = 'Erro cURL ao ligar ao Gemini: ' . $curlError;
        return null;
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        $error = json_decode($response, true);
        $_SESSION['last_gemini_error'] = $error['error']['message'] ?? ('Resposta HTTP inválida do Gemini: ' . $httpCode);
        return null;
    }

    $data = json_decode($response, true);
    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    if (!is_string($text) || trim($text) === '') {
        $_SESSION['last_gemini_error'] = 'O Gemini respondeu sem texto utilizável.';
        return null;
    }

    unset($_SESSION['last_gemini_error']);
    return $text;
}

function decodeAiJson(?string $json): ?array
{
    if (!$json) {
        return null;
    }

    $json = trim($json);
    $json = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $json) ?? $json;
    $decoded = json_decode($json, true);

    return is_array($decoded) ? $decoded : null;
}

function maskSecret(string $secret): string
{
    $secret = trim($secret);
    if ($secret === '') {
        return 'não configurada';
    }

    if (mb_strlen($secret, 'UTF-8') <= 10) {
        return str_repeat('*', mb_strlen($secret, 'UTF-8'));
    }

    return mb_substr($secret, 0, 6, 'UTF-8') . '...' . mb_substr($secret, -4, null, 'UTF-8');
}

function geminiRuntimeStatus(): array
{
    return [
        'configured' => GEMINI_API_KEY !== '',
        'curl' => function_exists('curl_init'),
        'model' => GEMINI_MODEL,
        'key_preview' => maskSecret(GEMINI_API_KEY),
        'last_error' => $_SESSION['last_gemini_error'] ?? '',
    ];
}

function legalAssistantUsesDatabase(?int $userId): bool
{
    return $userId !== null && tryDB() instanceof PDO && dbSchemaIsReady();
}

function ensureLegalAssistantSession(): void
{
    if (!isset($_SESSION['legal_assistant_threads']) || !is_array($_SESSION['legal_assistant_threads'])) {
        $_SESSION['legal_assistant_threads'] = [];
    }

    if (!isset($_SESSION['legal_assistant_next_id'])) {
        $_SESSION['legal_assistant_next_id'] = 1;
    }
}

function legalAssistantTitleFromQuestion(string $question): string
{
    $question = normalizeWhitespace($question);
    $first = preg_split('/[.?!\n]/u', $question)[0] ?? $question;
    $title = trim($first);
    if ($title === '') {
        return 'Nova conversa';
    }

    return mb_strlen($title, 'UTF-8') > 64
        ? mb_substr($title, 0, 61, 'UTF-8') . '...'
        : $title;
}

function detectLegalArea(string $text): string
{
    $lower = mb_strtolower($text, 'UTF-8');
    $map = [
        'Direito Penal' => ['crime', 'furto', 'roubo', 'homicídio', 'pena', 'arguido', 'ilícito', 'culpa'],
        'Direito Civil' => ['contrato', 'arrendamento', 'responsabilidade civil', 'indemnização', 'caução', 'posse'],
        'Direito Constitucional' => ['constituição', 'direitos fundamentais', 'inconstitucionalidade', 'tribunal constitucional'],
        'Direito do Trabalho' => ['despedimento', 'trabalhador', 'empregador', 'contrato de trabalho', 'salário'],
        'Processo' => ['petição', 'contestação', 'recurso', 'prova', 'sentença', 'prazo', 'tribunal'],
    ];

    foreach ($map as $area => $terms) {
        foreach ($terms as $term) {
            if (str_contains($lower, $term)) {
                return $area;
            }
        }
    }

    return 'Geral';
}

function inferLegalAssistantStyleNotes(array $messages): string
{
    $userMessages = array_values(array_filter(
        $messages,
        static fn(array $message): bool => ($message['role'] ?? '') === 'user'
    ));

    if (!$userMessages) {
        return 'Aluno novo. Responder de forma direta, calma e organizada.';
    }

    $totalLength = array_sum(array_map(
        static fn(array $message): int => mb_strlen((string)($message['content'] ?? ''), 'UTF-8'),
        $userMessages
    ));
    $average = (int)round($totalLength / max(1, count($userMessages)));
    $last = mb_strtolower((string)($userMessages[array_key_last($userMessages)]['content'] ?? ''), 'UTF-8');

    $notes = [];
    $notes[] = $average < 120
        ? 'Prefere mensagens curtas e respostas objetivas.'
        : 'Aceita explicações desenvolvidas, desde que estejam bem separadas por passos.';
    if (str_contains($last, 'não sei') || str_contains($last, 'nao sei')) {
        $notes[] = 'Quando houver dúvida, começar pelo enquadramento simples antes dos detalhes.';
    }
    if (str_contains($last, 'exemplo') || str_contains($last, 'caso')) {
        $notes[] = 'Valoriza exemplos práticos e aplicação a factos.';
    }

    return implode(' ', $notes);
}

function getLegalAssistantProfile(?int $userId): string
{
    if (legalAssistantUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT style_notes FROM legal_assistant_profiles WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        return trim((string)($stmt->fetchColumn() ?: ''));
    }

    ensureLegalAssistantSession();
    return trim((string)($_SESSION['legal_assistant_profile'] ?? ''));
}

function saveLegalAssistantProfile(?int $userId, string $styleNotes): void
{
    $styleNotes = trim($styleNotes);
    if ($styleNotes === '') {
        return;
    }

    if (legalAssistantUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare(
            'INSERT INTO legal_assistant_profiles (user_id, style_notes) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE style_notes = VALUES(style_notes), updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([$userId, $styleNotes]);
        return;
    }

    ensureLegalAssistantSession();
    $_SESSION['legal_assistant_profile'] = $styleNotes;
}

function getLegalAssistantThreads(?int $userId): array
{
    if (legalAssistantUsesDatabase($userId)) {
        ensureLearningTables();
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT t.*,
                (SELECT COUNT(*) FROM legal_assistant_messages m WHERE m.thread_id = t.id) AS message_count,
                (SELECT m.content FROM legal_assistant_messages m WHERE m.thread_id = t.id ORDER BY m.id DESC LIMIT 1) AS last_message
             FROM legal_assistant_threads t
             WHERE t.user_id = ?
             ORDER BY t.updated_at DESC, t.id DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    ensureLegalAssistantSession();
    $threads = array_values($_SESSION['legal_assistant_threads']);
    usort($threads, static fn(array $a, array $b): int => strcmp((string)($b['updated_at'] ?? ''), (string)($a['updated_at'] ?? '')));

    return array_map(static function (array $thread): array {
        $messages = $thread['messages'] ?? [];
        $last = $messages ? (string)($messages[array_key_last($messages)]['content'] ?? '') : '';
        unset($thread['messages']);
        $thread['message_count'] = count($messages);
        $thread['last_message'] = $last;
        return $thread;
    }, $threads);
}

function createLegalAssistantThread(?int $userId, string $title = 'Nova conversa'): array
{
    $now = date('Y-m-d H:i:s');
    $title = trim($title) !== '' ? trim($title) : 'Nova conversa';
    $profile = getLegalAssistantProfile($userId);
    $style = $profile !== '' ? $profile : 'Aluno novo. Responder de forma direta, calma e organizada.';

    if (legalAssistantUsesDatabase($userId)) {
        ensureLearningTables();
        $db = getDB();
        $stmt = $db->prepare('INSERT INTO legal_assistant_threads (user_id, title, area, style_notes) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $title, 'Geral', $style]);
        return getLegalAssistantThread($userId, (int)$db->lastInsertId()) ?? [
            'id' => (int)$db->lastInsertId(),
            'title' => $title,
            'area' => 'Geral',
            'style_notes' => $style,
            'messages' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    ensureLegalAssistantSession();
    $id = (int)$_SESSION['legal_assistant_next_id'];
    $_SESSION['legal_assistant_next_id'] = $id + 1;
    $_SESSION['legal_assistant_threads'][$id] = [
        'id' => $id,
        'title' => $title,
        'area' => 'Geral',
        'style_notes' => $style,
        'messages' => [],
        'created_at' => $now,
        'updated_at' => $now,
    ];

    return $_SESSION['legal_assistant_threads'][$id];
}

function getLegalAssistantThread(?int $userId, int $threadId): ?array
{
    if (legalAssistantUsesDatabase($userId)) {
        ensureLearningTables();
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM legal_assistant_threads WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$threadId, $userId]);
        $thread = $stmt->fetch();
        if (!$thread) {
            return null;
        }

        $messages = $db->prepare('SELECT role, content, created_at FROM legal_assistant_messages WHERE thread_id = ? ORDER BY id ASC');
        $messages->execute([$threadId]);
        $thread['messages'] = $messages->fetchAll();
        return $thread;
    }

    ensureLegalAssistantSession();
    return $_SESSION['legal_assistant_threads'][$threadId] ?? null;
}

function deleteLegalAssistantThread(?int $userId, int $threadId): void
{
    if (legalAssistantUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('DELETE FROM legal_assistant_threads WHERE id = ? AND user_id = ?');
        $stmt->execute([$threadId, $userId]);
        return;
    }

    ensureLegalAssistantSession();
    unset($_SESSION['legal_assistant_threads'][$threadId]);
}

function appendLegalAssistantMessage(?int $userId, int $threadId, string $role, string $content): void
{
    $content = trim($content);
    if ($content === '') {
        return;
    }

    if (legalAssistantUsesDatabase($userId)) {
        ensureLearningTables();
        $db = getDB();
        $stmt = $db->prepare('INSERT INTO legal_assistant_messages (thread_id, role, content) VALUES (?, ?, ?)');
        $stmt->execute([$threadId, $role, $content]);
        $db->prepare('UPDATE legal_assistant_threads SET updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?')
            ->execute([$threadId, $userId]);
        return;
    }

    ensureLegalAssistantSession();
    if (!isset($_SESSION['legal_assistant_threads'][$threadId])) {
        return;
    }

    $_SESSION['legal_assistant_threads'][$threadId]['messages'][] = [
        'role' => $role,
        'content' => $content,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    $_SESSION['legal_assistant_threads'][$threadId]['updated_at'] = date('Y-m-d H:i:s');
}

function updateLegalAssistantThreadMeta(?int $userId, int $threadId, array $meta): void
{
    $title = trim((string)($meta['title'] ?? ''));
    $area = trim((string)($meta['area'] ?? 'Geral'));
    $styleNotes = trim((string)($meta['style_notes'] ?? ''));

    if (legalAssistantUsesDatabase($userId)) {
        ensureLearningTables();
        $db = getDB();
        $current = getLegalAssistantThread($userId, $threadId);
        if (!$current) {
            return;
        }
        $title = $title !== '' ? $title : (string)$current['title'];
        $area = $area !== '' ? $area : (string)($current['area'] ?? 'Geral');
        $styleNotes = $styleNotes !== '' ? $styleNotes : (string)($current['style_notes'] ?? '');
        $stmt = $db->prepare('UPDATE legal_assistant_threads SET title = ?, area = ?, style_notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
        $stmt->execute([$title, $area, $styleNotes, $threadId, $userId]);
        return;
    }

    ensureLegalAssistantSession();
    if (!isset($_SESSION['legal_assistant_threads'][$threadId])) {
        return;
    }

    if ($title !== '') {
        $_SESSION['legal_assistant_threads'][$threadId]['title'] = $title;
    }
    if ($area !== '') {
        $_SESSION['legal_assistant_threads'][$threadId]['area'] = $area;
    }
    if ($styleNotes !== '') {
        $_SESSION['legal_assistant_threads'][$threadId]['style_notes'] = $styleNotes;
    }
    $_SESSION['legal_assistant_threads'][$threadId]['updated_at'] = date('Y-m-d H:i:s');
}

function normalizeLegalAssistantReply(array $reply, array $thread, string $question): array
{
    $messages = $thread['messages'] ?? [];
    $answer = trim((string)($reply['answer'] ?? ''));
    if ($answer === '') {
        return localLegalAssistantReply($thread, $question);
    }

    $suggested = $reply['suggested_questions'] ?? [];
    if (!is_array($suggested)) {
        $suggested = [];
    }

    return [
        'answer' => $answer,
        'title' => legalAssistantTitleFromQuestion((string)($reply['title'] ?? $question)),
        'area' => trim((string)($reply['area'] ?? detectLegalArea($question))) ?: 'Geral',
        'style_notes' => trim((string)($reply['style_notes'] ?? inferLegalAssistantStyleNotes($messages))),
        'suggested_questions' => array_slice(array_values(array_filter(array_map('strval', $suggested))), 0, 3),
    ];
}

function generateLegalAssistantReply(array $thread, string $question): array
{
    $messages = array_slice($thread['messages'] ?? [], -12);
    $styleNotes = trim((string)($thread['style_notes'] ?? ''));
    if ($styleNotes === '') {
        $styleNotes = inferLegalAssistantStyleNotes($messages);
    }

    $systemInstruction = "És o Assistente Jurídico do LexStudy, uma plataforma de estudo de Direito e advocacia em Portugal. Responde apenas a temas jurídicos, estudo de Direito, advocacia, peças processuais, casos práticos, acórdãos, legislação, profissão jurídica e métodos de estudo jurídico. Se o pedido fugir a Direito, redireciona com educação para um ângulo jurídico. Usa português europeu. Sê direto, claro e útil para um estudante de Direito. Não inventes artigos, jurisprudência ou prazos. Quando não tiveres certeza, diz para confirmar na lei atual ou numa fonte oficial. Não te apresentes como advogado do utilizador nem prometas resultados. Para casos reais, recomenda validação com advogado. Adapta a resposta ao estilo do aluno: {$styleNotes}";
    $prompt = "Devolve apenas JSON válido com estas chaves: answer, title, area, style_notes, suggested_questions.\n\nHistórico recente:\n" . json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\nPergunta atual:\n{$question}";

    $decoded = decodeAiJson(callGeminiText($prompt, null, $systemInstruction));
    if ($decoded) {
        return normalizeLegalAssistantReply($decoded, $thread, $question);
    }

    return localLegalAssistantReply($thread, $question);
}

function localLegalAssistantReply(array $thread, string $question): array
{
    $area = detectLegalArea($question);
    $lower = mb_strtolower($question, 'UTF-8');
    $intro = GEMINI_API_KEY === ''
        ? "Estou em modo local porque a chave Gemini não está ativa. Ainda assim, posso estruturar o raciocínio jurídico.\n\n"
        : '';

    $answer = $intro . "Para responder bem, eu trabalharia assim:\n\n"
        . "1. Factos relevantes: separa o que aconteceu do que ainda é opinião.\n"
        . "2. Questão jurídica: transforma a dúvida numa pergunta concreta.\n"
        . "3. Regime aplicável: identifica código, artigos e requisitos legais.\n"
        . "4. Aplicação aos factos: verifica requisito por requisito.\n"
        . "5. Conclusão: termina com uma resposta curta e defensável.\n\n";

    if (str_contains($lower, 'furto') || str_contains($lower, 'roubo')) {
        $answer .= "No tema de crimes patrimoniais, não fiques só pela tipicidade. Analisa também ilicitude, culpa, causas de exclusão, tentativa, comparticipação e medida da pena, conforme o caso.";
    } elseif (str_contains($lower, 'despedimento')) {
        $answer .= "No despedimento, confirma sempre: fundamento invocado, procedimento, prazos, prova, critérios de seleção e consequências da ilicitude.";
    } elseif (str_contains($lower, 'contrato') || str_contains($lower, 'arrendamento')) {
        $answer .= "Em contratos, começa por formação, validade, incumprimento, mora, resolução e responsabilidade civil. No arrendamento, junta o regime especial aplicável.";
    } elseif (str_contains($lower, 'recurso')) {
        $answer .= "Num recurso, organiza por: decisão recorrida, vício ou erro, norma violada, pedido e efeito útil pretendido.";
    } else {
        $answer .= "Se me deres factos concretos ou o artigo em causa, consigo transformar isto numa resposta de exame ou num plano de peça jurídica.";
    }

    $messages = $thread['messages'] ?? [];
    $messages[] = ['role' => 'user', 'content' => $question];

    return [
        'answer' => $answer,
        'title' => legalAssistantTitleFromQuestion($question),
        'area' => $area,
        'style_notes' => inferLegalAssistantStyleNotes($messages),
        'suggested_questions' => [
            'Como transformo isto numa resposta de exame?',
            'Que artigos devo confirmar?',
            'Podes criar um caso prático sobre este tema?',
        ],
    ];
}

function sendLegalAssistantMessage(?int $userId, int $threadId, string $content): array
{
    $content = trim($content);
    if ($content === '') {
        throw new RuntimeException('Escreve uma pergunta antes de enviar.');
    }
    assertAiQuota($userId, 'assistant_message');

    $thread = getLegalAssistantThread($userId, $threadId);
    if (!$thread) {
        $thread = createLegalAssistantThread($userId);
        $threadId = (int)$thread['id'];
    }

    appendLegalAssistantMessage($userId, $threadId, 'user', $content);
    $thread = getLegalAssistantThread($userId, $threadId) ?? $thread;
    $reply = generateLegalAssistantReply($thread, $content);
    appendLegalAssistantMessage($userId, $threadId, 'assistant', $reply['answer']);
    recordAiUsage($userId, 'assistant_message');

    $currentTitle = (string)($thread['title'] ?? 'Nova conversa');
    $messageCount = count($thread['messages'] ?? []);
    updateLegalAssistantThreadMeta($userId, $threadId, [
        'title' => ($currentTitle === 'Nova conversa' || $messageCount <= 1) ? $reply['title'] : $currentTitle,
        'area' => $reply['area'],
        'style_notes' => $reply['style_notes'],
    ]);
    saveLegalAssistantProfile($userId, $reply['style_notes']);

    return getLegalAssistantThread($userId, $threadId) ?? $thread;
}

function legalDraftUsesDatabase(?int $userId): bool
{
    return $userId !== null && tryDB() instanceof PDO && dbSchemaIsReady();
}

function ensureLegalDraftSession(): void
{
    if (!isset($_SESSION['legal_drafts']) || !is_array($_SESSION['legal_drafts'])) {
        $_SESSION['legal_drafts'] = [];
    }

    if (!isset($_SESSION['legal_draft_next_id'])) {
        $_SESSION['legal_draft_next_id'] = 1;
    }
}

function legalDraftTypes(): array
{
    return [
        'Petição inicial',
        'Contestação',
        'Recurso',
        'Requerimento',
        'Parecer jurídico',
        'Contrato simples',
        'Carta formal',
    ];
}

function normalizeLegalDraft(array $draft, string $pieceType, string $area, string $facts, string $goal): array
{
    $sections = $draft['sections'] ?? [];
    if (!is_array($sections)) {
        $sections = [];
    }

    $sections = array_values(array_filter(array_map(static function ($section): array {
        if (is_string($section)) {
            return ['heading' => 'Secção', 'content' => $section];
        }

        if (!is_array($section)) {
            return [];
        }

        return [
            'heading' => trim((string)($section['heading'] ?? $section['title'] ?? 'Secção')),
            'content' => trim((string)($section['content'] ?? $section['text'] ?? '')),
        ];
    }, $sections), static fn(array $section): bool => ($section['content'] ?? '') !== ''));

    if (!$sections) {
        $sections = localLegalDraft($pieceType, $area, $facts, $goal)['sections'];
    }

    foreach (['arguments', 'evidence', 'risks', 'next_steps'] as $key) {
        if (!isset($draft[$key]) || !is_array($draft[$key])) {
            $draft[$key] = [];
        }
        $draft[$key] = array_values(array_filter(array_map('strval', $draft[$key])));
    }

    return [
        'title' => trim((string)($draft['title'] ?? $pieceType . ' - ' . $area)) ?: $pieceType,
        'piece_type' => $pieceType,
        'area' => trim((string)($draft['area'] ?? $area)) ?: 'Geral',
        'goal' => $goal,
        'summary' => trim((string)($draft['summary'] ?? 'Estrutura de treino para escrita jurídica.')),
        'sections' => $sections,
        'arguments' => $draft['arguments'],
        'evidence' => $draft['evidence'],
        'risks' => $draft['risks'],
        'next_steps' => $draft['next_steps'],
        'disclaimer' => trim((string)($draft['disclaimer'] ?? 'Material de estudo. Deve ser revisto com legislação, prazos e prova atualizados.')),
    ];
}

function localLegalDraft(string $pieceType, string $area, string $facts, string $goal): array
{
    $facts = normalizeWhitespace($facts);
    $goal = trim($goal) !== '' ? trim($goal) : 'Definir uma posição jurídica clara.';
    $area = trim($area) !== '' ? trim($area) : detectLegalArea($facts . ' ' . $goal);

    return [
        'title' => $pieceType . ' - ' . $area,
        'piece_type' => $pieceType,
        'area' => $area,
        'goal' => $goal,
        'summary' => 'Rascunho estrutural criado em modo local.',
        'sections' => [
            [
                'heading' => '1. Identificação e enquadramento',
                'content' => 'Identifica partes, tribunal ou destinatário, relação jurídica e objetivo do pedido.',
            ],
            [
                'heading' => '2. Factos relevantes',
                'content' => 'Organiza os factos por ordem cronológica. Distingue factos provados, factos a provar e conclusões.',
            ],
            [
                'heading' => '3. Direito aplicável',
                'content' => 'Indica normas, requisitos legais e jurisprudência útil. Confirma artigos e prazos antes de usar.',
            ],
            [
                'heading' => '4. Aplicação aos factos',
                'content' => 'Aplica cada requisito legal aos factos. Mostra onde a prova suporta cada ponto.',
            ],
            [
                'heading' => '5. Pedido ou conclusão',
                'content' => 'Formula o pedido de forma precisa, com consequências jurídicas e eventual prova requerida.',
            ],
        ],
        'arguments' => [
            'Transformar cada facto importante num ponto jurídico.',
            'Separar normas de conclusões.',
            'Antecipar a resposta da parte contrária.',
        ],
        'evidence' => [
            'Documentos relevantes',
            'Testemunhas',
            'Comunicações escritas',
            'Datas e comprovativos',
        ],
        'risks' => [
            'Prazos processuais por confirmar.',
            'Artigos legais devem ser verificados na versão atual.',
            'Falta de prova pode enfraquecer a tese.',
        ],
        'next_steps' => [
            'Confirmar legislação aplicável.',
            'Completar factos em falta.',
            'Transformar a estrutura em texto final.',
        ],
        'disclaimer' => 'Material de estudo. Não substitui revisão por advogado nem consulta de legislação atualizada.',
    ];
}

function generateLegalDraft(string $pieceType, string $area, string $facts, string $goal): array
{
    $pieceType = in_array($pieceType, legalDraftTypes(), true) ? $pieceType : 'Parecer jurídico';
    $area = trim($area) !== '' ? trim($area) : detectLegalArea($facts . ' ' . $goal);
    $facts = trim($facts);
    $goal = trim($goal);

    if (mb_strlen($facts, 'UTF-8') < 20) {
        throw new RuntimeException('Escreve factos suficientes para criar uma peça útil.');
    }

    $systemInstruction = 'És um professor de prática jurídica em Portugal. Cria estruturas de peças para estudo, com rigor, português europeu, e sem inventar artigos específicos quando não forem fornecidos. Não dês aconselhamento jurídico definitivo.';
    $prompt = "Cria uma estrutura de peça jurídica para treino. Devolve apenas JSON válido com: title, area, summary, sections [{heading, content}], arguments, evidence, risks, next_steps, disclaimer.\n\nTipo de peça: {$pieceType}\nÁrea: {$area}\nObjetivo: {$goal}\nFactos:\n" . mb_substr($facts, 0, AI_MAX_SOURCE_CHARS, 'UTF-8');

    $decoded = decodeAiJson(callGeminiText($prompt, null, $systemInstruction));
    if ($decoded) {
        $draft = normalizeLegalDraft($decoded, $pieceType, $area, $facts, $goal);
        $draft['_mode'] = 'inteligencia';
        return $draft;
    }

    $draft = localLegalDraft($pieceType, $area, $facts, $goal);
    $draft['_mode'] = 'local';
    return normalizeLegalDraft($draft, $pieceType, $area, $facts, $goal) + ['_mode' => 'local'];
}

function saveLegalDraft(?int $userId, string $pieceType, string $area, string $facts, string $goal, array $draft): int
{
    if (legalDraftUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('INSERT INTO legal_drafts (user_id, title, piece_type, area, goal, facts, draft_json, ai_mode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId,
            $draft['title'],
            $pieceType,
            $draft['area'] ?? $area,
            $goal,
            $facts,
            json_encode($draft, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $draft['_mode'] ?? 'local',
        ]);
        return (int)getDB()->lastInsertId();
    }

    ensureLegalDraftSession();
    $id = (int)$_SESSION['legal_draft_next_id'];
    $_SESSION['legal_draft_next_id'] = $id + 1;
    $_SESSION['legal_drafts'][$id] = [
        'id' => $id,
        'title' => $draft['title'],
        'piece_type' => $pieceType,
        'area' => $draft['area'] ?? $area,
        'goal' => $goal,
        'facts' => $facts,
        'draft' => $draft,
        'ai_mode' => $draft['_mode'] ?? 'local',
        'created_at' => date('Y-m-d H:i:s'),
    ];

    return $id;
}

function getLegalDrafts(?int $userId, int $limit = 12): array
{
    if (legalDraftUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT id, title, piece_type, area, ai_mode, created_at FROM legal_drafts WHERE user_id = ? ORDER BY id DESC LIMIT ' . max(1, $limit));
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    ensureLegalDraftSession();
    $drafts = array_values($_SESSION['legal_drafts']);
    usort($drafts, static fn(array $a, array $b): int => (int)$b['id'] <=> (int)$a['id']);
    return array_slice($drafts, 0, $limit);
}

function getLegalDraft(?int $userId, int $draftId): ?array
{
    if (legalDraftUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT * FROM legal_drafts WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$draftId, $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $row['draft'] = decodeAiJson($row['draft_json']) ?? [];
        return $row;
    }

    ensureLegalDraftSession();
    return $_SESSION['legal_drafts'][$draftId] ?? null;
}

function legalConceptUsesDatabase(?int $userId): bool
{
    return $userId !== null && tryDB() instanceof PDO && dbSchemaIsReady();
}

function ensureLegalConceptSession(): void
{
    if (!isset($_SESSION['legal_concepts']) || !is_array($_SESSION['legal_concepts'])) {
        $_SESSION['legal_concepts'] = [];
    }

    if (!isset($_SESSION['legal_concept_next_id'])) {
        $_SESSION['legal_concept_next_id'] = 1;
    }
}

function cleanLegalConceptText(string $text): string
{
    $text = str_replace(['**', '__', '*', '_', '`'], '', $text);
    $text = preg_replace('/\s+/', ' ', $text) ?? $text;
    return trim($text);
}

function normalizeLegalConcept(array $concept, string $term, string $area): array
{
    foreach (['requirements', 'examples', 'common_mistakes', 'related_terms'] as $key) {
        if (!isset($concept[$key]) || !is_array($concept[$key])) {
            $concept[$key] = [];
        }
        $concept[$key] = array_values(array_filter(array_map(
            static fn($item): string => cleanLegalConceptText((string)$item),
            $concept[$key]
        )));
    }

    return [
        'term' => cleanLegalConceptText((string)($concept['term'] ?? $term)) ?: $term,
        'area' => cleanLegalConceptText((string)($concept['area'] ?? $area)) ?: 'Geral',
        'short_definition' => cleanLegalConceptText((string)($concept['short_definition'] ?? 'Conceito jurídico a rever.')),
        'plain_explanation' => cleanLegalConceptText((string)($concept['plain_explanation'] ?? 'Explica o conceito pelos seus elementos e pela aplicação a factos.')),
        'requirements' => $concept['requirements'],
        'examples' => $concept['examples'],
        'common_mistakes' => $concept['common_mistakes'],
        'related_terms' => $concept['related_terms'],
        'memory_hook' => cleanLegalConceptText((string)($concept['memory_hook'] ?? 'Identifica conceito, requisitos, aplicação e conclusão.')),
        'review_question' => cleanLegalConceptText((string)($concept['review_question'] ?? 'Consegues aplicar este conceito a um caso prático?')),
    ];
}

function localLegalConcept(string $term, string $area): array
{
    $area = trim($area) !== '' ? trim($area) : detectLegalArea($term);
    $lower = mb_strtolower($term, 'UTF-8');

    $definition = 'Conceito jurídico que deve ser compreendido pelos seus requisitos, função e aplicação prática.';
    if (str_contains($lower, 'responsabilidade civil')) {
        $definition = 'Obrigação de reparar danos causados a outra pessoa, por incumprimento contratual ou violação de dever jurídico.';
    } elseif (str_contains($lower, 'estado de necessidade')) {
        $definition = 'Situação em que um facto pode deixar de ser ilícito por ser meio adequado para afastar perigo atual, dentro dos requisitos legais.';
    } elseif (str_contains($lower, 'inconstitucionalidade')) {
        $definition = 'Desconformidade de uma norma ou omissão com a Constituição, sujeita a fiscalização nos termos constitucionais.';
    }

    return normalizeLegalConcept([
        'term' => $term,
        'area' => $area,
        'short_definition' => $definition,
        'plain_explanation' => 'Para estudar este conceito, começa pela função, depois lista requisitos, exceções e efeitos jurídicos.',
        'requirements' => ['Identificar a norma aplicável', 'Separar requisitos cumulativos e alternativos', 'Aplicar cada requisito aos factos'],
        'examples' => ['Cria um caso simples e verifica se todos os requisitos estão preenchidos.'],
        'common_mistakes' => ['Decorar a definição sem aplicar aos factos', 'Ignorar exceções', 'Concluir sem justificar'],
        'related_terms' => [$area, 'requisitos', 'ónus da prova'],
        'memory_hook' => 'Definição curta, requisitos, aplicação, conclusão.',
        'review_question' => 'Que factos precisas de provar para aplicar este conceito?',
    ], $term, $area);
}

function generateLegalConcept(string $term, string $area): array
{
    $term = trim($term);
    $area = trim($area);
    if (mb_strlen($term, 'UTF-8') < 3) {
        throw new RuntimeException('Escreve um conceito jurídico para pesquisar.');
    }

    $area = $area !== '' ? $area : detectLegalArea($term);
    $systemInstruction = 'És um professor de Direito em Portugal. Explica conceitos jurídicos com rigor, português europeu, foco em estudo e aplicação a casos práticos. Não inventes artigos específicos quando não tiveres certeza.';
    $prompt = "Explica este conceito jurídico para estudo. Devolve apenas JSON válido com: term, area, short_definition, plain_explanation, requirements, examples, common_mistakes, related_terms, memory_hook, review_question.\n\nConceito: {$term}\nÁrea: {$area}";

    $decoded = decodeAiJson(callGeminiText($prompt, null, $systemInstruction));
    if ($decoded) {
        $concept = normalizeLegalConcept($decoded, $term, $area);
        $concept['_mode'] = 'inteligencia';
        return $concept;
    }

    $concept = localLegalConcept($term, $area);
    $concept['_mode'] = 'local';
    return $concept;
}

function saveLegalConcept(?int $userId, array $concept): int
{
    if (legalConceptUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('INSERT INTO legal_concepts (user_id, term, area, concept_json, ai_mode) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId,
            $concept['term'],
            $concept['area'],
            json_encode($concept, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $concept['_mode'] ?? 'local',
        ]);
        return (int)getDB()->lastInsertId();
    }

    ensureLegalConceptSession();
    $id = (int)$_SESSION['legal_concept_next_id'];
    $_SESSION['legal_concept_next_id'] = $id + 1;
    $_SESSION['legal_concepts'][$id] = [
        'id' => $id,
        'term' => $concept['term'],
        'area' => $concept['area'],
        'concept' => $concept,
        'ai_mode' => $concept['_mode'] ?? 'local',
        'created_at' => date('Y-m-d H:i:s'),
    ];
    return $id;
}

function getLegalConcepts(?int $userId, int $limit = 16): array
{
    if (legalConceptUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT id, term, area, ai_mode, created_at FROM legal_concepts WHERE user_id = ? ORDER BY id DESC LIMIT ' . max(1, $limit));
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    ensureLegalConceptSession();
    $items = array_values($_SESSION['legal_concepts']);
    usort($items, static fn(array $a, array $b): int => (int)$b['id'] <=> (int)$a['id']);
    return array_slice($items, 0, $limit);
}

function getLegalConcept(?int $userId, int $conceptId): ?array
{
    if (legalConceptUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT * FROM legal_concepts WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$conceptId, $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $row['concept'] = decodeAiJson($row['concept_json']) ?? [];
        return $row;
    }

    ensureLegalConceptSession();
    return $_SESSION['legal_concepts'][$conceptId] ?? null;
}

function legalResearchUsesDatabase(?int $userId): bool
{
    return $userId !== null && tryDB() instanceof PDO && dbSchemaIsReady();
}

function ensureLegalResearchSession(): void
{
    if (!isset($_SESSION['legal_research_guides']) || !is_array($_SESSION['legal_research_guides'])) {
        $_SESSION['legal_research_guides'] = [];
    }

    if (!isset($_SESSION['legal_research_next_id'])) {
        $_SESSION['legal_research_next_id'] = 1;
    }
}

function legalResearchAreas(): array
{
    return [
        'Geral',
        'Direito Civil',
        'Direito Penal',
        'Direito Constitucional',
        'Direito do Trabalho',
        'Processo Civil',
        'Processo Penal',
        'Direito Administrativo',
        'Direito da União Europeia',
    ];
}

function legalResearchObjectives(): array
{
    return [
        'Aula do zero',
        'Caso prático',
        'Acórdão',
        'Peça jurídica',
        'Exame',
        'Revisão rápida',
    ];
}

function normalizeResearchList(mixed $items): array
{
    if (is_string($items)) {
        $items = preg_split('/\r?\n|;|•/u', $items) ?: [$items];
    }

    if (!is_array($items)) {
        return [];
    }

    return array_values(array_filter(array_map(static function ($item): string {
        if (is_array($item)) {
            $item = implode(' ', array_map('strval', $item));
        }

        return cleanLegalConceptText((string)$item);
    }, $items), static fn(string $item): bool => $item !== ''));
}

function normalizeResearchTracks(mixed $tracks): array
{
    if (!is_array($tracks)) {
        return [];
    }

    $normalized = [];
    foreach ($tracks as $track) {
        if (is_string($track)) {
            $normalized[] = [
                'name' => cleanLegalConceptText($track),
                'steps' => [],
            ];
            continue;
        }

        if (!is_array($track)) {
            continue;
        }

        $name = cleanLegalConceptText((string)($track['name'] ?? $track['title'] ?? $track['thesis'] ?? 'Linha de argumento'));
        $steps = normalizeResearchList($track['steps'] ?? $track['points'] ?? $track['items'] ?? []);
        if ($name !== '') {
            $normalized[] = [
                'name' => $name,
                'steps' => $steps,
            ];
        }
    }

    return array_slice($normalized, 0, 4);
}

function normalizeLegalResearchGuide(array $guide, string $topic, string $area, string $objective): array
{
    $topic = cleanLegalConceptText($topic);
    $area = cleanLegalConceptText((string)($guide['area'] ?? $area)) ?: 'Geral';
    $objective = cleanLegalConceptText((string)($guide['objective'] ?? $objective)) ?: 'Estudo';

    $normalized = [
        'title' => cleanLegalConceptText((string)($guide['title'] ?? 'Pesquisa - ' . $topic)) ?: 'Pesquisa jurídica',
        'topic' => cleanLegalConceptText((string)($guide['topic'] ?? $topic)) ?: $topic,
        'area' => $area,
        'objective' => $objective,
        'summary' => cleanLegalConceptText((string)($guide['summary'] ?? 'Roteiro de pesquisa jurídica para organizar o estudo.')),
        'opening_question' => cleanLegalConceptText((string)($guide['opening_question'] ?? 'Que problema jurídico preciso de resolver?')),
        'concepts' => normalizeResearchList($guide['concepts'] ?? []),
        'legal_sources' => normalizeResearchList($guide['legal_sources'] ?? []),
        'research_questions' => normalizeResearchList($guide['research_questions'] ?? []),
        'case_law_targets' => normalizeResearchList($guide['case_law_targets'] ?? []),
        'search_queries' => normalizeResearchList($guide['search_queries'] ?? []),
        'argument_tracks' => normalizeResearchTracks($guide['argument_tracks'] ?? []),
        'study_tasks' => normalizeResearchList($guide['study_tasks'] ?? []),
        'common_mistakes' => normalizeResearchList($guide['common_mistakes'] ?? []),
        'next_step_prompt' => cleanLegalConceptText((string)($guide['next_step_prompt'] ?? 'Transforma esta pesquisa numa resposta IRAC: questão, regra, aplicação e conclusão.')),
        'disclaimer' => cleanLegalConceptText((string)($guide['disclaimer'] ?? 'Material de estudo. Confirma sempre legislação, prazos e jurisprudência em fonte oficial.')),
    ];

    $fallback = localLegalResearchGuide($topic, $area, $objective);
    foreach (['concepts', 'legal_sources', 'research_questions', 'case_law_targets', 'search_queries', 'argument_tracks', 'study_tasks', 'common_mistakes'] as $key) {
        if (!$normalized[$key]) {
            $normalized[$key] = $fallback[$key] ?? [];
        }
    }

    return $normalized;
}

function localLegalResearchGuide(string $topic, string $area, string $objective): array
{
    $topic = cleanLegalConceptText($topic);
    $area = cleanLegalConceptText($area);
    $objective = cleanLegalConceptText($objective);
    if ($area === '' || $area === 'Geral') {
        $area = detectLegalArea($topic);
    }
    if ($area === '') {
        $area = 'Geral';
    }
    if ($objective === '') {
        $objective = 'Estudo';
    }

    $profiles = [
        'Direito Penal' => [
            'concepts' => ['Tipo legal', 'bem jurídico protegido', 'dolo ou negligência', 'ilicitude', 'culpa', 'tentativa', 'comparticipação', 'medida da pena'],
            'sources' => ['Código Penal: parte geral e tipo legal aplicável', 'Código de Processo Penal se houver prova, prazos ou medidas processuais', 'Constituição quando existirem direitos fundamentais em conflito'],
            'questions' => ['Qual é o bem jurídico protegido?', 'Os factos preenchem todos os elementos objetivos e subjetivos?', 'Existe causa de exclusão da ilicitude ou da culpa?', 'A prova chega para sustentar a imputação?'],
        ],
        'Direito Civil' => [
            'concepts' => ['Relação jurídica', 'validade do negócio', 'incumprimento', 'mora', 'dano', 'nexo causal', 'responsabilidade civil', 'ónus da prova'],
            'sources' => ['Código Civil: negócio jurídico, obrigações e responsabilidade civil', 'Regime especial aplicável ao tema, se existir', 'Código de Processo Civil quando a questão envolver prova ou pedido'],
            'questions' => ['Que obrigação foi assumida?', 'Houve incumprimento ou cumprimento defeituoso?', 'Que dano existe e como se prova?', 'Qual é o pedido juridicamente útil?'],
        ],
        'Direito do Trabalho' => [
            'concepts' => ['Contrato de trabalho', 'poder de direção', 'direitos do trabalhador', 'procedimento', 'justa causa', 'ilicitude', 'compensação', 'reintegração'],
            'sources' => ['Código do Trabalho: contrato, deveres, cessação e garantias', 'Instrumento de regulamentação coletiva se existir', 'Código de Processo do Trabalho se houver litígio'],
            'questions' => ['Qual é a relação laboral e que dever foi violado?', 'O procedimento foi cumprido?', 'Existe fundamento material suficiente?', 'Que consequência resulta da ilicitude?'],
        ],
        'Direito Constitucional' => [
            'concepts' => ['Direitos fundamentais', 'reserva de lei', 'proporcionalidade', 'igualdade', 'fiscalização da constitucionalidade', 'separação de poderes'],
            'sources' => ['Constituição da República Portuguesa', 'Lei do Tribunal Constitucional quando houver fiscalização', 'Jurisprudência do Tribunal Constitucional sobre o direito em causa'],
            'questions' => ['Que direito fundamental está em causa?', 'A restrição tem base legal?', 'Passa nos testes de adequação, necessidade e proporcionalidade?', 'Há violação do princípio da igualdade?'],
        ],
        'Processo Civil' => [
            'concepts' => ['Competência', 'legitimidade', 'pedido', 'causa de pedir', 'ónus da prova', 'meios de prova', 'recursos', 'caso julgado'],
            'sources' => ['Código de Processo Civil', 'Código Civil para o direito material', 'Jurisprudência sobre pressupostos processuais e ónus da prova'],
            'questions' => ['O tribunal é competente?', 'As partes têm legitimidade?', 'O pedido está formulado de forma útil?', 'Que factos precisam de prova?'],
        ],
        'Processo Penal' => [
            'concepts' => ['Notícia do crime', 'inquérito', 'acusação', 'instrução', 'prova proibida', 'medidas de coação', 'recursos'],
            'sources' => ['Código de Processo Penal', 'Código Penal para o crime em causa', 'Constituição em matéria de garantias de defesa'],
            'questions' => ['A prova foi obtida legalmente?', 'Que garantias de defesa estão em jogo?', 'A acusação contém todos os factos necessários?', 'Existe fundamento para recurso?'],
        ],
    ];

    $base = $profiles[$area] ?? [
        'concepts' => ['Factos relevantes', 'questão jurídica', 'norma aplicável', 'requisitos', 'ónus da prova', 'tese principal', 'contra-argumento'],
        'sources' => ['Lei aplicável ao tema', 'Jurisprudência em DGSI ou tribunal competente', 'Doutrina introdutória para estabilizar conceitos'],
        'questions' => ['Que factos são juridicamente relevantes?', 'Qual é a norma central?', 'Que requisito pode falhar?', 'Que conclusão é defensável?'],
    ];

    $lower = mb_strtolower($topic, 'UTF-8');
    if (str_contains($lower, 'despedimento')) {
        $base['concepts'] = array_values(array_unique(array_merge(['despedimento ilícito', 'justa causa', 'procedimento disciplinar', 'caducidade de prazos'], $base['concepts'])));
        $base['sources'][] = 'Código do Trabalho: cessação do contrato e impugnação do despedimento';
    } elseif (str_contains($lower, 'furto') || str_contains($lower, 'roubo')) {
        $base['concepts'] = array_values(array_unique(array_merge(['subtração', 'coisa móvel alheia', 'intenção de apropriação', 'qualificação do crime'], $base['concepts'])));
        $base['sources'][] = 'Código Penal: crimes contra o património';
    } elseif (str_contains($lower, 'arrendamento') || str_contains($lower, 'caução')) {
        $base['concepts'] = array_values(array_unique(array_merge(['contrato de arrendamento', 'caução', 'deteriorações', 'restituição', 'ónus da prova'], $base['concepts'])));
        $base['sources'][] = 'Código Civil e NRAU sobre arrendamento urbano';
    } elseif (str_contains($lower, 'inconstitucional')) {
        $base['concepts'] = array_values(array_unique(array_merge(['inconstitucionalidade material', 'fiscalização concreta', 'fiscalização abstrata', 'força obrigatória geral'], $base['concepts'])));
        $base['sources'][] = 'Constituição: fiscalização da constitucionalidade';
    }

    $safeTopic = $topic !== '' ? $topic : 'tema jurídico';

    return [
        'title' => 'Pesquisa - ' . $safeTopic,
        'topic' => $safeTopic,
        'area' => $area,
        'objective' => $objective,
        'summary' => 'Roteiro para deixar de pesquisar às cegas: primeiro conceitos, depois norma, jurisprudência, tese e treino.',
        'opening_question' => 'Qual é o problema jurídico central em "' . $safeTopic . '"?',
        'concepts' => array_slice($base['concepts'], 0, 9),
        'legal_sources' => array_values(array_unique($base['sources'])),
        'research_questions' => array_slice($base['questions'], 0, 6),
        'case_law_targets' => [
            'Pesquisar no DGSI por "' . $safeTopic . '" + área "' . $area . '".',
            'Procurar acórdãos recentes e comparar factos, norma aplicada e decisão.',
            'Separar jurisprudência dominante de decisões dependentes de factos muito específicos.',
        ],
        'search_queries' => [
            $safeTopic . ' ' . $area . ' requisitos',
            $safeTopic . ' jurisprudência DGSI',
            $safeTopic . ' caso prático Direito português',
        ],
        'argument_tracks' => [
            [
                'name' => 'Tese favorável',
                'steps' => ['Escolher os factos que preenchem os requisitos', 'Ligar cada facto a uma fonte', 'Antecipar o principal contra-argumento'],
            ],
            [
                'name' => 'Tese contrária',
                'steps' => ['Identificar requisito fraco', 'Questionar prova ou nexo causal', 'Apontar exceções, prazos ou vícios'],
            ],
        ],
        'study_tasks' => [
            'Escrever uma definição curta de cada conceito-chave.',
            'Fazer uma tabela com norma, requisito, facto e prova.',
            'Ler dois acórdãos e resumir apenas factos, questão, decisão e razão.',
            'Transformar o tema num caso prático de 10 linhas.',
        ],
        'common_mistakes' => [
            'Começar por artigos sem perceber a pergunta jurídica.',
            'Guardar acórdãos sem comparar os factos com o teu caso.',
            'Decorar definições sem testar requisitos em factos concretos.',
            'Concluir antes de tratar contra-argumentos.',
        ],
        'next_step_prompt' => 'Com base nesta pesquisa sobre ' . $safeTopic . ', cria uma resposta de exame em quatro partes: questão, regra, aplicação e conclusão.',
        'disclaimer' => 'Material de estudo. Confirma legislação, prazos e jurisprudência em fonte oficial antes de usar em caso real.',
    ];
}

function generateLegalResearchGuide(string $topic, string $area, string $objective): array
{
    $topic = trim($topic);
    $area = trim($area);
    $objective = trim($objective);

    if (mb_strlen($topic, 'UTF-8') < 4) {
        throw new RuntimeException('Escreve um tema jurídico um pouco mais concreto.');
    }

    if ($area === '' || $area === 'Geral') {
        $area = detectLegalArea($topic);
    }
    if ($area === '') {
        $area = 'Geral';
    }
    if ($objective === '') {
        $objective = 'Estudo';
    }

    $systemInstruction = 'És um professor de metodologia jurídica em Portugal. Ensina o aluno a pesquisar Direito com rigor: conceitos, normas prováveis, jurisprudência, perguntas de investigação, teses e erros comuns. Não inventes artigos específicos quando não houver base. Usa português europeu. Não dês aconselhamento jurídico definitivo.';
    $prompt = "Cria um roteiro de pesquisa jurídica para estudo. Devolve apenas JSON válido com estas chaves: title, topic, area, objective, summary, opening_question, concepts, legal_sources, research_questions, case_law_targets, search_queries, argument_tracks [{name, steps}], study_tasks, common_mistakes, next_step_prompt, disclaimer.\n\nTema: {$topic}\nÁrea: {$area}\nObjetivo: {$objective}";

    $decoded = decodeAiJson(callGeminiText($prompt, null, $systemInstruction));
    if ($decoded) {
        $guide = normalizeLegalResearchGuide($decoded, $topic, $area, $objective);
        $guide['_mode'] = 'inteligencia';
        return $guide;
    }

    $guide = localLegalResearchGuide($topic, $area, $objective);
    $guide['_mode'] = 'local';
    return normalizeLegalResearchGuide($guide, $topic, $area, $objective) + ['_mode' => 'local'];
}

function saveLegalResearchGuide(?int $userId, array $guide): int
{
    if (legalResearchUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('INSERT INTO legal_research_guides (user_id, topic, area, objective, guide_json, ai_mode) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId,
            $guide['topic'],
            $guide['area'],
            $guide['objective'],
            json_encode($guide, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $guide['_mode'] ?? 'local',
        ]);
        return (int)getDB()->lastInsertId();
    }

    ensureLegalResearchSession();
    $id = (int)$_SESSION['legal_research_next_id'];
    $_SESSION['legal_research_next_id'] = $id + 1;
    $_SESSION['legal_research_guides'][$id] = [
        'id' => $id,
        'topic' => $guide['topic'],
        'area' => $guide['area'],
        'objective' => $guide['objective'],
        'guide' => $guide,
        'ai_mode' => $guide['_mode'] ?? 'local',
        'created_at' => date('Y-m-d H:i:s'),
    ];
    return $id;
}

function getLegalResearchGuides(?int $userId, int $limit = 12): array
{
    if (legalResearchUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT id, topic, area, objective, ai_mode, created_at FROM legal_research_guides WHERE user_id = ? ORDER BY id DESC LIMIT ' . max(1, $limit));
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    ensureLegalResearchSession();
    $items = array_values($_SESSION['legal_research_guides']);
    usort($items, static fn(array $a, array $b): int => (int)$b['id'] <=> (int)$a['id']);
    return array_slice($items, 0, $limit);
}

function getLegalResearchGuide(?int $userId, int $guideId): ?array
{
    if (legalResearchUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT * FROM legal_research_guides WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$guideId, $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $row['guide'] = decodeAiJson($row['guide_json']) ?? [];
        return $row;
    }

    ensureLegalResearchSession();
    return $_SESSION['legal_research_guides'][$guideId] ?? null;
}

function legalExamUsesDatabase(?int $userId): bool
{
    return $userId !== null && tryDB() instanceof PDO && dbSchemaIsReady();
}

function ensureLegalExamSession(): void
{
    if (!isset($_SESSION['legal_exam_sessions']) || !is_array($_SESSION['legal_exam_sessions'])) {
        $_SESSION['legal_exam_sessions'] = [];
    }

    if (!isset($_SESSION['legal_exam_next_id'])) {
        $_SESSION['legal_exam_next_id'] = 1;
    }
}

function legalExamDifficulties(): array
{
    return ['Base', 'Médio', 'Difícil'];
}

function legalExamAreas(): array
{
    return array_values(array_unique(array_merge(legalResearchAreas(), ['Misto'])));
}

function normalizeExamRubric(mixed $rubric): array
{
    if (!is_array($rubric)) {
        return [];
    }

    $normalized = [];
    foreach ($rubric as $item) {
        if (is_string($item)) {
            $normalized[] = ['criterion' => cleanLegalConceptText($item), 'max_points' => 4];
            continue;
        }

        if (!is_array($item)) {
            continue;
        }

        $criterion = cleanLegalConceptText((string)($item['criterion'] ?? $item['name'] ?? 'Critério'));
        $maxPoints = max(1, (int)($item['max_points'] ?? $item['points'] ?? 4));
        $comment = cleanLegalConceptText((string)($item['comment'] ?? ''));
        if ($criterion !== '') {
            $normalized[] = [
                'criterion' => $criterion,
                'max_points' => $maxPoints,
                'comment' => $comment,
            ];
        }
    }

    return array_slice($normalized, 0, 8);
}

function normalizeLegalExam(array $exam, string $area, string $difficulty, string $focus): array
{
    $area = cleanLegalConceptText((string)($exam['area'] ?? $area)) ?: 'Geral';
    $difficulty = cleanLegalConceptText((string)($exam['difficulty'] ?? $difficulty)) ?: 'Médio';
    $focus = cleanLegalConceptText((string)($exam['focus'] ?? $focus));

    $normalized = [
        'title' => cleanLegalConceptText((string)($exam['title'] ?? 'Banca de Exame - ' . $area)) ?: 'Banca de Exame',
        'area' => $area,
        'difficulty' => $difficulty,
        'focus' => $focus,
        'time_limit' => max(10, (int)($exam['time_limit'] ?? 25)),
        'scenario' => trim((string)($exam['scenario'] ?? '')),
        'questions' => normalizeResearchList($exam['questions'] ?? []),
        'legal_materials' => normalizeResearchList($exam['legal_materials'] ?? []),
        'rubric' => normalizeExamRubric($exam['rubric'] ?? []),
        'examiner_notes' => normalizeResearchList($exam['examiner_notes'] ?? []),
        'answer_method' => cleanLegalConceptText((string)($exam['answer_method'] ?? 'Responde por IRAC: questão, regra, aplicação e conclusão.')),
    ];

    $fallback = localLegalExam($area, $difficulty, $focus);
    foreach (['scenario', 'questions', 'legal_materials', 'rubric', 'examiner_notes'] as $key) {
        if (!$normalized[$key]) {
            $normalized[$key] = $fallback[$key] ?? [];
        }
    }

    if (!is_string($normalized['scenario']) || trim($normalized['scenario']) === '') {
        $normalized['scenario'] = $fallback['scenario'];
    }

    return $normalized;
}

function localLegalExam(string $area, string $difficulty, string $focus): array
{
    $area = trim($area) !== '' ? trim($area) : 'Geral';
    $difficulty = in_array($difficulty, legalExamDifficulties(), true) ? $difficulty : 'Médio';
    $focus = cleanLegalConceptText($focus);
    $theme = $focus !== '' ? $focus : match ($area) {
        'Direito Penal' => 'furto e estado de necessidade',
        'Direito do Trabalho' => 'despedimento disciplinar',
        'Direito Constitucional' => 'restrição de direitos fundamentais',
        'Direito Civil' => 'responsabilidade civil contratual',
        default => 'responsabilidade jurídica aplicada a factos',
    };

    $scenario = match ($area) {
        'Direito Penal' => 'João, estudante sem rendimentos, entra num supermercado e leva alimentos avaliados em 38 euros sem pagar. É intercetado à saída. Afirma que tinha fome e que pretendia pagar quando recebesse dinheiro. O segurança recupera todos os bens. O Ministério Público pondera acusação por furto.',
        'Direito do Trabalho' => 'Marta trabalha há seis anos numa empresa de tecnologia. Depois de recusar horas extraordinárias não pagas, recebe comunicação de despedimento disciplinar por alegada quebra de confiança. A empresa junta apenas mensagens vagas e não ouviu duas testemunhas indicadas pela trabalhadora.',
        'Direito Constitucional' => 'Uma lei municipal limita manifestações junto a edifícios públicos durante todo o horário laboral, invocando tranquilidade administrativa. Um grupo de estudantes pretende impugnar a medida por violação da liberdade de reunião e manifestação.',
        'Direito Civil' => 'Uma empresa contrata a reparação urgente do sistema elétrico de uma loja. O técnico atrasa-se três dias, a loja fica encerrada e há perda de faturação. O prestador alega que a demora resultou de falta de peças no mercado.',
        default => 'Um conflito jurídico chega ao tribunal com factos parcialmente provados, prova documental incompleta e duas teses opostas. Tens de identificar a questão jurídica, escolher normas prováveis e aplicar requisitos aos factos.',
    };

    if ($focus !== '') {
        $scenario .= ' O foco principal do exame é: ' . $focus . '.';
    }

    return [
        'title' => 'Exame - ' . $theme,
        'area' => $area,
        'difficulty' => $difficulty,
        'focus' => $focus,
        'time_limit' => $difficulty === 'Difícil' ? 35 : ($difficulty === 'Base' ? 18 : 25),
        'scenario' => $scenario,
        'questions' => [
            'Identifica a questão jurídica principal e as questões secundárias.',
            'Indica as normas ou institutos jurídicos prováveis, sem inventar artigos se não tiveres certeza.',
            'Aplica os requisitos aos factos e constrói uma conclusão defensável.',
            'Aponta pelo menos um contra-argumento relevante.',
        ],
        'legal_materials' => [
            'Começa pelos factos juridicamente relevantes.',
            'Usa o método questão, regra, aplicação e conclusão.',
            'Distingue prova, requisito legal e opinião.',
        ],
        'rubric' => [
            ['criterion' => 'Identificação da questão jurídica', 'max_points' => 4, 'comment' => 'O problema deve ser formulado como pergunta jurídica.'],
            ['criterion' => 'Normas e conceitos relevantes', 'max_points' => 4, 'comment' => 'Valoriza-se rigor sem artigos inventados.'],
            ['criterion' => 'Aplicação aos factos', 'max_points' => 6, 'comment' => 'Cada requisito deve ser testado nos factos.'],
            ['criterion' => 'Contra-argumento', 'max_points' => 3, 'comment' => 'A resposta deve prever a tese oposta.'],
            ['criterion' => 'Conclusão e clareza', 'max_points' => 3, 'comment' => 'Conclusão curta, coerente e útil.'],
        ],
        'examiner_notes' => [
            'Não escrevas uma opinião geral sobre justiça; escreve raciocínio jurídico.',
            'Se não souberes o artigo, nomeia o instituto e diz que confirmarias a norma.',
            'Uma boa resposta curta vale mais do que uma resposta longa sem estrutura.',
        ],
        'answer_method' => 'Usa IRAC: questão, regra, aplicação aos factos, conclusão.',
    ];
}

function generateLegalExam(string $area, string $difficulty, string $focus): array
{
    $area = trim($area) !== '' ? trim($area) : 'Geral';
    $difficulty = in_array($difficulty, legalExamDifficulties(), true) ? $difficulty : 'Médio';
    $focus = cleanLegalConceptText($focus);

    $systemInstruction = 'És um professor universitário de Direito em Portugal. Cria enunciados de exame realistas para estudo, com grelha de correção. Não inventes artigos específicos sem necessidade. Usa português europeu.';
    $prompt = "Cria um mini-exame jurídico. Devolve apenas JSON válido com: title, area, difficulty, focus, time_limit, scenario, questions, legal_materials, rubric [{criterion, max_points, comment}], examiner_notes, answer_method.\n\nÁrea: {$area}\nDificuldade: {$difficulty}\nFoco opcional: {$focus}";

    $decoded = decodeAiJson(callGeminiText($prompt, null, $systemInstruction));
    if ($decoded) {
        $exam = normalizeLegalExam($decoded, $area, $difficulty, $focus);
        $exam['_mode'] = 'inteligencia';
        return $exam;
    }

    $exam = localLegalExam($area, $difficulty, $focus);
    $exam['_mode'] = 'local';
    return normalizeLegalExam($exam, $area, $difficulty, $focus) + ['_mode' => 'local'];
}

function saveLegalExamSession(?int $userId, array $exam): int
{
    if (legalExamUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('INSERT INTO legal_exam_sessions (user_id, title, area, difficulty, focus, exam_json, ai_mode) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId,
            $exam['title'],
            $exam['area'],
            $exam['difficulty'],
            $exam['focus'] ?? '',
            json_encode($exam, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $exam['_mode'] ?? 'local',
        ]);
        return (int)getDB()->lastInsertId();
    }

    ensureLegalExamSession();
    $id = (int)$_SESSION['legal_exam_next_id'];
    $_SESSION['legal_exam_next_id'] = $id + 1;
    $_SESSION['legal_exam_sessions'][$id] = [
        'id' => $id,
        'title' => $exam['title'],
        'area' => $exam['area'],
        'difficulty' => $exam['difficulty'],
        'focus' => $exam['focus'] ?? '',
        'exam' => $exam,
        'answer' => null,
        'evaluation' => null,
        'score' => null,
        'ai_mode' => $exam['_mode'] ?? 'local',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'evaluated_at' => null,
    ];
    return $id;
}

function getLegalExamSessions(?int $userId, int $limit = 12): array
{
    if (legalExamUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT id, title, area, difficulty, score, ai_mode, created_at, evaluated_at FROM legal_exam_sessions WHERE user_id = ? ORDER BY id DESC LIMIT ' . max(1, $limit));
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    ensureLegalExamSession();
    $items = array_values($_SESSION['legal_exam_sessions']);
    usort($items, static fn(array $a, array $b): int => (int)$b['id'] <=> (int)$a['id']);
    return array_slice($items, 0, $limit);
}

function getLegalExamSession(?int $userId, int $sessionId): ?array
{
    if (legalExamUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT * FROM legal_exam_sessions WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$sessionId, $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $row['exam'] = decodeAiJson($row['exam_json']) ?? [];
        $row['evaluation'] = decodeAiJson((string)($row['evaluation_json'] ?? '')) ?? null;
        return $row;
    }

    ensureLegalExamSession();
    return $_SESSION['legal_exam_sessions'][$sessionId] ?? null;
}

function normalizeLegalExamEvaluation(array $evaluation, array $exam, string $answer): array
{
    $score = clampScore((int)($evaluation['score'] ?? 0));
    $rubric = $evaluation['rubric'] ?? [];
    if (!is_array($rubric)) {
        $rubric = [];
    }

    $normalizedRubric = [];
    foreach ($rubric as $item) {
        if (!is_array($item)) {
            continue;
        }
        $criterion = cleanLegalConceptText((string)($item['criterion'] ?? 'Critério'));
        $maxPoints = max(1, (int)($item['max_points'] ?? 4));
        $points = max(0, min($maxPoints, (int)($item['points'] ?? 0)));
        $normalizedRubric[] = [
            'criterion' => $criterion,
            'points' => $points,
            'max_points' => $maxPoints,
            'comment' => cleanLegalConceptText((string)($item['comment'] ?? '')),
        ];
    }

    if (!$normalizedRubric) {
        $normalizedRubric = localLegalExamEvaluation($exam, $answer)['rubric'];
    }

    return [
        'score' => $score,
        'grade_label' => cleanLegalConceptText((string)($evaluation['grade_label'] ?? examGradeLabel($score))),
        'strengths' => normalizeResearchList($evaluation['strengths'] ?? []),
        'gaps' => normalizeResearchList($evaluation['gaps'] ?? []),
        'rubric' => $normalizedRubric,
        'model_answer' => trim((string)($evaluation['model_answer'] ?? '')),
        'next_study_tasks' => normalizeResearchList($evaluation['next_study_tasks'] ?? []),
        'flashcard_prompts' => normalizeResearchList($evaluation['flashcard_prompts'] ?? []),
        'examiner_comment' => cleanLegalConceptText((string)($evaluation['examiner_comment'] ?? 'Correção criada para estudo.')),
    ];
}

function examGradeLabel(int $score): string
{
    return match (true) {
        $score >= 18 => 'Excelente',
        $score >= 15 => 'Muito bom',
        $score >= 12 => 'Suficiente sólido',
        $score >= 10 => 'Passa, mas frágil',
        default => 'Insuficiente',
    };
}

function localLegalExamEvaluation(array $exam, string $answer): array
{
    $answer = trim($answer);
    $lower = mb_strtolower($answer, 'UTF-8');
    $plainAnswer = function_exists('iconv') ? (iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $answer) ?: $answer) : $answer;
    $wordCount = str_word_count(strtolower($plainAnswer));
    $rubric = [];
    $scoreTotal = 0;
    $maxTotal = 0;

    $checks = [
        ['question' => ['questão', 'problema', 'jurídic'], 'norm' => ['lei', 'norma', 'código', 'artigo', 'instituto'], 'fact' => ['facto', 'aplica', 'requisito'], 'counter' => ['porém', 'contudo', 'contra', 'oposto', 'defesa'], 'conclusion' => ['conclu', 'assim', 'logo']],
    ][0];

    foreach (($exam['rubric'] ?? []) as $item) {
        $criterion = (string)($item['criterion'] ?? 'Critério');
        $max = max(1, (int)($item['max_points'] ?? 4));
        $criterionLower = mb_strtolower($criterion, 'UTF-8');
        $points = (int)floor($max * .35);

        if (str_contains($criterionLower, 'quest') && arrayAnyContained($lower, $checks['question'])) {
            $points += (int)ceil($max * .35);
        } elseif (str_contains($criterionLower, 'norm') || str_contains($criterionLower, 'conceit')) {
            if (arrayAnyContained($lower, $checks['norm'])) {
                $points += (int)ceil($max * .35);
            }
        } elseif (str_contains($criterionLower, 'fact') || str_contains($criterionLower, 'aplica')) {
            if (arrayAnyContained($lower, $checks['fact'])) {
                $points += (int)ceil($max * .40);
            }
        } elseif (str_contains($criterionLower, 'contra')) {
            if (arrayAnyContained($lower, $checks['counter'])) {
                $points += (int)ceil($max * .35);
            }
        } elseif (str_contains($criterionLower, 'conclus') || str_contains($criterionLower, 'clareza')) {
            if (arrayAnyContained($lower, $checks['conclusion'])) {
                $points += (int)ceil($max * .35);
            }
        }

        if ($wordCount >= 120) {
            $points += 1;
        }
        if ($wordCount < 55) {
            $points = max(0, $points - 2);
        }

        $points = max(0, min($max, $points));
        $scoreTotal += $points;
        $maxTotal += $max;
        $rubric[] = [
            'criterion' => $criterion,
            'points' => $points,
            'max_points' => $max,
            'comment' => $points >= ceil($max * .7) ? 'Tratado com estrutura aceitável.' : 'Precisa de aplicação mais explícita aos factos.',
        ];
    }

    $score = $maxTotal > 0 ? clampScore(($scoreTotal / $maxTotal) * 20) : clampScore($wordCount / 8);
    $gaps = [];
    if (!arrayAnyContained($lower, $checks['question'])) {
        $gaps[] = 'A questão jurídica principal não ficou suficientemente clara.';
    }
    if (!arrayAnyContained($lower, $checks['norm'])) {
        $gaps[] = 'Faltou identificar normas, artigos ou institutos prováveis.';
    }
    if (!arrayAnyContained($lower, $checks['counter'])) {
        $gaps[] = 'Faltou antecipar a tese contrária.';
    }
    if ($wordCount < 90) {
        $gaps[] = 'A resposta está demasiado curta para uma correção segura.';
    }

    return [
        'score' => $score,
        'grade_label' => examGradeLabel($score),
        'strengths' => [
            $wordCount >= 90 ? 'Resposta com desenvolvimento mínimo para correção.' : 'Resposta direta, mas ainda curta.',
            'Tentativa de aplicar o caso ao enunciado.',
        ],
        'gaps' => $gaps ?: ['A resposta precisa de maior densidade jurídica e melhor sequência lógica.'],
        'rubric' => $rubric,
        'model_answer' => "Uma resposta forte começaria por formular a questão jurídica, indicaria o regime provável, aplicaria requisito por requisito aos factos provados, enfrentaria a tese oposta e terminaria com conclusão curta. No caso apresentado, o essencial é não saltar da intuição para a conclusão sem passar pela norma e pelos factos.",
        'next_study_tasks' => [
            'Reescrever a resposta em quatro parágrafos: questão, regra, aplicação, conclusão.',
            'Criar uma tabela com requisitos e factos que os provam ou enfraquecem.',
            'Treinar um contra-argumento de cinco linhas.',
        ],
        'flashcard_prompts' => [
            'Quais são os quatro passos do método IRAC?',
            'Como distinguir facto relevante de conclusão jurídica?',
            'Porque é importante tratar o contra-argumento?',
        ],
        'examiner_comment' => 'Correção local. Serve para treino estrutural; confirma sempre normas concretas.',
    ];
}

function arrayAnyContained(string $haystack, array $needles): bool
{
    foreach ($needles as $needle) {
        if ($needle !== '' && str_contains($haystack, $needle)) {
            return true;
        }
    }

    return false;
}

function evaluateLegalExamAnswer(array $exam, string $answer): array
{
    $answer = trim($answer);
    if (mb_strlen($answer, 'UTF-8') < 40) {
        throw new RuntimeException('Escreve uma resposta mais completa antes de pedir correção.');
    }

    $systemInstruction = 'És um professor corretor de exames de Direito em Portugal. Corrige com rigor, mas em linguagem útil para estudo. Não inventes artigos. Dá nota de 0 a 20 e explica falhas concretas.';
    $prompt = "Corrige esta resposta de exame. Devolve apenas JSON válido com: score, grade_label, strengths, gaps, rubric [{criterion, points, max_points, comment}], model_answer, next_study_tasks, flashcard_prompts, examiner_comment.\n\nEnunciado e grelha:\n" . json_encode($exam, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\nResposta do aluno:\n{$answer}";

    $decoded = decodeAiJson(callGeminiText($prompt, null, $systemInstruction));
    if ($decoded) {
        $evaluation = normalizeLegalExamEvaluation($decoded, $exam, $answer);
        $evaluation['_mode'] = 'inteligencia';
        return $evaluation;
    }

    $evaluation = localLegalExamEvaluation($exam, $answer);
    $evaluation['_mode'] = 'local';
    return $evaluation;
}

function awardLegalExamXp(?int $userId, int $score, string $title): int
{
    if (!$userId || !(tryDB() instanceof PDO) || !dbSchemaIsReady()) {
        return 0;
    }

    try {
        $xp = max(25, min(140, 35 + ($score * 5)));
        $xpStmt = getDB()->prepare('SELECT xp FROM users WHERE id = ?');
        $xpStmt->execute([$userId]);
        $currentXp = (int)($xpStmt->fetch()['xp'] ?? 0);
        getDB()->prepare('UPDATE users SET xp = xp + ?, level = ? WHERE id = ?')->execute([$xp, getLevel($currentXp + $xp), $userId]);
        writeActivityLog($userId, 'exam_completed', 'Banca de Exame: ' . $title, $xp);
        return $xp;
    } catch (Throwable) {
        return 0;
    }
}

function saveLegalExamEvaluation(?int $userId, int $sessionId, string $answer, array $evaluation): int
{
    if (legalExamUsesDatabase($userId)) {
        ensureLearningTables();
        $session = getLegalExamSession($userId, $sessionId);
        if (!$session) {
            throw new RuntimeException('Sessão de exame não encontrada.');
        }
        $wasUnevaluated = empty($session['evaluated_at']);
        $stmt = getDB()->prepare('UPDATE legal_exam_sessions SET answer = ?, evaluation_json = ?, score = ?, evaluated_at = COALESCE(evaluated_at, NOW()), updated_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->execute([
            $answer,
            json_encode($evaluation, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            (int)($evaluation['score'] ?? 0),
            $sessionId,
            $userId,
        ]);

        return $wasUnevaluated ? awardLegalExamXp($userId, (int)($evaluation['score'] ?? 0), (string)($session['title'] ?? 'Exame')) : 0;
    }

    ensureLegalExamSession();
    if (!isset($_SESSION['legal_exam_sessions'][$sessionId])) {
        throw new RuntimeException('Sessão de exame não encontrada.');
    }

    $_SESSION['legal_exam_sessions'][$sessionId]['answer'] = $answer;
    $_SESSION['legal_exam_sessions'][$sessionId]['evaluation'] = $evaluation;
    $_SESSION['legal_exam_sessions'][$sessionId]['score'] = (int)($evaluation['score'] ?? 0);
    $_SESSION['legal_exam_sessions'][$sessionId]['updated_at'] = date('Y-m-d H:i:s');
    $_SESSION['legal_exam_sessions'][$sessionId]['evaluated_at'] = $_SESSION['legal_exam_sessions'][$sessionId]['evaluated_at'] ?? date('Y-m-d H:i:s');
    return 0;
}

function legalThesisUsesDatabase(?int $userId): bool
{
    return $userId !== null && tryDB() instanceof PDO && dbSchemaIsReady();
}

function ensureLegalThesisSession(): void
{
    if (!isset($_SESSION['legal_thesis_labs']) || !is_array($_SESSION['legal_thesis_labs'])) {
        $_SESSION['legal_thesis_labs'] = [];
    }

    if (!isset($_SESSION['legal_thesis_next_id'])) {
        $_SESSION['legal_thesis_next_id'] = 1;
    }
}

function normalizeThesisSide(mixed $side, string $fallbackName): array
{
    if (!is_array($side)) {
        $side = [];
    }

    return [
        'name' => cleanLegalConceptText((string)($side['name'] ?? $fallbackName)) ?: $fallbackName,
        'position' => cleanLegalConceptText((string)($side['position'] ?? 'Tese jurídica defensável.')),
        'arguments' => normalizeResearchList($side['arguments'] ?? []),
        'facts_to_use' => normalizeResearchList($side['facts_to_use'] ?? []),
        'weak_points' => normalizeResearchList($side['weak_points'] ?? []),
        'best_reply' => cleanLegalConceptText((string)($side['best_reply'] ?? 'Responder requisito por requisito, sem fugir aos factos difíceis.')),
    ];
}

function normalizeLegalThesisLab(array $lab, string $topic, string $area, string $facts): array
{
    $topic = cleanLegalConceptText((string)($lab['topic'] ?? $topic)) ?: cleanLegalConceptText($topic);
    $area = cleanLegalConceptText((string)($lab['area'] ?? $area)) ?: detectLegalArea($topic . ' ' . $facts);
    $fallback = localLegalThesisLab($topic, $area, $facts);

    $normalized = [
        'title' => cleanLegalConceptText((string)($lab['title'] ?? 'Laboratório - ' . $topic)) ?: 'Laboratório de Teses',
        'topic' => $topic,
        'area' => $area ?: 'Geral',
        'facts' => trim((string)($lab['facts'] ?? $facts)),
        'issue' => cleanLegalConceptText((string)($lab['issue'] ?? $fallback['issue'])),
        'side_a' => normalizeThesisSide($lab['side_a'] ?? [], 'Tese A'),
        'side_b' => normalizeThesisSide($lab['side_b'] ?? [], 'Tese B'),
        'judge_questions' => normalizeResearchList($lab['judge_questions'] ?? []),
        'winning_conditions' => normalizeResearchList($lab['winning_conditions'] ?? []),
        'study_drills' => normalizeResearchList($lab['study_drills'] ?? []),
        'strategy_note' => cleanLegalConceptText((string)($lab['strategy_note'] ?? $fallback['strategy_note'])),
        'disclaimer' => cleanLegalConceptText((string)($lab['disclaimer'] ?? 'Material de estudo. Confirma legislação e jurisprudência em fonte oficial.')),
    ];

    foreach (['judge_questions', 'winning_conditions', 'study_drills'] as $key) {
        if (!$normalized[$key]) {
            $normalized[$key] = $fallback[$key] ?? [];
        }
    }

    foreach (['side_a', 'side_b'] as $sideKey) {
        foreach (['arguments', 'facts_to_use', 'weak_points'] as $listKey) {
            if (!$normalized[$sideKey][$listKey]) {
                $normalized[$sideKey][$listKey] = $fallback[$sideKey][$listKey] ?? [];
            }
        }
    }

    return $normalized;
}

function localLegalThesisLab(string $topic, string $area, string $facts): array
{
    $topic = cleanLegalConceptText($topic);
    $area = cleanLegalConceptText($area) ?: detectLegalArea($topic . ' ' . $facts);
    $area = $area !== '' ? $area : 'Geral';
    $facts = normalizeWhitespace($facts);
    $safeTopic = $topic !== '' ? $topic : 'problema jurídico';

    $sideA = match ($area) {
        'Direito Penal', 'Processo Penal' => 'Acusação',
        'Direito do Trabalho' => 'Trabalhador',
        'Direito Constitucional' => 'Impugnante',
        default => 'Autor',
    };
    $sideB = match ($area) {
        'Direito Penal', 'Processo Penal' => 'Defesa',
        'Direito do Trabalho' => 'Empregador',
        'Direito Constitucional' => 'Entidade pública',
        default => 'Réu',
    };

    return [
        'title' => 'Laboratório - ' . $safeTopic,
        'topic' => $safeTopic,
        'area' => $area,
        'facts' => $facts,
        'issue' => 'Qual é a tese mais defensável sobre "' . $safeTopic . '" perante estes factos?',
        'side_a' => [
            'name' => $sideA,
            'position' => 'Defender que os requisitos jurídicos principais estão preenchidos.',
            'arguments' => ['Escolher a norma central e ligar cada requisito a um facto.', 'Valorizar prova documental e sequência cronológica.', 'Apresentar uma conclusão simples e útil.'],
            'facts_to_use' => ['Factos que mostram conduta, dano, nexo, culpa ou violação de dever.', 'Datas, comunicações, testemunhas e documentos.'],
            'weak_points' => ['Pode faltar prova de um requisito essencial.', 'A tese contrária pode explorar exceções, culpa ou proporcionalidade.'],
            'best_reply' => 'Mostrar que o requisito fraco é suprido por prova indireta ou por interpretação do regime aplicável.',
        ],
        'side_b' => [
            'name' => $sideB,
            'position' => 'Defender que falta pelo menos um requisito ou que existe exceção relevante.',
            'arguments' => ['Atacar o requisito mais fraco.', 'Separar factos provados de alegações.', 'Invocar proporcionalidade, ausência de culpa, falta de nexo ou vício processual quando fizer sentido.'],
            'facts_to_use' => ['Factos ambíguos, falta de prova, comportamento da outra parte e contexto.', 'Omissões no enunciado que impedem uma conclusão segura.'],
            'weak_points' => ['Não basta negar; é preciso oferecer leitura alternativa dos factos.', 'Uma defesa puramente formal pode falhar se os factos forem fortes.'],
            'best_reply' => 'Transformar dúvida probatória ou exceção num bloqueio concreto à conclusão da outra parte.',
        ],
        'judge_questions' => ['Que requisito decide o caso?', 'Que facto prova esse requisito?', 'Qual é o melhor argumento da parte contrária?', 'A conclusão continuaria igual se um facto mudasse?'],
        'winning_conditions' => ['Tese com norma certa, requisito decisivo e factos bem escolhidos.', 'Resposta que enfrenta o contra-argumento antes da conclusão.', 'Clareza: uma frase final que o juiz consiga copiar para a decisão.'],
        'study_drills' => ['Escrever a tese A em 5 linhas.', 'Escrever a tese B em 5 linhas.', 'Responder a uma pergunta difícil do juiz.', 'Transformar a melhor tese numa resposta de exame IRAC.'],
        'strategy_note' => 'A melhor tese não é a mais agressiva; é a que sobrevive melhor aos factos difíceis.',
        'disclaimer' => 'Material de estudo. Confirma legislação e jurisprudência em fonte oficial.',
    ];
}

function generateLegalThesisLab(string $topic, string $area, string $facts): array
{
    $topic = trim($topic);
    $area = trim($area);
    $facts = trim($facts);
    if (mb_strlen($topic . $facts, 'UTF-8') < 12) {
        throw new RuntimeException('Escreve um tema ou factos suficientes para criar teses.');
    }
    if ($area === '' || $area === 'Geral') {
        $area = detectLegalArea($topic . ' ' . $facts);
    }
    $area = $area !== '' ? $area : 'Geral';

    $systemInstruction = 'És um professor de argumentação jurídica em Portugal. Constrói as duas teses possíveis de um problema jurídico, com argumentos, riscos, factos úteis e perguntas de juiz. Não inventes artigos específicos sem base. Usa português europeu.';
    $prompt = "Cria um laboratório de teses jurídicas. Devolve apenas JSON válido com: title, topic, area, facts, issue, side_a {name, position, arguments, facts_to_use, weak_points, best_reply}, side_b {name, position, arguments, facts_to_use, weak_points, best_reply}, judge_questions, winning_conditions, study_drills, strategy_note, disclaimer.\n\nTema: {$topic}\nÁrea: {$area}\nFactos:\n" . mb_substr($facts, 0, 9000, 'UTF-8');

    $decoded = decodeAiJson(callGeminiText($prompt, null, $systemInstruction));
    if ($decoded) {
        $lab = normalizeLegalThesisLab($decoded, $topic, $area, $facts);
        $lab['_mode'] = 'inteligencia';
        return $lab;
    }

    $lab = localLegalThesisLab($topic, $area, $facts);
    $lab['_mode'] = 'local';
    return normalizeLegalThesisLab($lab, $topic, $area, $facts) + ['_mode' => 'local'];
}

function saveLegalThesisLab(?int $userId, array $lab): int
{
    if (legalThesisUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('INSERT INTO legal_thesis_labs (user_id, topic, area, facts, lab_json, ai_mode) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId,
            $lab['topic'],
            $lab['area'],
            $lab['facts'] ?? '',
            json_encode($lab, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $lab['_mode'] ?? 'local',
        ]);
        return (int)getDB()->lastInsertId();
    }

    ensureLegalThesisSession();
    $id = (int)$_SESSION['legal_thesis_next_id'];
    $_SESSION['legal_thesis_next_id'] = $id + 1;
    $_SESSION['legal_thesis_labs'][$id] = [
        'id' => $id,
        'topic' => $lab['topic'],
        'area' => $lab['area'],
        'lab' => $lab,
        'ai_mode' => $lab['_mode'] ?? 'local',
        'created_at' => date('Y-m-d H:i:s'),
    ];
    return $id;
}

function getLegalThesisLabs(?int $userId, int $limit = 12): array
{
    if (legalThesisUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT id, topic, area, ai_mode, created_at FROM legal_thesis_labs WHERE user_id = ? ORDER BY id DESC LIMIT ' . max(1, $limit));
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    ensureLegalThesisSession();
    $items = array_values($_SESSION['legal_thesis_labs']);
    usort($items, static fn(array $a, array $b): int => (int)$b['id'] <=> (int)$a['id']);
    return array_slice($items, 0, $limit);
}

function getLegalThesisLab(?int $userId, int $labId): ?array
{
    if (legalThesisUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT * FROM legal_thesis_labs WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$labId, $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $row['lab'] = decodeAiJson($row['lab_json']) ?? [];
        return $row;
    }

    ensureLegalThesisSession();
    return $_SESSION['legal_thesis_labs'][$labId] ?? null;
}

function dailyReviewUsesDatabase(?int $userId): bool
{
    return $userId !== null && tryDB() instanceof PDO && dbSchemaIsReady();
}

function ensureDailyReviewSession(): void
{
    if (!isset($_SESSION['daily_study_reviews']) || !is_array($_SESSION['daily_study_reviews'])) {
        $_SESSION['daily_study_reviews'] = [];
    }

    if (!isset($_SESSION['daily_review_next_id'])) {
        $_SESSION['daily_review_next_id'] = 1;
    }
}

function normalizeDailyReviewDate(string $classDate): string
{
    $classDate = trim($classDate);
    if ($classDate === '') {
        return date('Y-m-d');
    }

    $parsed = DateTime::createFromFormat('Y-m-d', $classDate);
    return $parsed ? $parsed->format('Y-m-d') : date('Y-m-d');
}

function normalizeDailyReviewClientBrief(mixed $brief, string $topic): array
{
    if (!is_array($brief)) {
        $brief = [];
    }

    return [
        'plain_explanation' => cleanLegalConceptText((string)($brief['plain_explanation'] ?? 'Explica "' . $topic . '" sem juridiquês: qual é o problema, que regra entra e que consequência pode existir.')),
        'client_warning' => cleanLegalConceptText((string)($brief['client_warning'] ?? 'Não prometas resultado. Explica riscos, documentos necessários e próximos passos.')),
        'useful_example' => cleanLegalConceptText((string)($brief['useful_example'] ?? 'Usa um exemplo simples com uma pessoa, um facto, uma dúvida jurídica e uma consequência provável.')),
    ];
}

function normalizeDailyReviewQuiz(mixed $items, string $topic): array
{
    if (!is_array($items)) {
        return [];
    }

    $quiz = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $question = cleanLegalConceptText((string)($item['question'] ?? ''));
        $answer = cleanLegalConceptText((string)($item['answer'] ?? ''));
        if ($question === '' || $answer === '') {
            continue;
        }

        $quiz[] = [
            'question' => mb_substr($question, 0, 420, 'UTF-8'),
            'answer' => mb_substr($answer, 0, 900, 'UTF-8'),
            'hint' => mb_substr(cleanLegalConceptText((string)($item['hint'] ?? 'Procura conceito, requisito e aplicação.')), 0, 320, 'UTF-8'),
        ];
    }

    if ($quiz) {
        return array_slice($quiz, 0, 6);
    }

    return [
        [
            'question' => 'Explica "' . $topic . '" em 30 segundos.',
            'answer' => 'Define o tema, indica a função jurídica e dá um exemplo curto.',
            'hint' => 'Começa por: isto serve para...',
        ],
        [
            'question' => 'Que requisito ou passo de análise costuma decidir esta matéria?',
            'answer' => 'O requisito decisivo é aquele que liga a regra aos factos. Se não souberes qual é, revê os apontamentos e separa elementos.',
            'hint' => 'Procura a palavra “requisito”, “pressuposto” ou “elemento”.',
        ],
        [
            'question' => 'Que erro de exame deves evitar?',
            'answer' => 'Evita concluir sem aplicar a regra aos factos concretos.',
            'hint' => 'Uma conclusão sem aplicação costuma valer pouco.',
        ],
    ];
}

function localDailyStudyReview(string $discipline, string $topic, string $notes, string $classDate): array
{
    $discipline = cleanLegalConceptText($discipline) ?: detectLegalArea($notes . ' ' . $topic);
    $discipline = $discipline !== '' ? $discipline : 'Geral';
    $topic = cleanLegalConceptText($topic) ?: 'Matéria do dia';
    $classDate = normalizeDailyReviewDate($classDate);
    $notes = normalizeWhitespace($notes);
    $sentences = array_slice(array_values(array_filter(preg_split('/(?<=[.!?])\s+/u', $notes) ?: [])), 0, 5);
    $summary = $sentences ? implode(' ', $sentences) : 'A revisão reúne os pontos principais da aula e transforma-os em treino ativo.';
    $conceptSeed = array_slice(array_values(array_filter(preg_split('/[,.;:\n]+/u', $topic . '. ' . $notes) ?: [])), 0, 8);
    $concepts = [];

    foreach ($conceptSeed as $item) {
        $clean = cleanLegalConceptText($item);
        if (mb_strlen($clean, 'UTF-8') >= 4 && mb_strlen($clean, 'UTF-8') <= 90) {
            $concepts[] = $clean;
        }
    }

    $concepts = array_values(array_unique(array_slice($concepts, 0, 5))) ?: [$topic, $discipline, 'Factos relevantes', 'Norma aplicável'];

    return [
        'title' => 'Revisão - ' . $topic,
        'discipline' => $discipline,
        'topic' => $topic,
        'class_date' => $classDate,
        'summary' => mb_substr($summary, 0, 760, 'UTF-8'),
        'professor_explanation' => 'A forma certa de rever esta aula é separar conceito, função, requisitos e aplicação. Não tentes decorar tudo de uma vez: escolhe dois conceitos nucleares, explica-os em voz alta e aplica-os a um caso simples.',
        'key_concepts' => $concepts,
        'must_review' => [
            'Definição curta de cada conceito.',
            'Requisitos ou elementos que precisam de estar presentes.',
            'Exemplo prático ligado aos apontamentos.',
            'Dúvidas para confirmar com professor, manual ou fonte oficial.',
        ],
        'confusion_flags' => [
            'Misturar opinião pessoal com conclusão jurídica.',
            'Citar uma regra sem explicar o requisito que ela resolve.',
            'Saltar dos factos diretamente para a resposta final.',
        ],
        'active_questions' => [
            'Qual foi a ideia mais importante desta aula?',
            'Que conceito ainda não consegues explicar em 30 segundos?',
            'Que facto mudaria a solução de um caso prático sobre este tema?',
            'Que pergunta farias ao professor na próxima aula?',
        ],
        'mini_case' => [
            'scenario' => 'Um aluno recebe um caso curto sobre "' . $topic . '" e tem de identificar a questão jurídica antes de escolher a solução.',
            'task' => 'Escreve em 6 linhas: factos relevantes, problema jurídico, regra provável, aplicação e conclusão provisória.',
        ],
        'client_brief' => normalizeDailyReviewClientBrief([], $topic),
        'quick_quiz' => normalizeDailyReviewQuiz([], $topic),
        'flashcards' => normalizeGeneratedFlashcards([
            [
                'front' => 'Qual é a ideia central de "' . $topic . '"?',
                'back' => 'Explica a função do tema, os requisitos principais e um exemplo prático retirado da aula.',
                'difficulty' => 'Fácil',
                'article_ref' => $discipline,
            ],
            [
                'front' => 'Que passos usas para aplicar "' . $topic . '" a um caso?',
                'back' => 'Identificar factos relevantes, escolher a norma ou princípio, testar requisitos e fechar com conclusão.',
                'difficulty' => 'Médio',
                'article_ref' => null,
            ],
            [
                'front' => 'Que erro de exame deves evitar nesta matéria?',
                'back' => 'Não concluir sem passar pelos requisitos e sem ligar cada requisito a um facto concreto.',
                'difficulty' => 'Difícil',
                'article_ref' => null,
            ],
        ]),
        'review_plan' => [
            '5 min: ler o resumo e sublinhar uma dúvida.',
            '8 min: responder às perguntas ativas sem olhar.',
            '10 min: resolver o mini-caso em estrutura IRAC.',
            '5 min: criar ou rever flashcards.',
        ],
        'next_session_prompt' => 'Explica-me ' . $topic . ' como professor de Direito e depois faz-me 5 perguntas de revisão.',
        'confidence_check' => 'Se não consegues explicar o tema em 60 segundos, ainda estás em leitura passiva.',
        'disclaimer' => 'Material de estudo. Confirma legislação, apontamentos e fontes oficiais.',
    ];
}

function normalizeDailyStudyReview(array $review, string $discipline, string $topic, string $notes, string $classDate): array
{
    $discipline = cleanLegalConceptText((string)($review['discipline'] ?? $discipline)) ?: 'Geral';
    $topic = cleanLegalConceptText((string)($review['topic'] ?? $topic)) ?: 'Matéria do dia';
    $classDate = normalizeDailyReviewDate((string)($review['class_date'] ?? $classDate));
    $fallback = localDailyStudyReview($discipline, $topic, $notes, $classDate);
    $miniCase = $review['mini_case'] ?? [];
    if (!is_array($miniCase)) {
        $miniCase = [];
    }

    $normalized = [
        'title' => cleanLegalConceptText((string)($review['title'] ?? ('Revisão - ' . $topic))) ?: ('Revisão - ' . $topic),
        'discipline' => $discipline,
        'topic' => $topic,
        'class_date' => $classDate,
        'summary' => cleanLegalConceptText((string)($review['summary'] ?? $fallback['summary'])),
        'professor_explanation' => cleanLegalConceptText((string)($review['professor_explanation'] ?? $fallback['professor_explanation'])),
        'key_concepts' => normalizeResearchList($review['key_concepts'] ?? []),
        'must_review' => normalizeResearchList($review['must_review'] ?? []),
        'confusion_flags' => normalizeResearchList($review['confusion_flags'] ?? []),
        'active_questions' => normalizeResearchList($review['active_questions'] ?? []),
        'mini_case' => [
            'scenario' => cleanLegalConceptText((string)($miniCase['scenario'] ?? $fallback['mini_case']['scenario'])),
            'task' => cleanLegalConceptText((string)($miniCase['task'] ?? $fallback['mini_case']['task'])),
        ],
        'client_brief' => normalizeDailyReviewClientBrief($review['client_brief'] ?? [], $topic),
        'quick_quiz' => normalizeDailyReviewQuiz($review['quick_quiz'] ?? [], $topic),
        'flashcards' => normalizeGeneratedFlashcards((array)($review['flashcards'] ?? [])),
        'review_plan' => normalizeResearchList($review['review_plan'] ?? []),
        'next_session_prompt' => cleanLegalConceptText((string)($review['next_session_prompt'] ?? $fallback['next_session_prompt'])),
        'confidence_check' => cleanLegalConceptText((string)($review['confidence_check'] ?? $fallback['confidence_check'])),
        'disclaimer' => cleanLegalConceptText((string)($review['disclaimer'] ?? 'Material de estudo. Confirma legislação, apontamentos e fontes oficiais.')),
    ];

    foreach (['key_concepts', 'must_review', 'confusion_flags', 'active_questions', 'review_plan'] as $key) {
        if (!$normalized[$key]) {
            $normalized[$key] = $fallback[$key] ?? [];
        }
    }

    if (!$normalized['flashcards']) {
        $normalized['flashcards'] = $fallback['flashcards'];
    }

    if (!$normalized['quick_quiz']) {
        $normalized['quick_quiz'] = $fallback['quick_quiz'];
    }

    return $normalized;
}

function generateDailyStudyReview(string $discipline, string $topic, string $notes, string $classDate): array
{
    $discipline = trim($discipline) ?: 'Geral';
    $topic = trim($topic) ?: 'Matéria do dia';
    $notes = trim($notes);
    $classDate = normalizeDailyReviewDate($classDate);

    if (mb_strlen($notes . $topic, 'UTF-8') < 30) {
        throw new RuntimeException('Escreve apontamentos suficientes ou indica melhor a matéria da aula.');
    }

    if ($discipline === 'Geral') {
        $detected = detectLegalArea($topic . ' ' . $notes);
        $discipline = $detected !== '' ? $detected : 'Geral';
    }

    $systemInstruction = 'És um professor de Direito em Portugal a preparar uma revisão diária para um aluno no início do percurso jurídico. Sê direto, rigoroso e prático. Não inventes artigos específicos sem base nos apontamentos. Usa português europeu.';
    $prompt = "Cria uma revisão diária de estudo jurídico. Devolve apenas JSON válido com: title, discipline, topic, class_date, summary, professor_explanation, key_concepts, must_review, confusion_flags, active_questions, mini_case {scenario, task}, client_brief {plain_explanation, client_warning, useful_example}, quick_quiz [{question, answer, hint}], flashcards [{front, back, difficulty, article_ref}], review_plan, next_session_prompt, confidence_check, disclaimer.\n\nData da aula: {$classDate}\nDisciplina: {$discipline}\nTema: {$topic}\nApontamentos:\n" . mb_substr($notes, 0, 14000, 'UTF-8');

    $decoded = decodeAiJson(callGeminiText($prompt, null, $systemInstruction));
    if ($decoded) {
        $review = normalizeDailyStudyReview($decoded, $discipline, $topic, $notes, $classDate);
        $review['_mode'] = 'inteligencia';
        return $review;
    }

    $review = localDailyStudyReview($discipline, $topic, $notes, $classDate);
    $review['_mode'] = 'local';
    return normalizeDailyStudyReview($review, $discipline, $topic, $notes, $classDate) + ['_mode' => 'local'];
}

function matterReviewCadence(array $review): array
{
    $topic = (string)($review['topic'] ?? 'matéria');
    $questions = array_values((array)($review['active_questions'] ?? []));
    $firstQuestion = $questions[0] ?? 'Explica a matéria sem olhar para os apontamentos.';

    return [
        [
            'when' => '24h',
            'title' => 'Recuperação curta',
            'task' => 'Responder a uma pergunta ativa e rever 5 flashcards.',
            'output' => $firstQuestion,
        ],
        [
            'when' => '3 dias',
            'title' => 'Aplicação prática',
            'task' => 'Resolver o mini-caso em estrutura de exame.',
            'output' => (string)($review['mini_case']['task'] ?? 'Factos, regra, aplicação e conclusão.'),
        ],
        [
            'when' => '7 dias',
            'title' => 'Consolidação',
            'task' => 'Criar uma resposta modelo e uma dúvida para o professor IA.',
            'output' => 'Tema: ' . $topic,
        ],
    ];
}

function matterSourceContent(array $review, string $notes): string
{
    $lines = [
        '# ' . (string)($review['title'] ?? 'Matéria importada'),
        '',
        'Disciplina: ' . (string)($review['discipline'] ?? 'Geral'),
        'Tema: ' . (string)($review['topic'] ?? 'Matéria do dia'),
        'Data: ' . (string)($review['class_date'] ?? date('Y-m-d')),
        '',
        '## Resumo',
        (string)($review['summary'] ?? ''),
        '',
        '## Explicação do professor',
        (string)($review['professor_explanation'] ?? ''),
        '',
        '## Conceitos-chave',
    ];

    foreach ((array)($review['key_concepts'] ?? []) as $concept) {
        $lines[] = '- ' . (string)$concept;
    }

    $lines[] = '';
    $lines[] = '## O que rever';
    foreach ((array)($review['must_review'] ?? []) as $item) {
        $lines[] = '- ' . (string)$item;
    }

    $lines[] = '';
    $lines[] = '## Perguntas ativas';
    foreach ((array)($review['active_questions'] ?? []) as $question) {
        $lines[] = '- ' . (string)$question;
    }

    $lines[] = '';
    $lines[] = '## Mini-caso';
    $lines[] = (string)($review['mini_case']['scenario'] ?? '');
    $lines[] = (string)($review['mini_case']['task'] ?? '');
    $lines[] = '';
    $lines[] = '## Apontamentos originais';
    $lines[] = mb_substr($notes, 0, 40000, 'UTF-8');

    return implode("\n", $lines);
}

function saveDailyStudyReview(?int $userId, array $review, string $notes): int
{
    if (dailyReviewUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('INSERT INTO daily_study_reviews (user_id, class_date, discipline, topic, raw_notes, review_json, ai_mode) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId,
            $review['class_date'] ?? date('Y-m-d'),
            $review['discipline'],
            $review['topic'],
            mb_substr($notes, 0, 120000, 'UTF-8'),
            json_encode($review, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $review['_mode'] ?? 'local',
        ]);
        return (int)getDB()->lastInsertId();
    }

    ensureDailyReviewSession();
    $id = (int)$_SESSION['daily_review_next_id'];
    $_SESSION['daily_review_next_id'] = $id + 1;
    $_SESSION['daily_study_reviews'][$id] = [
        'id' => $id,
        'class_date' => $review['class_date'] ?? date('Y-m-d'),
        'discipline' => $review['discipline'],
        'topic' => $review['topic'],
        'raw_notes' => $notes,
        'review' => $review,
        'ai_mode' => $review['_mode'] ?? 'local',
        'created_at' => date('Y-m-d H:i:s'),
    ];
    return $id;
}

function getDailyStudyReviews(?int $userId, int $limit = 14): array
{
    if (dailyReviewUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT id, class_date, discipline, topic, ai_mode, created_at FROM daily_study_reviews WHERE user_id = ? ORDER BY class_date DESC, id DESC LIMIT ' . max(1, $limit));
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    ensureDailyReviewSession();
    $items = array_values($_SESSION['daily_study_reviews']);
    usort($items, static fn(array $a, array $b): int => strcmp((string)($b['class_date'] ?? ''), (string)($a['class_date'] ?? '')) ?: ((int)$b['id'] <=> (int)$a['id']));
    return array_slice($items, 0, $limit);
}

function getDailyStudyReview(?int $userId, int $reviewId): ?array
{
    if (dailyReviewUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT * FROM daily_study_reviews WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$reviewId, $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $row['review'] = decodeAiJson((string)$row['review_json']) ?? [];
        return $row;
    }

    ensureDailyReviewSession();
    return $_SESSION['daily_study_reviews'][$reviewId] ?? null;
}

function normalizeDailyReviewEvaluation(array $evaluation, array $review, string $answer): array
{
    $score = (int)($evaluation['score'] ?? 0);
    $score = max(0, min(20, $score));

    return [
        'score' => $score,
        'grade_label' => cleanLegalConceptText((string)($evaluation['grade_label'] ?? ($score >= 14 ? 'Bom caminho' : 'A rever'))),
        'strengths' => normalizeResearchList($evaluation['strengths'] ?? []),
        'gaps' => normalizeResearchList($evaluation['gaps'] ?? []),
        'better_answer' => cleanLegalConceptText((string)($evaluation['better_answer'] ?? 'Reescreve a resposta com factos, problema jurídico, regra, aplicação e conclusão.')),
        'next_drill' => cleanLegalConceptText((string)($evaluation['next_drill'] ?? 'Repetir o mini-caso em 8 linhas, sem olhar para o resumo.')),
        'client_style_tip' => cleanLegalConceptText((string)($evaluation['client_style_tip'] ?? 'Explica a conclusão em linguagem simples, sem prometer resultado.')),
    ];
}

function localDailyReviewEvaluation(array $review, string $answer): array
{
    $answer = normalizeWhitespace($answer);
    $lower = mb_strtolower($answer, 'UTF-8');
    $words = preg_split('/\s+/', trim($answer), -1, PREG_SPLIT_NO_EMPTY);
    $wordCount = is_array($words) ? count($words) : 0;
    $structureHits = 0;
    foreach (['facto', 'problema', 'regra', 'artigo', 'requisito', 'aplica', 'conclu'] as $term) {
        if (str_contains($lower, $term)) {
            $structureHits++;
        }
    }

    $score = min(20, max(5, 6 + min(8, (int)floor($wordCount / 18)) + min(6, $structureHits)));
    $strengths = [];
    $gaps = [];

    if ($wordCount >= 70) {
        $strengths[] = 'Resposta desenvolvida o suficiente para avaliar raciocínio.';
    } else {
        $gaps[] = 'Resposta ainda curta; falta desenvolver aplicação aos factos.';
    }

    if ($structureHits >= 3) {
        $strengths[] = 'Já aparecem sinais de estrutura jurídica.';
    } else {
        $gaps[] = 'Falta organizar por factos, problema, regra, aplicação e conclusão.';
    }

    if (str_contains($lower, 'fact')) {
        $strengths[] = 'Ligaste pelo menos parte da resposta aos factos.';
    } else {
        $gaps[] = 'Quase não há ligação explícita aos factos do mini-caso.';
    }

    return normalizeDailyReviewEvaluation([
        'score' => $score,
        'grade_label' => $score >= 14 ? 'Resposta aproveitável' : 'Resposta incompleta',
        'strengths' => $strengths,
        'gaps' => $gaps,
        'better_answer' => 'Uma resposta mais forte começaria por identificar os factos relevantes, formularia a questão jurídica, indicaria a regra ou princípio provável, aplicaria cada requisito aos factos e terminaria com conclusão provisória.',
        'next_drill' => 'Reescreve a resposta em 8 linhas usando a estrutura: factos, problema, regra, aplicação, conclusão.',
        'client_style_tip' => 'No modo cliente, troca termos técnicos por consequência prática: o que aconteceu, qual é o risco e qual é o próximo passo.',
    ], $review, $answer);
}

function evaluateDailyReviewAnswer(array $review, string $answer): array
{
    $answer = trim($answer);
    if (mb_strlen($answer, 'UTF-8') < 40) {
        throw new RuntimeException('Escreve uma resposta um pouco mais completa antes de pedir correção.');
    }

    $systemInstruction = 'És um professor de Direito em Portugal. Corrige uma resposta curta de mini-caso para um aluno no início do curso. Dá feedback direto, útil e com nota de 0 a 20. Não inventes artigos.';
    $prompt = "Corrige a resposta do aluno à Revisão do Dia. Devolve apenas JSON válido com: score, grade_label, strengths, gaps, better_answer, next_drill, client_style_tip.\n\nRevisão:\n" . json_encode($review, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\nResposta do aluno:\n{$answer}";

    $decoded = decodeAiJson(callGeminiText($prompt, null, $systemInstruction));
    if ($decoded) {
        $evaluation = normalizeDailyReviewEvaluation($decoded, $review, $answer);
        $evaluation['_mode'] = 'inteligencia';
        return $evaluation;
    }

    $evaluation = localDailyReviewEvaluation($review, $answer);
    $evaluation['_mode'] = 'local';
    return $evaluation;
}

function saveDailyReviewAttempt(?int $userId, int $reviewId, string $answer, array $evaluation): int
{
    if (dailyReviewUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('INSERT INTO daily_review_attempts (user_id, review_id, answer, evaluation_json, score, ai_mode) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId,
            $reviewId,
            $answer,
            json_encode($evaluation, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            (int)($evaluation['score'] ?? 0),
            $evaluation['_mode'] ?? 'local',
        ]);
        return (int)getDB()->lastInsertId();
    }

    if (!isset($_SESSION['daily_review_attempts']) || !is_array($_SESSION['daily_review_attempts'])) {
        $_SESSION['daily_review_attempts'] = [];
    }
    if (!isset($_SESSION['daily_review_attempt_next_id'])) {
        $_SESSION['daily_review_attempt_next_id'] = 1;
    }

    $id = (int)$_SESSION['daily_review_attempt_next_id'];
    $_SESSION['daily_review_attempt_next_id'] = $id + 1;
    $_SESSION['daily_review_attempts'][$id] = [
        'id' => $id,
        'review_id' => $reviewId,
        'answer' => $answer,
        'evaluation' => $evaluation,
        'score' => (int)($evaluation['score'] ?? 0),
        'ai_mode' => $evaluation['_mode'] ?? 'local',
        'created_at' => date('Y-m-d H:i:s'),
    ];

    return $id;
}

function getDailyReviewAttempts(?int $userId, int $reviewId, int $limit = 3): array
{
    if (dailyReviewUsesDatabase($userId)) {
        ensureLearningTables();
        $stmt = getDB()->prepare('SELECT * FROM daily_review_attempts WHERE user_id = ? AND review_id = ? ORDER BY id DESC LIMIT ' . max(1, $limit));
        $stmt->execute([$userId, $reviewId]);
        return array_map(static function (array $row): array {
            $row['evaluation'] = decodeAiJson((string)$row['evaluation_json']) ?? [];
            return $row;
        }, $stmt->fetchAll());
    }

    $items = array_values(array_filter(
        (array)($_SESSION['daily_review_attempts'] ?? []),
        static fn(array $item): bool => (int)($item['review_id'] ?? 0) === $reviewId
    ));
    usort($items, static fn(array $a, array $b): int => (int)$b['id'] <=> (int)$a['id']);
    return array_slice($items, 0, $limit);
}

function createDailyReviewFlashcards(int $userId, int $reviewId): array
{
    $record = getDailyStudyReview($userId, $reviewId);
    if (!$record || empty($record['review'])) {
        throw new RuntimeException('Revisão não encontrada.');
    }

    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        throw new RuntimeException('A base de dados não está disponível.');
    }

    ensureFlashcardPersonalColumns($db);

    $existing = $db->prepare(
        "SELECT id, total_cards
         FROM flashcard_decks
         WHERE user_id <=> ? AND source_type = 'daily_review' AND source_id = ? AND active = 1
         LIMIT 1"
    );
    $existing->execute([$userId, $reviewId]);
    $deck = $existing->fetch();

    if ($deck && (int)$deck['total_cards'] > 0) {
        return [
            'deck_id' => (int)$deck['id'],
            'created' => 0,
            'existing' => true,
            'review_id' => $reviewId,
        ];
    }

    $review = normalizeDailyStudyReview((array)$record['review'], (string)($record['discipline'] ?? 'Geral'), (string)($record['topic'] ?? 'Matéria do dia'), (string)($record['raw_notes'] ?? ''), (string)($record['class_date'] ?? date('Y-m-d')));
    $cards = normalizeGeneratedFlashcards((array)($review['flashcards'] ?? []));
    if (!$cards) {
        throw new RuntimeException('Esta revisão não tem cartas úteis para criar.');
    }

    $name = 'Revisão: ' . $review['topic'];
    $description = 'Flashcards gerados a partir da Revisão do Dia.';

    $db->beginTransaction();
    try {
        if ($deck) {
            $deckId = (int)$deck['id'];
            $db->prepare('DELETE FROM flashcards WHERE deck_id = ?')->execute([$deckId]);
        } else {
            $stmt = $db->prepare(
                "INSERT INTO flashcard_decks (user_id, name, description, area, area_key, color, icon, total_cards, active, source_type, source_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1, 'daily_review', ?)"
            );
            $stmt->execute([
                $userId,
                mb_substr($name, 0, 100, 'UTF-8'),
                $description,
                $review['discipline'],
                areaKeyFromArea($review['discipline']),
                '#75d6cf',
                'daily-review',
                $reviewId,
            ]);
            $deckId = (int)$db->lastInsertId();
        }

        $insert = $db->prepare(
            'INSERT INTO flashcards (deck_id, front, back, difficulty, article_ref, order_num, active) VALUES (?, ?, ?, ?, ?, ?, 1)'
        );

        foreach ($cards as $index => $card) {
            $insert->execute([
                $deckId,
                $card['front'],
                $card['back'],
                $card['difficulty'],
                $card['article_ref'],
                $index + 1,
            ]);
        }

        $db->prepare('UPDATE flashcard_decks SET total_cards = ? WHERE id = ?')->execute([count($cards), $deckId]);
        $db->commit();

        return [
            'deck_id' => $deckId,
            'created' => count($cards),
            'existing' => false,
            'review_id' => $reviewId,
        ];
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

function summarizeJudgment(string $text, ?array $inlinePdf = null): array
{
    $prompt = "Analisa este acórdão português e devolve apenas JSON válido com estas chaves: tribunal, processo, data, relator, area_direito, partes, factos_provados, questoes_juridicas, normas_relevantes, decisao, fundamentacao_essencial, ratio_decidendi, conceitos_para_estudar, possiveis_perguntas_exame, resumo_curto. Usa português europeu. Se algum campo não existir, usa null ou lista vazia.\n\nTexto extraído:\n" . mb_substr($text, 0, AI_MAX_SOURCE_CHARS, 'UTF-8');
    $ai = callGeminiText($prompt, $inlinePdf);
    $decoded = decodeAiJson($ai);

    if ($decoded) {
        $decoded['_mode'] = 'gemini';
        return normalizeJudgmentSummary($decoded);
    }

    $summary = localJudgmentSummary($text);
    $summary['_mode'] = 'local';
    return $summary;
}

function normalizeJudgmentSummary(array $summary): array
{
    $defaults = [
        'tribunal' => null,
        'processo' => null,
        'data' => null,
        'relator' => null,
        'area_direito' => null,
        'partes' => [],
        'factos_provados' => [],
        'questoes_juridicas' => [],
        'normas_relevantes' => [],
        'decisao' => null,
        'fundamentacao_essencial' => [],
        'ratio_decidendi' => null,
        'conceitos_para_estudar' => [],
        'possiveis_perguntas_exame' => [],
        'resumo_curto' => null,
        '_mode' => $summary['_mode'] ?? 'local',
    ];

    return array_replace($defaults, array_intersect_key($summary, $defaults));
}

function localJudgmentSummary(string $text): array
{
    $clean = normalizeWhitespace($text);
    $sentences = preg_split('/(?<=[.!?])\s+/u', $clean) ?: [];
    $sentences = array_values(array_filter($sentences, static fn(string $s): bool => mb_strlen($s, 'UTF-8') > 30));
    $lower = mb_strtolower($clean, 'UTF-8');
    preg_match('/processo\s+n[.ºo]*\s*([a-z0-9\/\-.]+)/iu', $clean, $processo);
    preg_match('/relator[:\s]+([A-ZÁÉÍÓÚÂÊÔÃÕÇ][^\n\r]{3,80})/u', $clean, $relator);
    preg_match('/(\d{1,2}[\/\-.]\d{1,2}[\/\-.]\d{2,4})/u', $clean, $date);

    $normas = [];
    if (preg_match_all('/art\.?\s*\d+[.ºº]?(?:\s*[a-z]*)?/iu', $clean, $matches)) {
        $normas = array_values(array_unique(array_slice($matches[0], 0, 12)));
    }

    $area = 'Geral';
    foreach (['penal' => 'Direito Penal', 'trabalho' => 'Direito do Trabalho', 'contrato' => 'Direito Civil', 'constitucional' => 'Direito Constitucional', 'consumidor' => 'Direito do Consumidor'] as $needle => $label) {
        if (str_contains($lower, $needle)) {
            $area = $label;
            break;
        }
    }

    return normalizeJudgmentSummary([
        'tribunal' => str_contains($lower, 'supremo tribunal') ? 'Supremo Tribunal de Justiça' : null,
        'processo' => $processo[1] ?? null,
        'data' => $date[1] ?? null,
        'relator' => isset($relator[1]) ? trim($relator[1]) : null,
        'area_direito' => $area,
        'factos_provados' => array_slice($sentences, 0, 4),
        'questoes_juridicas' => inferStudyQuestions($clean),
        'normas_relevantes' => $normas,
        'decisao' => findDecisionSentence($sentences),
        'fundamentacao_essencial' => array_slice($sentences, 4, 4),
        'ratio_decidendi' => findDecisionSentence($sentences) ?: ($sentences[0] ?? null),
        'conceitos_para_estudar' => inferConcepts($clean),
        'possiveis_perguntas_exame' => [
            'Qual é o problema jurídico central do acórdão?',
            'Que factos foram decisivos para a solução?',
            'Que norma sustenta a decisão?',
        ],
        'resumo_curto' => mb_substr($clean, 0, 700, 'UTF-8'),
    ]);
}

function normalizeWhitespace(string $text): string
{
    $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
    $text = preg_replace('/\s{2,}/u', ' ', $text) ?? $text;
    return trim($text);
}

function inferStudyQuestions(string $text): array
{
    $questions = [];
    foreach (['nulidade', 'responsabilidade', 'culpa', 'ilicitude', 'contrato', 'despedimento', 'furto', 'constitucionalidade'] as $term) {
        if (str_contains(mb_strtolower($text, 'UTF-8'), $term)) {
            $questions[] = 'Como se aplica o conceito de ' . $term . ' neste caso?';
        }
    }

    return array_slice($questions ?: ['Qual foi a questão jurídica essencial?'], 0, 5);
}

function inferConcepts(string $text): array
{
    $concepts = [];
    foreach (['culpa', 'nexo causal', 'dano', 'ilicitude', 'prescrição', 'caducidade', 'legítima defesa', 'estado de necessidade', 'proporcionalidade'] as $term) {
        if (str_contains(mb_strtolower($text, 'UTF-8'), $term)) {
            $concepts[] = $term;
        }
    }

    return array_values(array_unique($concepts));
}

function findDecisionSentence(array $sentences): ?string
{
    foreach ($sentences as $sentence) {
        $lower = mb_strtolower($sentence, 'UTF-8');
        if (str_contains($lower, 'decide') || str_contains($lower, 'acordam') || str_contains($lower, 'julga')) {
            return $sentence;
        }
    }

    return null;
}

function extractUploadedDocumentText(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload inválido.');
    }

    $tmp = (string)$file['tmp_name'];
    $name = (string)$file['name'];
    $mime = mime_content_type($tmp) ?: 'application/octet-stream';
    $bytes = file_get_contents($tmp);

    if ($bytes === false || strlen($bytes) > 12 * 1024 * 1024) {
        throw new RuntimeException('Ficheiro vazio ou demasiado grande.');
    }

    if ($mime === 'text/plain' || str_ends_with(mb_strtolower($name, 'UTF-8'), '.txt')) {
        return ['text' => $bytes, 'mime' => 'text/plain', 'bytes' => $bytes, 'name' => $name];
    }

    if ($mime !== 'application/pdf' && !str_ends_with(mb_strtolower($name, 'UTF-8'), '.pdf')) {
        throw new RuntimeException('Só são aceites PDFs ou ficheiros TXT.');
    }

    return ['text' => extractTextFromPdfBytes($bytes), 'mime' => 'application/pdf', 'bytes' => $bytes, 'name' => $name];
}

function extractTextFromPdfBytes(string $bytes): string
{
    $chunks = [];

    if (preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $bytes, $streams)) {
        foreach ($streams[1] as $stream) {
            $decoded = @gzuncompress(trim($stream));
            if ($decoded === false) {
                $decoded = @gzdecode(trim($stream));
            }
            $chunks[] = is_string($decoded) ? $decoded : $stream;
        }
    }

    $content = implode("\n", $chunks) ?: $bytes;
    $texts = [];

    if (preg_match_all('/\((?:\\\\.|[^\\\\)])*\)\s*Tj/s', $content, $matches)) {
        foreach ($matches[0] as $match) {
            $texts[] = decodePdfLiteral(trim(preg_replace('/\)\s*Tj$/', ')', $match) ?? $match, '()'));
        }
    }

    if (preg_match_all('/\[(.*?)\]\s*TJ/s', $content, $arrays)) {
        foreach ($arrays[1] as $array) {
            if (preg_match_all('/\((?:\\\\.|[^\\\\)])*\)/s', $array, $items)) {
                foreach ($items[0] as $item) {
                    $texts[] = decodePdfLiteral($item);
                }
            }
        }
    }

    if (!$texts && preg_match_all('/\(([^()]{4,})\)/s', $content, $fallback)) {
        $texts = array_map(static fn(string $item): string => decodePdfLiteral('(' . $item . ')'), $fallback[1]);
    }

    return normalizeWhitespace(implode(' ', array_filter($texts)));
}

function decodePdfLiteral(string $literal): string
{
    $literal = trim($literal);
    if (str_starts_with($literal, '(') && str_ends_with($literal, ')')) {
        $literal = substr($literal, 1, -1);
    }

    $literal = preg_replace('/\\\\([nrtbf()\\\\])/', ' ', $literal) ?? $literal;
    $literal = preg_replace('/\\\\[0-7]{1,3}/', ' ', $literal) ?? $literal;
    return trim($literal);
}

function saveJudgmentSummary(?int $userId, string $filename, string $text, array $summary): void
{
    if (!$userId) {
        return;
    }

    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        return;
    }

    ensureLearningTables();
    $stmt = $db->prepare('INSERT INTO judgment_summaries (user_id, original_filename, extracted_text, summary_json, ai_mode) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([
        $userId,
        $filename,
        mb_substr($text, 0, 120000, 'UTF-8'),
        json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        $summary['_mode'] ?? 'local',
    ]);
}

function getJudgmentLibrary(?int $userId, string $query = ''): array
{
    if (!$userId) {
        return [];
    }

    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        return [];
    }

    ensureLearningTables();
    $params = [$userId];
    $where = 'WHERE user_id <=> ?';
    $query = trim($query);

    if ($query !== '') {
        $where .= ' AND (original_filename LIKE ? OR summary_json LIKE ? OR extracted_text LIKE ?)';
        $like = '%' . $query . '%';
        array_push($params, $like, $like, $like);
    }

    $stmt = $db->prepare("SELECT id, original_filename, summary_json, ai_mode, created_at FROM judgment_summaries {$where} ORDER BY created_at DESC LIMIT 80");
    $stmt->execute($params);

    return array_map(static function (array $row): array {
        $summary = decodeAiJson((string)$row['summary_json']) ?: [];
        return [
            'id' => (int)$row['id'],
            'filename' => (string)$row['original_filename'],
            'ai_mode' => (string)$row['ai_mode'],
            'created_at' => (string)$row['created_at'],
            'summary' => normalizeJudgmentSummary($summary),
        ];
    }, $stmt->fetchAll());
}

function getJudgmentSummaryRecord(?int $userId, int $id): ?array
{
    if (!$userId || $id <= 0) {
        return null;
    }

    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        return null;
    }

    ensureLearningTables();
    $stmt = $db->prepare('SELECT id, original_filename, extracted_text, summary_json, ai_mode, created_at FROM judgment_summaries WHERE id = ? AND user_id <=> ?');
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    $summary = decodeAiJson((string)$row['summary_json']) ?: [];
    return [
        'id' => (int)$row['id'],
        'filename' => (string)$row['original_filename'],
        'extracted_text' => (string)($row['extracted_text'] ?? ''),
        'ai_mode' => (string)$row['ai_mode'],
        'created_at' => (string)$row['created_at'],
        'summary' => normalizeJudgmentSummary($summary),
    ];
}

function generateJudgmentFlashcards(int $userId, int $judgmentId): array
{
    $record = getJudgmentSummaryRecord($userId, $judgmentId);
    if (!$record) {
        throw new RuntimeException('Acórdão não encontrado na tua biblioteca.');
    }

    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        throw new RuntimeException('A base de dados não está disponível.');
    }

    ensureFlashcardPersonalColumns($db);

    $existing = $db->prepare(
        "SELECT id, total_cards
         FROM flashcard_decks
         WHERE user_id <=> ? AND source_type = 'judgment' AND source_id = ? AND active = 1
         LIMIT 1"
    );
    $existing->execute([$userId, $judgmentId]);
    $deck = $existing->fetch();

    if ($deck && (int)$deck['total_cards'] > 0) {
        return [
            'deck_id' => (int)$deck['id'],
            'created' => 0,
            'existing' => true,
            'judgment_id' => $judgmentId,
        ];
    }

    $cards = generateFlashcardsFromSummary($record['summary']);
    if (!$cards) {
        throw new RuntimeException('Não consegui gerar cartas úteis a partir deste acórdão.');
    }

    $summary = $record['summary'];
    $area = (string)($summary['area_direito'] ?: 'Geral');
    $process = (string)($summary['processo'] ?: '');
    $name = 'Acórdão: ' . ($process !== '' ? $process : mb_substr($record['filename'], 0, 60, 'UTF-8'));
    $description = 'Flashcards gerados a partir da Biblioteca Jurídica.';

    $db->beginTransaction();
    try {
        if ($deck) {
            $deckId = (int)$deck['id'];
            $db->prepare('DELETE FROM flashcards WHERE deck_id = ?')->execute([$deckId]);
        } else {
            $stmt = $db->prepare(
                "INSERT INTO flashcard_decks (user_id, name, description, area, area_key, color, icon, total_cards, active, source_type, source_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1, 'judgment', ?)"
            );
            $stmt->execute([
                $userId,
                mb_substr($name, 0, 100, 'UTF-8'),
                $description,
                $area,
                areaKeyFromArea($area),
                '#5aa3d8',
                'judgment',
                $judgmentId,
            ]);
            $deckId = (int)$db->lastInsertId();
        }

        $insert = $db->prepare(
            'INSERT INTO flashcards (deck_id, front, back, difficulty, article_ref, order_num, active) VALUES (?, ?, ?, ?, ?, ?, 1)'
        );

        foreach ($cards as $index => $card) {
            $insert->execute([
                $deckId,
                $card['front'],
                $card['back'],
                $card['difficulty'],
                $card['article_ref'],
                $index + 1,
            ]);
        }

        $db->prepare('UPDATE flashcard_decks SET total_cards = ? WHERE id = ?')->execute([count($cards), $deckId]);
        $db->commit();

        return [
            'deck_id' => $deckId,
            'created' => count($cards),
            'existing' => false,
            'judgment_id' => $judgmentId,
        ];
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

function generateConceptFlashcards(int $userId, int $conceptId): array
{
    $record = getLegalConcept($userId, $conceptId);
    if (!$record || empty($record['concept'])) {
        throw new RuntimeException('Conceito não encontrado no teu dicionário.');
    }

    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        throw new RuntimeException('A base de dados não está disponível.');
    }

    ensureFlashcardPersonalColumns($db);

    $existing = $db->prepare(
        "SELECT id, total_cards
         FROM flashcard_decks
         WHERE user_id <=> ? AND source_type = 'concept' AND source_id = ? AND active = 1
         LIMIT 1"
    );
    $existing->execute([$userId, $conceptId]);
    $deck = $existing->fetch();

    if ($deck && (int)$deck['total_cards'] > 0) {
        return [
            'deck_id' => (int)$deck['id'],
            'created' => 0,
            'existing' => true,
            'concept_id' => $conceptId,
        ];
    }

    $concept = normalizeLegalConcept((array)$record['concept'], (string)$record['term'], (string)$record['area']);
    $cards = generateFlashcardsFromConcept($concept);
    if (!$cards) {
        throw new RuntimeException('Não consegui criar flashcards úteis para este conceito.');
    }

    $name = 'Conceito: ' . $concept['term'];
    $description = 'Flashcards gerados a partir do Dicionário Jurídico.';

    $db->beginTransaction();
    try {
        if ($deck) {
            $deckId = (int)$deck['id'];
            $db->prepare('DELETE FROM flashcards WHERE deck_id = ?')->execute([$deckId]);
        } else {
            $stmt = $db->prepare(
                "INSERT INTO flashcard_decks (user_id, name, description, area, area_key, color, icon, total_cards, active, source_type, source_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1, 'concept', ?)"
            );
            $stmt->execute([
                $userId,
                mb_substr($name, 0, 100, 'UTF-8'),
                $description,
                $concept['area'],
                areaKeyFromArea($concept['area']),
                '#75d6cf',
                'concept',
                $conceptId,
            ]);
            $deckId = (int)$db->lastInsertId();
        }

        $insert = $db->prepare(
            'INSERT INTO flashcards (deck_id, front, back, difficulty, article_ref, order_num, active) VALUES (?, ?, ?, ?, ?, ?, 1)'
        );

        foreach ($cards as $index => $card) {
            $insert->execute([
                $deckId,
                $card['front'],
                $card['back'],
                $card['difficulty'],
                $card['article_ref'],
                $index + 1,
            ]);
        }

        $db->prepare('UPDATE flashcard_decks SET total_cards = ? WHERE id = ?')->execute([count($cards), $deckId]);
        $db->commit();

        return [
            'deck_id' => $deckId,
            'created' => count($cards),
            'existing' => false,
            'concept_id' => $conceptId,
        ];
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

function generateFlashcardsFromSummary(array $summary): array
{
    $prompt = "Cria flashcards de estudo para Direito em Portugal a partir deste resumo de acórdão. Devolve apenas JSON válido com a chave cards. Cada carta deve ter: front, back, difficulty, article_ref. Cria 8 a 12 cartas, com perguntas curtas e respostas úteis para exame. Usa português europeu.\n\nResumo:\n" . json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $decoded = decodeAiJson(callGeminiText($prompt));

    if (isset($decoded['cards']) && is_array($decoded['cards'])) {
        $cards = normalizeGeneratedFlashcards($decoded['cards']);
        if ($cards) {
            return $cards;
        }
    }

    return normalizeGeneratedFlashcards(localJudgmentFlashcards($summary));
}

function generateFlashcardsFromConcept(array $concept): array
{
    $requirements = array_values((array)($concept['requirements'] ?? []));
    $examples = array_values((array)($concept['examples'] ?? []));
    $mistakes = array_values((array)($concept['common_mistakes'] ?? []));
    $related = array_values((array)($concept['related_terms'] ?? []));
    $term = (string)($concept['term'] ?? 'Conceito jurídico');

    return normalizeGeneratedFlashcards([
        [
            'front' => 'O que é "' . $term . '"?',
            'back' => (string)($concept['short_definition'] ?? ''),
            'difficulty' => 'Fácil',
            'article_ref' => (string)($concept['area'] ?? 'Geral'),
        ],
        [
            'front' => 'Explica "' . $term . '" por palavras tuas.',
            'back' => (string)($concept['plain_explanation'] ?? ''),
            'difficulty' => 'Médio',
            'article_ref' => null,
        ],
        [
            'front' => 'Quais são os requisitos ou passos de análise de "' . $term . '"?',
            'back' => implode("\n", $requirements ?: ['Identificar norma, requisitos, factos relevantes e conclusão.']),
            'difficulty' => 'Médio',
            'article_ref' => null,
        ],
        [
            'front' => 'Dá um exemplo de aplicação de "' . $term . '".',
            'back' => implode("\n", $examples ?: ['Criar um caso simples e aplicar os requisitos aos factos.']),
            'difficulty' => 'Médio',
            'article_ref' => null,
        ],
        [
            'front' => 'Que erro de exame deves evitar em "' . $term . '"?',
            'back' => implode("\n", $mistakes ?: ['Não decorar sem aplicar aos factos.']),
            'difficulty' => 'Difícil',
            'article_ref' => null,
        ],
        [
            'front' => (string)($concept['review_question'] ?? 'Como aplicarias este conceito a um caso prático?'),
            'back' => 'Usa a mnemónica: ' . (string)($concept['memory_hook'] ?? 'Definição, requisitos, aplicação e conclusão.')
                . ($related ? "\nTermos ligados: " . implode(', ', $related) : ''),
            'difficulty' => 'Difícil',
            'article_ref' => null,
        ],
    ]);
}

function normalizeGeneratedFlashcards(array $items): array
{
    $cards = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $front = trim((string)($item['front'] ?? ''));
        $back = trim((string)($item['back'] ?? ''));
        if ($front === '' || $back === '') {
            continue;
        }

        $difficulty = (string)($item['difficulty'] ?? 'Médio');
        if (!in_array($difficulty, ['Fácil', 'Médio', 'Difícil'], true)) {
            $difficulty = 'Médio';
        }

        $cards[] = [
            'front' => mb_substr($front, 0, 900, 'UTF-8'),
            'back' => mb_substr($back, 0, 1600, 'UTF-8'),
            'difficulty' => $difficulty,
            'article_ref' => mb_substr(trim((string)($item['article_ref'] ?? '')), 0, 200, 'UTF-8') ?: null,
        ];
    }

    return array_slice($cards, 0, 12);
}

function localJudgmentFlashcards(array $summary): array
{
    $norms = array_values((array)($summary['normas_relevantes'] ?? []));
    $facts = array_values((array)($summary['factos_provados'] ?? []));
    $questions = array_values((array)($summary['questoes_juridicas'] ?? []));
    $concepts = array_values((array)($summary['conceitos_para_estudar'] ?? []));
    $examQuestions = array_values((array)($summary['possiveis_perguntas_exame'] ?? []));
    $articleRef = implode(', ', array_slice($norms, 0, 3));

    $cards = [
        [
            'front' => 'Qual é o problema jurídico central deste acórdão?',
            'back' => implode("\n", $questions ?: ['Identificar a questão jurídica principal a partir dos factos e da decisão.']),
            'difficulty' => 'Médio',
            'article_ref' => $articleRef,
        ],
        [
            'front' => 'Que factos foram decisivos para a solução?',
            'back' => implode("\n", $facts ?: ['Selecionar os factos provados que sustentam a decisão.']),
            'difficulty' => 'Médio',
            'article_ref' => null,
        ],
        [
            'front' => 'Que normas ou artigos deves associar a este acórdão?',
            'back' => implode(', ', $norms ?: ['Normas não extraídas automaticamente. Rever o texto original.']),
            'difficulty' => 'Fácil',
            'article_ref' => $articleRef,
        ],
        [
            'front' => 'Qual foi a decisão do tribunal?',
            'back' => (string)($summary['decisao'] ?: 'A decisão não foi identificada automaticamente. Confirma no texto original.'),
            'difficulty' => 'Fácil',
            'article_ref' => $articleRef,
        ],
        [
            'front' => 'Qual é a ratio decidendi?',
            'back' => (string)($summary['ratio_decidendi'] ?: 'A ratio deve ser extraída da fundamentação essencial do tribunal.'),
            'difficulty' => 'Difícil',
            'article_ref' => $articleRef,
        ],
    ];

    foreach (array_slice($concepts, 0, 4) as $concept) {
        $cards[] = [
            'front' => 'Como explicarias "' . $concept . '" neste acórdão?',
            'back' => 'Define o conceito, liga-o aos factos provados e explica por que influenciou ou podia influenciar a decisão.',
            'difficulty' => 'Médio',
            'article_ref' => $articleRef,
        ];
    }

    foreach (array_slice($examQuestions, 0, 3) as $question) {
        $cards[] = [
            'front' => $question,
            'back' => 'Resposta esperada: enquadrar a questão, indicar a norma relevante, aplicar aos factos e concluir com a posição do tribunal.',
            'difficulty' => 'Difícil',
            'article_ref' => $articleRef,
        ];
    }

    return $cards;
}

function saveLegalSource(?int $userId, string $title, string $area, string $content): void
{
    if (!$userId) {
        return;
    }

    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        return;
    }

    ensureLearningTables();
    $stmt = $db->prepare('INSERT INTO legal_sources (user_id, title, area, content) VALUES (?, ?, ?, ?)');
    $stmt->execute([$userId, $title, $area, mb_substr($content, 0, 250000, 'UTF-8')]);
}

function getLegalSources(?int $userId): array
{
    if (!$userId) {
        return [];
    }

    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        return [];
    }

    ensureLearningTables();
    $stmt = $db->prepare('SELECT id, title, area, created_at FROM legal_sources WHERE user_id <=> ? ORDER BY created_at DESC LIMIT 12');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getLegalSourceContent(?int $userId, int $sourceId): ?array
{
    if (!$userId) {
        return null;
    }

    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        return null;
    }

    ensureLearningTables();
    $stmt = $db->prepare('SELECT id, title, area, content FROM legal_sources WHERE id = ? AND user_id <=> ?');
    $stmt->execute([$sourceId, $userId]);
    $source = $stmt->fetch();
    return $source ?: null;
}

function generateVirtualCase(string $area, string $sourceText): array
{
    $prompt = "Cria um caso prático de exame de Direito em Portugal com base no texto legal fornecido. Devolve apenas JSON válido com: titulo, area, enunciado, factos, artigos_relevantes, armadilhas, criterios_correcao. O caso deve obrigar o estudante a raciocinar, não apenas decorar.\n\nÁrea: {$area}\nTexto legal:\n" . mb_substr($sourceText, 0, AI_MAX_SOURCE_CHARS, 'UTF-8');
    $decoded = decodeAiJson(callGeminiText($prompt));

    if ($decoded) {
        return normalizeVirtualCase($decoded, 'gemini');
    }

    return normalizeVirtualCase(localVirtualCase($area, $sourceText), 'local');
}

function normalizeVirtualCase(array $case, string $mode): array
{
    return [
        'titulo' => (string)($case['titulo'] ?? 'Caso prático gerado'),
        'area' => (string)($case['area'] ?? 'Geral'),
        'enunciado' => (string)($case['enunciado'] ?? ''),
        'factos' => array_values((array)($case['factos'] ?? [])),
        'artigos_relevantes' => array_values((array)($case['artigos_relevantes'] ?? [])),
        'armadilhas' => array_values((array)($case['armadilhas'] ?? [])),
        'criterios_correcao' => array_values((array)($case['criterios_correcao'] ?? [])),
        '_mode' => $mode,
    ];
}

function localVirtualCase(string $area, string $sourceText): array
{
    $norms = [];
    if (preg_match_all('/art\.?\s*\d+[.ºº]?(?:\s*[a-z]*)?/iu', $sourceText, $matches)) {
        $norms = array_values(array_unique(array_slice($matches[0], 0, 5)));
    }

    $area = $area !== '' ? $area : 'Direito Penal';
    $isPenal = str_contains(mb_strtolower($area . ' ' . $sourceText, 'UTF-8'), 'penal');
    $title = $isPenal ? 'O furto por necessidade' : 'Conflito jurídico aplicado';
    $statement = $isPenal
        ? 'João, desempregado e sem dinheiro, retira alimentos de um supermercado para alimentar o filho menor. É intercetado à saída. O valor dos bens é reduzido, mas o gerente apresenta queixa.'
        : 'Duas partes celebram um acordo, mas uma delas incumpre parcialmente. A outra exige indemnização e resolução, enquanto a contraparte invoca impossibilidade e falta de culpa.';

    return [
        'titulo' => $title,
        'area' => $area,
        'enunciado' => $statement,
        'factos' => [
            'Existe uma conduta juridicamente relevante.',
            'Há conflito entre aplicação literal da norma e ponderação dos factos.',
            'O estudante deve qualificar juridicamente a situação.',
        ],
        'artigos_relevantes' => $norms ?: ['Identificar artigos aplicáveis no texto fornecido'],
        'armadilhas' => ['Não ignorar causas de exclusão ou atenuação.', 'Separar factos de juízos conclusivos.'],
        'criterios_correcao' => ['Enquadramento legal', 'Aplicação aos factos', 'Contra-argumento', 'Conclusão fundamentada'],
    ];
}

function evaluateVirtualAnswer(array $case, string $answer): array
{
    $prompt = "Age como Professor de Direito em Portugal. Avalia a resposta do estudante ao caso prático. Devolve apenas JSON válido com: nota_20, pontos_fortes, falhas, artigos_em_falta, feedback, resposta_modelo_curta.\n\nCaso:\n" . json_encode($case, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\nResposta do estudante:\n{$answer}";
    $decoded = decodeAiJson(callGeminiText($prompt));

    if ($decoded) {
        return normalizeEvaluation($decoded, 'gemini');
    }

    return normalizeEvaluation(localVirtualEvaluation($case, $answer), 'local');
}

function startVirtualJudgeChat(?int $userId, string $area, string $sourceText, string $sourceTitle = ''): array
{
    $case = generateVirtualCase($area, $sourceText);
    $intro = 'Vou atuar como juiz-professor. Não vou dar a solução de imediato. Primeiro lê o caso e responde: quais são os factos juridicamente relevantes e que problema jurídico principal identificas?';

    $chat = [
        'id' => null,
        'source_title' => $sourceTitle,
        'area' => $case['area'],
        'case' => $case,
        'messages' => [
            [
                'role' => 'judge',
                'content' => $intro,
                'meta' => ['stage' => 'identificacao'],
            ],
        ],
        'completed' => false,
    ];

    return persistVirtualJudgeChat($userId, $chat);
}

function continueVirtualJudgeChat(?int $userId, array $chat, string $studentAnswer): array
{
    $studentAnswer = trim($studentAnswer);
    if (mb_strlen($studentAnswer, 'UTF-8') < 12) {
        throw new RuntimeException('Escreve uma resposta um pouco mais completa para o juiz conseguir trabalhar contigo.');
    }

    $chat['messages'][] = [
        'role' => 'student',
        'content' => $studentAnswer,
        'meta' => [],
    ];

    $reply = generateSocraticJudgeReply($chat['case'], $chat['messages'], $studentAnswer);
    $chat['messages'][] = [
        'role' => 'judge',
        'content' => $reply['message'],
        'meta' => $reply,
    ];
    $chat['completed'] = (bool)($reply['ready_for_grade'] ?? false);

    return persistVirtualJudgeChat($userId, $chat);
}

function generateSocraticJudgeReply(array $case, array $messages, string $studentAnswer): array
{
    $prompt = "Age como Juiz Virtual e Professor de Direito em Portugal. Conduz o aluno em modo socrático: não entregues logo a solução final, faz perguntas que obriguem a raciocinar. Avalia a última resposta e devolve apenas JSON válido com: message, strengths, gaps, next_question, progress_100, ready_for_grade. Se a resposta já tiver enquadramento legal, aplicação aos factos e conclusão, podes pôr ready_for_grade=true e incluir uma nota breve no message.\n\nCaso:\n" . json_encode($case, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\nHistórico:\n" . json_encode(array_slice($messages, -8), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\nÚltima resposta do estudante:\n{$studentAnswer}";
    $decoded = decodeAiJson(callGeminiText($prompt));

    if ($decoded) {
        return normalizeJudgeReply($decoded);
    }

    return normalizeJudgeReply(localSocraticJudgeReply($case, $messages, $studentAnswer));
}

function normalizeJudgeReply(array $reply): array
{
    $strengths = array_values((array)($reply['strengths'] ?? []));
    $gaps = array_values((array)($reply['gaps'] ?? []));
    $nextQuestion = trim((string)($reply['next_question'] ?? 'Que norma aplicarias e porquê?'));
    $message = trim((string)($reply['message'] ?? 'A tua resposta tem uma direção, mas precisa de melhor enquadramento jurídico.'));

    if ($strengths) {
        $message .= "\n\nPontos fortes: " . implode('; ', array_slice($strengths, 0, 3)) . '.';
    }
    if ($gaps) {
        $message .= "\n\nFalhas a corrigir: " . implode('; ', array_slice($gaps, 0, 3)) . '.';
    }
    if ($nextQuestion !== '') {
        $message .= "\n\nPróxima pergunta: " . $nextQuestion;
    }

    return [
        'message' => $message,
        'strengths' => $strengths,
        'gaps' => $gaps,
        'next_question' => $nextQuestion,
        'progress_100' => max(0, min(100, (int)($reply['progress_100'] ?? 35))),
        'ready_for_grade' => (bool)($reply['ready_for_grade'] ?? false),
    ];
}

function localSocraticJudgeReply(array $case, array $messages, string $studentAnswer): array
{
    $lower = mb_strtolower($studentAnswer, 'UTF-8');
    $turns = count(array_filter($messages, static fn(array $message): bool => ($message['role'] ?? '') === 'student'));
    $strengths = [];
    $gaps = [];
    $progress = 20 + min(45, $turns * 12);

    if (str_contains($lower, 'fact')) {
        $strengths[] = 'começaste pelos factos';
        $progress += 10;
    } else {
        $gaps[] = 'faltou separar factos de conclusões';
    }

    if (str_contains($lower, 'art') || str_contains($lower, 'código') || str_contains($lower, 'norma')) {
        $strengths[] = 'procuraste apoio normativo';
        $progress += 12;
    } else {
        $gaps[] = 'faltou indicar artigo ou norma aplicável';
    }

    if (str_contains($lower, 'concl') || str_contains($lower, 'logo') || str_contains($lower, 'assim')) {
        $strengths[] = 'tentaste fechar a conclusão';
        $progress += 10;
    } else {
        $gaps[] = 'faltou conclusão expressa';
    }

    $nextQuestion = match (true) {
        !str_contains($lower, 'fact') => 'Quais são exatamente os factos provados que mudam a solução jurídica?',
        !str_contains($lower, 'art') && !str_contains($lower, 'norma') => 'Que artigo ou princípio jurídico aplicarias primeiro?',
        !str_contains($lower, 'concl') => 'Qual é a tua conclusão final e que consequência jurídica decorre dela?',
        default => 'Que contra-argumento poderia ser usado contra a tua posição?',
    };

    return [
        'message' => 'A resposta está a evoluir. Vou puxar mais pelo teu raciocínio antes de fechar a correção.',
        'strengths' => $strengths ?: ['identificaste parte do problema'],
        'gaps' => $gaps ?: ['agora falta antecipar a posição contrária'],
        'next_question' => $nextQuestion,
        'progress_100' => min(92, $progress),
        'ready_for_grade' => $progress >= 78,
    ];
}

function persistVirtualJudgeChat(?int $userId, array $chat): array
{
    if (!$userId) {
        $_SESSION['judge_chat'] = $chat;
        return $chat;
    }

    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        $_SESSION['judge_chat'] = $chat;
        return $chat;
    }

    ensureLearningTables();
    if (!empty($chat['id'])) {
        $stmt = $db->prepare('UPDATE virtual_judge_chats SET source_title = ?, area = ?, case_json = ?, messages_json = ?, completed = ? WHERE id = ? AND user_id <=> ?');
        $stmt->execute([
            $chat['source_title'] ?? null,
            $chat['area'] ?? 'Geral',
            json_encode($chat['case'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            json_encode($chat['messages'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            !empty($chat['completed']) ? 1 : 0,
            (int)$chat['id'],
            $userId,
        ]);
    } else {
        $stmt = $db->prepare('INSERT INTO virtual_judge_chats (user_id, source_title, area, case_json, messages_json, completed) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId,
            $chat['source_title'] ?? null,
            $chat['area'] ?? 'Geral',
            json_encode($chat['case'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            json_encode($chat['messages'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            !empty($chat['completed']) ? 1 : 0,
        ]);
        $chat['id'] = (int)$db->lastInsertId();
    }

    $_SESSION['judge_chat'] = $chat;
    return $chat;
}

function normalizeEvaluation(array $evaluation, string $mode): array
{
    return [
        'nota_20' => max(0, min(20, (int)($evaluation['nota_20'] ?? 10))),
        'pontos_fortes' => array_values((array)($evaluation['pontos_fortes'] ?? [])),
        'falhas' => array_values((array)($evaluation['falhas'] ?? [])),
        'artigos_em_falta' => array_values((array)($evaluation['artigos_em_falta'] ?? [])),
        'feedback' => (string)($evaluation['feedback'] ?? ''),
        'resposta_modelo_curta' => (string)($evaluation['resposta_modelo_curta'] ?? ''),
        '_mode' => $mode,
    ];
}

function getFlashcardDecks(?int $userId = null): array
{
    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        return [];
    }

    ensureFlashcardPersonalColumns($db);

    if ($userId) {
        $stmt = $db->prepare(
            "SELECT id, name, description, area, area_key, total_cards, user_id, source_type, source_id
             FROM flashcard_decks
             WHERE active = 1 AND (user_id IS NULL OR user_id = ?)
             ORDER BY user_id IS NULL ASC, area, name"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    return $db->query(
        "SELECT id, name, description, area, area_key, total_cards, user_id, source_type, source_id
         FROM flashcard_decks
         WHERE active = 1 AND user_id IS NULL
         ORDER BY area, name"
    )->fetchAll();
}

function getNextFlashcard(?int $userId, ?int $deckId = null): ?array
{
    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        $cards = appData()['flashcards'];
        return $cards[0] ?? null;
    }

    $params = [];
    $deckWhere = '';
    if ($deckId) {
        $deckWhere = 'AND flashcards.deck_id = ?';
        $params[] = $deckId;
    }

    ensureFlashcardPersonalColumns($db);

    if ($userId) {
        $sql = "SELECT flashcards.id, flashcards.front, flashcards.back, flashcards.difficulty, flashcards.article_ref, flashcard_decks.name AS deck_name, flashcard_decks.area,
                       COALESCE(flashcard_progress.status, 'novo') AS status,
                       COALESCE(flashcard_progress.reviews, 0) AS reviews
                FROM flashcards
                INNER JOIN flashcard_decks ON flashcard_decks.id = flashcards.deck_id
                LEFT JOIN flashcard_progress ON flashcard_progress.card_id = flashcards.id AND flashcard_progress.user_id = ?
                WHERE flashcards.active = 1 AND flashcard_decks.active = 1
                {$deckWhere}
                AND (flashcard_decks.user_id IS NULL OR flashcard_decks.user_id = ?)
                AND (flashcard_progress.next_review IS NULL OR flashcard_progress.next_review <= CURDATE())
                ORDER BY FIELD(COALESCE(flashcard_progress.status, 'novo'), 'aprendendo', 'novo', 'dominado'), COALESCE(flashcard_progress.next_review, '1970-01-01'), flashcard_progress.reviews ASC, RAND()
                LIMIT 1";
        array_unshift($params, $userId);
        $params[] = $userId;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $card = $stmt->fetch();
        if ($card) {
            return $card;
        }
    }

    $sql = "SELECT flashcards.id, flashcards.front, flashcards.back, flashcards.difficulty, flashcards.article_ref, flashcard_decks.name AS deck_name, flashcard_decks.area,
                   'novo' AS status, 0 AS reviews
            FROM flashcards
            INNER JOIN flashcard_decks ON flashcard_decks.id = flashcards.deck_id
            WHERE flashcards.active = 1 AND flashcard_decks.active = 1
            {$deckWhere}
            AND flashcard_decks.user_id IS NULL
            ORDER BY RAND()
            LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute($deckId ? [$deckId] : []);
    $card = $stmt->fetch();
    return $card ?: null;
}

function reviewFlashcard(int $userId, int $cardId, string $grade): array
{
    $db = getDB();
    ensureFlashcardPersonalColumns($db);

    $allowed = $db->prepare(
        'SELECT flashcards.id, flashcards.front, flashcards.back, flashcards.article_ref, flashcard_decks.name AS deck_name, flashcard_decks.area
         FROM flashcards
         INNER JOIN flashcard_decks ON flashcard_decks.id = flashcards.deck_id
         WHERE flashcards.id = ? AND flashcards.active = 1 AND flashcard_decks.active = 1
         AND (flashcard_decks.user_id IS NULL OR flashcard_decks.user_id = ?)'
    );
    $allowed->execute([$cardId, $userId]);
    $card = $allowed->fetch();
    if (!$card) {
        throw new RuntimeException('Carta não encontrada.');
    }

    $isCorrect = in_array($grade, ['hard', 'good', 'easy'], true);
    $status = $grade === 'again' ? 'aprendendo' : ($grade === 'easy' ? 'dominado' : 'aprendendo');
    $days = match ($grade) {
        'easy' => 7,
        'good' => 3,
        'hard' => 1,
        default => 0,
    };

    $stmt = $db->prepare(
        "INSERT INTO flashcard_progress (user_id, card_id, status, reviews, correct, next_review, last_reviewed)
         VALUES (?, ?, ?, 1, ?, DATE_ADD(CURDATE(), INTERVAL ? DAY), NOW())
         ON DUPLICATE KEY UPDATE
             status = VALUES(status),
             reviews = reviews + 1,
             correct = correct + VALUES(correct),
             next_review = VALUES(next_review),
             last_reviewed = NOW()"
    );
    $stmt->execute([$userId, $cardId, $status, $isCorrect ? 1 : 0, $days]);

    $xp = $isCorrect ? XP_CARD_REVIEW : 0;
    if ($xp > 0) {
        $xpStmt = $db->prepare('SELECT xp FROM users WHERE id = ?');
        $xpStmt->execute([$userId]);
        $currentXp = (int)($xpStmt->fetch()['xp'] ?? 0);
        $db->prepare('UPDATE users SET xp = xp + ?, level = ? WHERE id = ?')->execute([$xp, getLevel($currentXp + $xp), $userId]);
        writeActivityLog($userId, 'cards_studied', 'Flashcard revisto', $xp);
    }

    $mistakeLogged = false;
    if (in_array($grade, ['again', 'hard'], true)) {
        logStudyMistake($userId, [
            'source_type' => 'flashcard',
            'source_id' => $cardId,
            'area' => $card['area'] ?? 'Geral',
            'title' => 'Flashcard: ' . ($card['deck_name'] ?? 'Revisão'),
            'prompt' => $card['front'] ?? '',
            'correction' => $card['back'] ?? 'Rever a resposta correta.',
            'next_step' => $grade === 'again'
                ? 'Voltar a responder esta carta ainda hoje.'
                : 'Rever amanhã e explicar em voz alta sem olhar.',
            'weight' => $grade === 'again' ? 4 : 3,
        ]);
        $mistakeLogged = true;
    }

    return ['xp' => $xp, 'next_review_days' => $days, 'status' => $status, 'mistake_logged' => $mistakeLogged];
}

function localVirtualEvaluation(array $case, string $answer): array
{
    $lower = mb_strtolower($answer, 'UTF-8');
    $score = 8;
    $strengths = [];
    $failures = [];

    foreach (['art', 'facto', 'culpa', 'ilicitude', 'conclus'] as $term) {
        if (str_contains($lower, $term)) {
            $score += 2;
        }
    }

    if (mb_strlen($answer, 'UTF-8') > 700) {
        $score += 2;
        $strengths[] = 'Resposta desenvolvida.';
    }

    if (!str_contains($lower, 'art')) {
        $failures[] = 'Faltou indicar artigos ou normas aplicáveis.';
    }
    if (!str_contains($lower, 'conclus')) {
        $failures[] = 'Faltou uma conclusão expressa.';
    }

    return [
        'nota_20' => min(20, $score),
        'pontos_fortes' => $strengths ?: ['Identificaste parte do problema jurídico.'],
        'falhas' => $failures ?: ['Podes melhorar a análise de contra-argumentos.'],
        'artigos_em_falta' => $case['artigos_relevantes'] ?? [],
        'feedback' => 'A resposta foi avaliada localmente. Com inteligência ativa, a correção fica mais próxima de um professor real.',
        'resposta_modelo_curta' => 'Uma boa resposta deve qualificar juridicamente os factos, indicar normas, discutir exceções ou causas de exclusão e terminar com conclusão fundamentada.',
    ];
}

function defaultStudyState(): array
{
    return [
        'xp' => 0,
        'streak' => 0,
        'solvedCases' => 0,
        'masteredCards' => 0,
        'activeCaseId' => 1,
        'role' => 'defense',
        'flashcardIndex' => 0,
        'cardFlipped' => false,
        'quizIndex' => 0,
        'quizScore' => 0,
        'answeredQuiz' => false,
    ];
}

function getUserStudyState(?int $userId): array
{
    if (!$userId) {
        return defaultStudyState();
    }

    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        return defaultStudyState();
    }

    $stmt = $db->prepare('SELECT xp, streak FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) {
        return defaultStudyState();
    }

    $cases = $db->prepare('SELECT COUNT(*) AS total FROM case_sessions WHERE user_id = ? AND completed = 1');
    $cases->execute([$userId]);

    $cards = $db->prepare("SELECT COUNT(*) AS total FROM flashcard_progress WHERE user_id = ? AND status = 'dominado'");
    $cards->execute([$userId]);

    $quiz = $db->prepare('SELECT COALESCE(SUM(score), 0) AS total FROM quiz_attempts WHERE user_id = ?');
    $quiz->execute([$userId]);

    return [
        ...defaultStudyState(),
        'xp' => (int)$user['xp'],
        'streak' => (int)$user['streak'],
        'solvedCases' => (int)($cases->fetch()['total'] ?? 0),
        'masteredCards' => (int)($cards->fetch()['total'] ?? 0),
        'quizScore' => (int)($quiz->fetch()['total'] ?? 0),
    ];
}
