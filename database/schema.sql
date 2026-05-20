-- =====================================================
-- LexStudy - Base de Dados Completa
-- Compatível com MySQL 5.7+ / MariaDB 10.3+
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS lexstudy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lexstudy;

-- =====================================================
-- UTILIZADORES
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(10) DEFAULT '⚖️',
    level INT UNSIGNED DEFAULT 1,
    xp INT UNSIGNED DEFAULT 0,
    streak INT UNSIGNED DEFAULT 0,
    last_activity DATE DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    area_interesse VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CASOS JURÍDICOS
-- =====================================================
CREATE TABLE IF NOT EXISTS cases (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    area VARCHAR(100) NOT NULL,
    area_key VARCHAR(50) NOT NULL,
    difficulty ENUM('Fácil','Médio','Difícil') DEFAULT 'Médio',
    description TEXT NOT NULL,
    full_context TEXT NOT NULL,
    tags VARCHAR(500) DEFAULT NULL,
    legislation VARCHAR(500) DEFAULT NULL,
    views INT UNSIGNED DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SESSÕES DE CASOS (conversas com IA)
-- =====================================================
CREATE TABLE IF NOT EXISTS case_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    case_id INT UNSIGNED NOT NULL,
    role ENUM('defesa','acusacao') NOT NULL,
    messages LONGTEXT NOT NULL DEFAULT '[]',
    completed TINYINT(1) DEFAULT 0,
    xp_earned INT UNSIGNED DEFAULT 0,
    rating TINYINT(1) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FLASH CARDS
-- =====================================================
CREATE TABLE IF NOT EXISTS flashcard_decks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(300) DEFAULT NULL,
    area VARCHAR(100) NOT NULL,
    area_key VARCHAR(50) NOT NULL,
    color VARCHAR(20) DEFAULT '#c9a84c',
    icon VARCHAR(50) DEFAULT 'ti-cards',
    total_cards INT UNSIGNED DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    source_type VARCHAR(40) DEFAULT 'core',
    source_id INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS flashcards (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    deck_id INT UNSIGNED NOT NULL,
    front TEXT NOT NULL,
    back TEXT NOT NULL,
    difficulty ENUM('Fácil','Médio','Difícil') DEFAULT 'Médio',
    hint VARCHAR(300) DEFAULT NULL,
    article_ref VARCHAR(200) DEFAULT NULL,
    order_num INT UNSIGNED DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (deck_id) REFERENCES flashcard_decks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS flashcard_progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    card_id INT UNSIGNED NOT NULL,
    status ENUM('novo','aprendendo','dominado') DEFAULT 'novo',
    reviews INT UNSIGNED DEFAULT 0,
    correct INT UNSIGNED DEFAULT 0,
    next_review DATE DEFAULT NULL,
    last_reviewed DATETIME DEFAULT NULL,
    UNIQUE KEY uq_user_card (user_id, card_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (card_id) REFERENCES flashcards(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- QUIZ
-- =====================================================
CREATE TABLE IF NOT EXISTS quiz_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    area VARCHAR(100) NOT NULL,
    area_key VARCHAR(50) NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(400) NOT NULL,
    option_b VARCHAR(400) NOT NULL,
    option_c VARCHAR(400) NOT NULL,
    option_d VARCHAR(400) NOT NULL,
    correct_answer CHAR(1) NOT NULL,
    explanation TEXT NOT NULL,
    difficulty ENUM('Fácil','Médio','Difícil') DEFAULT 'Médio',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quiz_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    area VARCHAR(100) DEFAULT 'Geral',
    questions_json TEXT NOT NULL DEFAULT '[]',
    answers_json TEXT NOT NULL DEFAULT '[]',
    score INT UNSIGNED DEFAULT 0,
    total INT UNSIGNED DEFAULT 0,
    percentage DECIMAL(5,2) DEFAULT 0.00,
    xp_earned INT UNSIGNED DEFAULT 0,
    time_taken INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CONQUISTAS (ACHIEVEMENTS)
-- =====================================================
CREATE TABLE IF NOT EXISTS achievements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    icon VARCHAR(10) NOT NULL,
    xp_reward INT UNSIGNED DEFAULT 0,
    condition_type ENUM('cases_solved','quiz_score','streak','xp_total','cards_mastered','login_count') NOT NULL,
    condition_value INT UNSIGNED NOT NULL,
    rarity ENUM('Comum','Raro','Épico','Lendário') DEFAULT 'Comum',
    active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_achievements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    achievement_id INT UNSIGNED NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_ach (user_id, achievement_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ACTIVIDADE DO UTILIZADOR
-- =====================================================
CREATE TABLE IF NOT EXISTS activity_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('case_solved','quiz_done','cards_studied','achievement','login','level_up') NOT NULL,
    description TEXT NOT NULL,
    xp_earned INT UNSIGNED DEFAULT 0,
    metadata VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- NOTAS DO UTILIZADOR
-- =====================================================
CREATE TABLE IF NOT EXISTS user_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    content LONGTEXT NOT NULL,
    tags VARCHAR(300) DEFAULT NULL,
    color VARCHAR(20) DEFAULT '#fef9ec',
    pinned TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SUMÁRIOS DE ACÓRDÃOS
-- =====================================================
CREATE TABLE IF NOT EXISTS judgment_summaries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    original_filename VARCHAR(255) NOT NULL,
    source_url VARCHAR(500) DEFAULT NULL,
    extracted_text LONGTEXT DEFAULT NULL,
    summary_json LONGTEXT NOT NULL,
    ai_mode VARCHAR(30) DEFAULT 'local',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FONTES LEGAIS PARA O JUIZ VIRTUAL
-- =====================================================
CREATE TABLE IF NOT EXISTS legal_sources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    title VARCHAR(200) NOT NULL,
    area VARCHAR(100) DEFAULT 'Geral',
    content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- HISTÓRICO DO JUIZ VIRTUAL
-- =====================================================
CREATE TABLE IF NOT EXISTS virtual_judge_chats (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ASSISTENTE JURÍDICO COM HISTÓRICO
-- =====================================================
CREATE TABLE IF NOT EXISTS legal_assistant_threads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    title VARCHAR(180) NOT NULL DEFAULT 'Nova conversa',
    area VARCHAR(100) DEFAULT 'Geral',
    style_notes LONGTEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS legal_assistant_profiles (
    user_id INT UNSIGNED PRIMARY KEY,
    style_notes LONGTEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS legal_assistant_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    thread_id INT UNSIGNED NOT NULL,
    role VARCHAR(20) NOT NULL,
    content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_thread_created (thread_id, created_at),
    FOREIGN KEY (thread_id) REFERENCES legal_assistant_threads(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- OFICINA DE PEÇAS JURÍDICAS
-- =====================================================
CREATE TABLE IF NOT EXISTS legal_drafts (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DICIONÁRIO JURÍDICO INTELIGENTE
-- =====================================================
CREATE TABLE IF NOT EXISTS legal_concepts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    term VARCHAR(180) NOT NULL,
    area VARCHAR(100) DEFAULT 'Geral',
    concept_json LONGTEXT NOT NULL,
    ai_mode VARCHAR(30) DEFAULT 'local',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_created (user_id, created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PESQUISA JURÍDICA GUIADA
-- =====================================================
CREATE TABLE IF NOT EXISTS legal_research_guides (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    topic VARCHAR(180) NOT NULL,
    area VARCHAR(100) DEFAULT 'Geral',
    objective VARCHAR(80) DEFAULT 'Estudo',
    guide_json LONGTEXT NOT NULL,
    ai_mode VARCHAR(30) DEFAULT 'local',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_created (user_id, created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BANCA DE EXAME
-- =====================================================
CREATE TABLE IF NOT EXISTS legal_exam_sessions (
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
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_user_evaluated (user_id, evaluated_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- LABORATÓRIO DE TESES
-- =====================================================
CREATE TABLE IF NOT EXISTS legal_thesis_labs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    topic VARCHAR(180) NOT NULL,
    area VARCHAR(100) DEFAULT 'Geral',
    facts LONGTEXT DEFAULT NULL,
    lab_json LONGTEXT NOT NULL,
    ai_mode VARCHAR(30) DEFAULT 'local',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_created (user_id, created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- REVISÃO DO DIA
-- =====================================================
CREATE TABLE IF NOT EXISTS daily_study_reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    class_date DATE DEFAULT NULL,
    discipline VARCHAR(120) DEFAULT 'Geral',
    topic VARCHAR(180) NOT NULL,
    raw_notes LONGTEXT NOT NULL,
    review_json LONGTEXT NOT NULL,
    ai_mode VARCHAR(30) DEFAULT 'local',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_class_date (user_id, class_date),
    INDEX idx_user_created (user_id, created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS daily_review_attempts (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DIAGNÓSTICO E PLANO DE ESTUDO
-- =====================================================
CREATE TABLE IF NOT EXISTS study_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    answers_json LONGTEXT NOT NULL,
    profile_json LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_study_profile_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS study_mistakes (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mentor_sessions (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS study_lesson_progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    lesson_id VARCHAR(120) NOT NULL,
    status ENUM('started','completed') DEFAULT 'completed',
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_lesson (user_id, lesson_id),
    INDEX idx_user_status (user_id, status, completed_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_subscriptions (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_usage_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    feature VARCHAR(60) NOT NULL,
    quantity INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_feature_created (user_id, feature, created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SEED DATA - CASOS JURÍDICOS
-- =====================================================
INSERT INTO cases (title, area, area_key, difficulty, description, full_context, tags, legislation) VALUES

('Caução Não Devolvida pelo Senhorio',
 'Direito Civil', 'civil', 'Fácil',
 'Senhorio recusa devolver caução de 1.500€ após fim do contrato de arrendamento, alegando danos que o inquilino contesta.',
 'Maria Silva arrendou um apartamento em Lisboa pelo prazo de 1 ano, tendo entregue uma caução no valor de 1.500€ (correspondente a 2 meses de renda). No término do contrato, o senhorio João Costa recusou devolver a caução, alegando que o apartamento sofreu danos de desgaste e deterioração. Maria contesta, afirmando que os danos são de uso normal e que o apartamento foi entregue nas mesmas condições em que foi recebido, tendo fotografias datadas do momento da entrada. O senhorio não elaborou vistoria de entrada nem saída devidamente documentada.',
 'Arrendamento,Caução,Danos,Art. 1682º CC,Código Civil',
 'Art. 1682º CC, Art. 1043º CC, NRAU (Lei 6/2006)'),

('Despedimento Ilícito por Reestruturação',
 'Direito do Trabalho', 'trabalho', 'Médio',
 'Trabalhadora despedida por extinção do posto de trabalho após 9 anos, mas função foi imediatamente atribuída a trabalhador temporário.',
 'Ana Ferreira trabalhou durante 9 anos na empresa TechCorp Lda como gestora de projetos. A empresa comunicou o despedimento por extinção do posto de trabalho, invocando reestruturação. Contudo, dois meses após o despedimento, a empresa contratou um trabalhador temporário para exercer as mesmas funções. Ana não recebeu qualquer formação para reintegração nem foram avaliadas alternativas ao despedimento. A empresa não seguiu os critérios legais de seleção quando existem trabalhadores em funções equivalentes.',
 'Despedimento,Extinção Posto Trabalho,Ilicitude,Art. 368º CT,Indemnização',
 'Art. 368º CT, Art. 369º CT, Art. 389º CT, Art. 391º CT'),

('Acidente de Viação - Embate na Traseira',
 'Direito Civil', 'civil', 'Médio',
 'Colisão traseira em interseção com semáforo vermelho. Seguradora da parte culpada recusa indemnização plena alegando culpa concorrente.',
 'Carlos Matos parou o seu veículo no semáforo vermelho na Avenida da Liberdade quando foi embatido na traseira pelo veículo de Pedro Alves. Carlos sofreu ferimentos na coluna cervical (traumatismo cervical grau II), ficou incapacitado para trabalhar durante 3 meses e o seu veículo teve danos no valor de 8.500€. A seguradora Secure Plus alega culpa concorrente de Carlos, afirmando que este travou bruscamente. Não há testemunhas diretas mas existe câmara de videovigilância que registou parte do incidente.',
 'Acidente,Responsabilidade Civil,Seguro,Art. 483º CC,Indemnização,Danos Corporais',
 'Art. 483º CC, Art. 496º CC, DL 291/2007, Lei SNSOA'),

('Furto Qualificado em Residência Habitada',
 'Direito Penal', 'penal', 'Difícil',
 'Arguido acusado de furto qualificado em residência habitada. Sem antecedentes, confessou parcialmente. Família dependente.',
 'Rui Santos, 34 anos, desempregado há 8 meses e com família a cargo (esposa e 2 filhos menores), foi detido em flagrante delito ao sair de uma residência com artigos no valor de 3.200€. O arguido confessou ter entrado na residência mas alega que não sabia que estava habitada, tendo entrado por uma janela encontrada aberta. Os proprietários estavam de férias mas a residência apresentava sinais de habitação. Rui não tem antecedentes criminais e demonstrou arrependimento. A acusação pede pena efetiva de prisão.',
 'Furto Qualificado,Residência Habitada,Art. 204º CP,Primeira Vez,Arrependimento',
 'Art. 202º CP, Art. 203º CP, Art. 204º CP, Art. 71º CP, Art. 50º CP'),

('Discriminação de Género em Promoção',
 'Direito do Trabalho', 'trabalho', 'Difícil',
 'Trabalhadora sénior com 12 anos de experiência preterida em promoção a favor de colega masculino com 4 anos e habilitações inferiores.',
 'Sofia Costa, 38 anos, trabalha há 12 anos na empresa FinGroup S.A. como analista financeira sénior, com licenciatura e mestrado em finanças. Candidatou-se à posição de diretora financeira. Foi preterida a favor de Bruno Martins, 31 anos, com apenas 4 anos de empresa e licenciatura em gestão. Sofia tem avaliações de desempenho consistentemente superiores (4.8/5 vs 3.9/5), mais certificações e já desempenhou as funções de diretora interinamente por 6 meses. A empresa não fundamentou a decisão. Sofia está grávida de 3 meses, facto que a empresa conhecia.',
 'Discriminação Género,Promoção,Gravidez,Art. 24º CT,Igualdade,Assédio',
 'Art. 24º CT, Art. 25º CT, Art. 63º CT, Art. 33º CRP, Diretiva 2006/54/CE'),

('Nulidade de Cláusula em Contrato de Consumo',
 'Direito do Consumidor', 'consumidor', 'Médio',
 'Consumidor contrata serviço de telecomunicações com fidelização de 24 meses. Empresa altera unilateralmente o preço sem opção de rescisão gratuita.',
 'Miguel Rodrigues celebrou contrato de telecomunicações com a empresa TelePT com fidelização de 24 meses a 35€/mês. Ao 8º mês, a empresa comunicou aumento do preço para 42€/mês, invocando cláusula contratual que permite alterações com aviso prévio de 30 dias. Miguel pretende rescindir sem penalização, mas a empresa exige o pagamento da cláusula penal de 480€. A cláusula de alteração unilateral do preço estava em letra pequena no rodapé do contrato.',
 'Consumidor,Cláusulas Abusivas,Telecomunicações,Rescisão,Fidelização',
 'Lei 24/96, DL 446/85, Art. 9º LCCG, Lei 5/2004'),

('Responsabilidade Médica por Negligência',
 'Direito Civil', 'civil', 'Difícil',
 'Paciente sofre complicações pós-operatórias alegadamente por erro médico. Hospital recusa responsabilidade invocando risco cirúrgico aceite.',
 'Luísa Mendes, 52 anos, submeteu-se a uma cirurgia programada de remoção de vesícula no Hospital Central. Durante a operação, ocorreu uma lesão no ducto biliar, complicação rara mas conhecida. Luísa ficou com sequelas permanentes que implicaram 3 cirurgias adicionais e incapacidade para trabalho durante 14 meses. O hospital alega que a doente assinou consentimento informado que contempla este risco. A família de Luísa contesta que o consentimento não explicou adequadamente a probabilidade real da complicação e que a técnica cirúrgica utilizada era obsoleta.',
 'Responsabilidade Médica,Negligência,Consentimento Informado,Art. 483º CC,Danos',
 'Art. 483º CC, Art. 487º CC, Art. 150º CP, Lei 15/2014, CDOM'),

('Herança - Colação e Partilha',
 'Direito das Sucessões', 'sucessoes', 'Difícil',
 'Falecimento de pai com testamento que beneficia um dos três filhos. Os outros dois filhos contestam a partilha invocando quota indisponível.',
 'António Sousa faleceu deixando três filhos: Pedro (45), Margarida (42) e Filipe (38). No testamento, António deixou 75% dos bens a Pedro, argumentando que os outros dois já tinham recebido doações em vida. Pedro recebeu em vida doações no valor de 50.000€ não declaradas. O espólio é de 350.000€ em imóveis e contas bancárias. Margarida e Filipe contestam o testamento, invocando a legítima, e exigem a colação das doações feitas a Pedro. Existe ainda uma dívida do falecido de 30.000€ a um credor.',
 'Herança,Testamento,Legítima,Colação,Art. 2156º CC,Sucessão',
 'Art. 2156º CC, Art. 2157º CC, Art. 2104º CC, Art. 2068º CC');

-- =====================================================
-- SEED DATA - FLASHCARD DECKS
-- =====================================================
INSERT INTO flashcard_decks (name, description, area, area_key, color, icon, total_cards) VALUES
('Fundamentos do Direito Civil', 'Conceitos essenciais: contratos, obrigações e responsabilidade', 'Direito Civil', 'civil', '#2563eb', 'ti-file-text', 10),
('Direito Penal Essencial', 'Princípios, crimes e sanções no ordenamento penal português', 'Direito Penal', 'penal', '#dc2626', 'ti-shield-lock', 10),
('Direito do Trabalho', 'Relações laborais, direitos e obrigações dos trabalhadores', 'Direito do Trabalho', 'trabalho', '#16a34a', 'ti-briefcase', 8),
('Direito Constitucional', 'Constituição da República Portuguesa e direitos fundamentais', 'Direito Constitucional', 'constitucional', '#7c3aed', 'ti-building-arch', 8);

-- DECK 1: Direito Civil
INSERT INTO flashcards (deck_id, front, back, difficulty, article_ref, order_num) VALUES
(1, 'O que é um contrato bilateral?', 'Contrato em que AMBAS as partes assumem obrigações recíprocas. As prestações são correspetivas — uma é a causa da outra. Exemplos: compra e venda, arrendamento, empreitada.', 'Fácil', 'Arts. 405º, 874º CC', 1),
(1, 'Defina culpa in contrahendo', 'Responsabilidade pré-contratual que surge quando uma parte viola os deveres de boa-fé, informação e lealdade durante as negociações preliminares. A parte culpada responde pelos danos causados, mesmo sem contrato concluído.', 'Médio', 'Art. 227º CC', 2),
(1, 'Diferença entre nulidade e anulabilidade', 'NULIDADE: vício grave, invocável por qualquer pessoa a qualquer tempo, não é sanável (ex: objeto ilícito). ANULABILIDADE: vício menos grave, apenas invocável pelas partes em prazo determinado, pode ser confirmada (ex: erro, dolo, coacção).', 'Médio', 'Arts. 285º, 287º CC', 3),
(1, 'O que é a exceptio non adimpleti contractus?', 'Exceção de não cumprimento: nos contratos bilaterais, uma parte pode recusar a sua prestação enquanto a contraparte não cumprir ou não oferecer cumprir a dela simultaneamente. Tutela o sinalagma contratual.', 'Médio', 'Art. 428º CC', 4),
(1, 'Quais os requisitos da responsabilidade civil extracontratual?', '1. FACTO ilícito (ação ou omissão contra a lei)\n2. ILICITUDE (violação de direito ou norma de proteção)\n3. CULPA (dolo ou negligência)\n4. DANO (patrimonial ou não patrimonial)\n5. NEXO DE CAUSALIDADE entre facto e dano', 'Médio', 'Art. 483º CC', 5),
(1, 'O que é o usucapião?', 'Forma de AQUISIÇÃO ORIGINÁRIA da propriedade (ou outros direitos reais) pela posse pública, pacífica e de boa-fé mantida durante o prazo legal. Não há transmissão — o direito nasce ex novo na esfera do possuidor.', 'Médio', 'Art. 1287º CC', 6),
(1, 'Defina dano patrimonial e não patrimonial', 'PATRIMONIAL: afeta bens ou interesses de natureza económica (dano emergente + lucro cessante). NÃO PATRIMONIAL (moral): afeta bens de natureza espiritual ou moral (dor, sofrimento, desgosto). Ambos são ressarcíveis quando "pela sua gravidade mereçam tutela".', 'Fácil', 'Arts. 563º, 496º CC', 7),
(1, 'O que é a cessão de créditos?', 'Transmissão de um crédito do cedente para o cessionário, por negócio jurídico, independentemente do consentimento do devedor (mas com notificação obrigatória). O cessionário adquire o crédito no estado em que se encontra.', 'Difícil', 'Art. 577º CC', 8),
(1, 'Diferença entre dolo e erro?', 'ERRO: representação inexata da realidade (espontâneo). DOLO: o erro é provocado por manobras fraudulentas da outra parte ou de terceiro. O dolo é sempre causa de anulação; o erro só o é se essencial e reconhecível.', 'Médio', 'Arts. 247º, 253º CC', 9),
(1, 'O que é a prescrição?', 'Extinção de um direito pelo seu não exercício durante determinado prazo legal. Prazo ordinário: 20 anos. Não é de conhecimento oficioso — tem de ser invocada. Distingue-se da caducidade (esta extingue o próprio direito e é de conhecimento oficioso).', 'Fácil', 'Arts. 300º, 309º CC', 10);

-- DECK 2: Direito Penal
INSERT INTO flashcards (deck_id, front, back, difficulty, article_ref, order_num) VALUES
(2, 'O que é o princípio da legalidade em Direito Penal?', '"Nullum crimen, nulla poena sine lege" — não há crime nem pena sem lei prévia. A lei penal tem de ser: SCRIPTA (escrita), STRICTA (não há analogia in malam partem), CERTA (determinada) e PRAEVIA (anterior ao facto).', 'Fácil', 'Art. 29º CRP, Art. 1º CP', 1),
(2, 'Diferença entre dolo direto, necessário e eventual', 'DIRETO: agente quer o resultado como fim da ação.\nNECESSÁRIO: resultado é consequência necessária do meio escolhido.\nEVENTUAL: agente representa resultado como possível e age conformando-se com essa possibilidade ("se acontecer, aconteceu").', 'Médio', 'Art. 14º CP', 2),
(2, 'Negligência consciente vs. inconsciente', 'CONSCIENTE: agente prevê o resultado como possível mas confia que não ocorrerá (distinção do dolo eventual pela não conformação).\nINCONSCIENTE: agente não prevê o resultado, podendo e devendo prevê-lo (erro sobre os factos por falta de cuidado).', 'Médio', 'Art. 15º CP', 3),
(2, 'O que é a legítima defesa?', 'CAUSA DE JUSTIFICAÇÃO: afasta a ilicitude. Requisitos: facto ilícito atual de terceiro + necessidade de defesa + meio proporcional não manifestamente excessivo. A legítima defesa de terceiros também é admissível.', 'Médio', 'Art. 32º CP', 4),
(2, 'O que é o estado de necessidade?', 'Causa de exclusão da ilicitude (ou culpa). Agente lesa bem jurídico para salvar interesse próprio ou alheio de perigo atual, não removível de outra forma, e o bem sacrificado é de valor igual ou inferior ao bem salvo.', 'Médio', 'Art. 34º CP', 5),
(2, 'O que é a pena suspensa (suspensão de execução)?', 'A execução da pena de prisão até 5 anos pode ser suspensa se o tribunal concluir que a simples censura e a ameaça da prisão realizam de forma adequada as finalidades da punição. Sujeita a condições e regras de conduta.', 'Fácil', 'Art. 50º CP', 6),
(2, 'Diferença entre crime consumado e tentado', 'TENTADO: agente prática atos de execução mas o crime não se consuma por razões alheias à sua vontade. Punição: pena do crime consumado especialmente atenuada. Tentativa impossível: quando for manifesta a inadequação do meio ou a inexistência do objeto.', 'Médio', 'Arts. 22º, 23º CP', 7),
(2, 'O que é a culpa e qual a sua função?', 'CULPA: juízo de censura pessoal dirigido ao agente por ter agido ilicitamente podendo agir de outro modo. Sem culpa não há punição (princípio da culpa). Elementos: imputabilidade, conhecimento da ilicitude e exigibilidade de conduta diferente.', 'Difícil', 'Arts. 13º, 17º, 19º CP', 8),
(2, 'O que é o concurso de crimes?', 'Uma pessoa pratica vários crimes com o mesmo ou diferentes atos. IDEAL: um facto preenche vários tipos. REAL: vários factos autónomos. Pena única conjunta: soma das penas mais elevada à mais elevada das penas parcelares.', 'Difícil', 'Art. 77º, 78º CP', 9),
(2, 'O que é a medida da pena?', 'Determinação concreta da pena dentro da moldura legal. Critérios: grau de ilicitude, modo de execução, grau de culpa, intensidade do dolo/negligência, fins da pena, conduta posterior, condições pessoais do agente.', 'Médio', 'Art. 71º CP', 10);

-- DECK 3: Direito do Trabalho
INSERT INTO flashcards (deck_id, front, back, difficulty, article_ref, order_num) VALUES
(3, 'O que é justa causa de despedimento?', 'Comportamento culposo do trabalhador que, pela sua gravidade e consequências, torne imediata e praticamente impossível a subsistência da relação de trabalho. Requer processo disciplinar prévio com nota de culpa.', 'Médio', 'Art. 351º CT', 1),
(3, 'Diferença entre despedimento coletivo e por extinção de posto', 'COLETIVO: decisão empresarial, mais de 2-5 trabalhadores (conforme dimensão). EXTINÇÃO DE POSTO: apenas um posto de trabalho por causas objetivas. Ambos carecem de comprovação dos motivos e consulta prévia à comissão de trabalhadores.', 'Médio', 'Arts. 359º, 367º CT', 2),
(3, 'Qual a indemnização por despedimento ilícito?', '20 a 45 dias de retribuição base + diuturnidades por cada ano completo de antiguidade (nunca inferior a 3 meses). O trabalhador pode optar entre reintegração ou indemnização.', 'Médio', 'Art. 389º, 391º CT', 3),
(3, 'O que é assédio moral no trabalho?', 'Comportamento indesejado, nomeadamente baseado em discriminação, praticado ao longo do tempo, com o objetivo ou efeito de perturbar, constranger ou afetar a dignidade da pessoa, criando ambiente hostil, degradante ou desestabilizador.', 'Médio', 'Art. 29º CT', 4),
(3, 'Qual o prazo de aviso prévio no despedimento com justa causa do trabalhador?', 'Trabalhador demite-se com justa causa: aviso prévio de 30 dias (ou sem aviso se justa causa imputável ao empregador). Sem justa causa: 30 dias para contratos até 2 anos, 60 dias para contratos superiores.', 'Fácil', 'Art. 394º, 400º CT', 5),
(3, 'O que são diuturnidades?', 'Aumentos periódicos da retribuição ao fim de um determinado tempo de serviço na empresa. Constituem retribuição e são obrigatórias quando previstas em IRCT. Relevam para o cálculo de indemnizações e subsídios.', 'Médio', 'Art. 262º CT', 6),
(3, 'O que é o período experimental?', 'Período inicial do contrato durante o qual qualquer das partes pode cessá-lo sem aviso prévio nem indemnização. Duração: 90 dias regra geral; 180 dias para trabalhadores qualificados; 240 dias para cargos de direção.', 'Fácil', 'Art. 111º CT', 7),
(3, 'Qual a duração máxima do contrato a termo certo?', 'Máximo de 3 anos (incluindo renovações). Pode ser renovado até 3 vezes. Após o limite máximo, converte-se automaticamente em contrato sem termo. O prazo mínimo de cada período é de 6 meses.', 'Médio', 'Art. 148º CT', 8);

-- DECK 4: Direito Constitucional
INSERT INTO flashcards (deck_id, front, back, difficulty, article_ref, order_num) VALUES
(4, 'O que é o princípio da proporcionalidade?', 'Toda a restrição a direitos fundamentais deve ser: ADEQUADA (apta a prosseguir o fim), NECESSÁRIA (não há meio menos restritivo) e PROPORCIONAL EM SENTIDO ESTRITO (vantagens superam desvantagens). Triplo teste.', 'Médio', 'Art. 18º/2 CRP', 1),
(4, 'Diferença entre direitos, liberdades e garantias vs. direitos sociais', 'DLG: direitos de defesa contra o Estado, diretamente aplicáveis e vinculam entidades públicas e privadas. Não comportam reserva do possível. SOCIAIS: direitos a prestações estaduais, dependem de recursos económicos e de opções políticas (programáticos).', 'Médio', 'Arts. 17º, 18º CRP', 2),
(4, 'O que é o princípio da igualdade?', 'Tratar igual o que é igual e desigual o que é desigual na medida da sua desigualdade. Proíbe discriminações arbitrárias baseadas em ascendência, sexo, raça, língua, religião, etc. Admite discriminações justificadas (positivas).', 'Fácil', 'Art. 13º CRP', 3),
(4, 'Quais os direitos fundamentais insusceptíveis de suspensão?', 'Mesmo em estado de emergência são intocáveis: direito à vida, integridade pessoal, identidade pessoal, capacidade civil, cidadania, não retroatividade da lei penal, defesa do arguido, liberdade de consciência e religião.', 'Difícil', 'Art. 19º/6 CRP', 4),
(4, 'O que é a inconstitucionalidade por omissão?', 'Violação da Constituição pela inércia do legislador em concretizar normas constitucionais que impõem uma ação positiva. O TC pode verificar a inconstitucionalidade mas não pode substituir-se ao legislador. Efeitos apenas declarativos.', 'Difícil', 'Art. 283º CRP', 5),
(4, 'O que é a reserva de lei?', 'Certas matérias só podem ser reguladas por lei formal da Assembleia da República (reserva absoluta) ou por decreto-lei autorizado (reserva relativa). Garante que matérias sensíveis sejam decididas democraticamente.', 'Médio', 'Arts. 164º, 165º CRP', 6),
(4, 'Diferença entre fiscalização preventiva e sucessiva da constitucionalidade', 'PREVENTIVA: antes da promulgação/ratificação, a pedido do PR ou AR; SUCESSIVA: após vigência, por qualquer tribunal (fiscalização concreta) ou pelo TC (fiscalização abstrata a pedido do PR, AR, Governo ou Provedor).', 'Médio', 'Arts. 278º, 280º, 281º CRP', 7),
(4, 'O que é o princípio da separação de poderes?', 'Distribuição do poder do Estado por órgãos distintos: LEGISLATIVO (AR), EXECUTIVO (Governo), JUDICIAL (Tribunais). Cada poder controla os outros (checks and balances). Em Portugal há também o PR como árbitro e o TC como guardião da Constituição.', 'Fácil', 'Art. 111º CRP', 8);

-- Atualizar total_cards nos decks
UPDATE flashcard_decks SET total_cards = (SELECT COUNT(*) FROM flashcards WHERE deck_id = flashcard_decks.id);

-- =====================================================
-- SEED DATA - QUIZ
-- =====================================================
INSERT INTO quiz_questions (area, area_key, question, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty) VALUES

-- Direito Civil
('Direito Civil', 'civil', 'Qual o prazo ordinário de prescrição no Código Civil Português?', '10 anos', '15 anos', '20 anos', '5 anos', 'C', 'O Art. 309º do CC estabelece que o prazo ordinário de prescrição é de 20 anos, exceto quando a lei fixar prazo diferente.', 'Fácil'),
('Direito Civil', 'civil', 'Em que situação pode o comprador resolver o contrato de compra e venda por vício da coisa?', 'Sempre que não goste da coisa', 'Quando a coisa tenha defeito que a desvalorize ou impeça o fim a que se destina', 'Só se o vendedor agiu com dolo', 'Apenas dentro de 30 dias após a compra', 'B', 'O Art. 913º CC prevê a responsabilidade do vendedor por defeitos que desvalorizem a coisa ou a tornem imprópria para o uso. O prazo de denúncia é de 30 dias após o conhecimento e a ação caduca em 6 meses (móveis) ou 5 anos (imóveis).', 'Médio'),
('Direito Civil', 'civil', 'O que é o enriquecimento sem causa?', 'Enriquecimento ilícito do Estado', 'Obrigação de restituir aquilo com que alguém se locupletou à custa alheia sem causa justificativa', 'Crime de abuso de confiança', 'Ganho em negócio jurídico vantajoso', 'B', 'Art. 473º CC: quem se enriquecer sem causa justificativa à custa de outrem fica obrigado a restituir. É fonte autónoma de obrigações, subsidiária face às outras.', 'Médio'),
('Direito Civil', 'civil', 'A cláusula penal tem que função?', 'Fixar convencionalmente a indemnização por incumprimento', 'Permitir que o credor obtenha a execução específica', 'Estabelecer o prazo de cumprimento', 'Proibir a resolução do contrato', 'A', 'Art. 810º CC: as partes podem fixar por acordo a indemnização devida pelo incumprimento (cláusula penal), que dispensa a prova do dano. O tribunal pode reduzi-la se manifestamente excessiva.', 'Médio'),
('Direito Civil', 'civil', 'Quando é que o contrato de arrendamento urbano se renova automaticamente?', 'Nunca — requer sempre novo contrato', 'Quando nenhuma das partes o denunciar no prazo legal', 'Apenas se o senhorio concordar', 'Após 2 anos de vigência', 'B', 'Nos termos do NRAU, o contrato renova-se automaticamente no seu termo, salvo denúncia por qualquer das partes com o pré-aviso legal exigido.', 'Fácil'),

-- Direito Penal
('Direito Penal', 'penal', 'A pena máxima para homicídio qualificado em Portugal é:', '15 anos de prisão', '20 anos de prisão', '25 anos de prisão', '30 anos de prisão', 'C', 'Art. 132º CP: o homicídio qualificado é punido com prisão de 12 a 25 anos, quando praticado com circunstâncias que revelem especial censurabilidade ou perversidade.', 'Fácil'),
('Direito Penal', 'penal', 'O princípio da subsidiariedade do Direito Penal significa:', 'O Direito Penal aplica-se antes dos outros ramos', 'O Direito Penal só intervém quando os outros ramos são insuficientes para proteger bens jurídicos', 'As penas são sempre menores que as coimas administrativas', 'O Direito Penal regula apenas relações entre privados', 'B', 'O Direito Penal é a ultima ratio: só deve intervir quando a proteção de bens jurídicos essenciais não puder ser assegurada por meios menos gravosos (princípio da intervenção mínima).', 'Médio'),
('Direito Penal', 'penal', 'Qual é o prazo de prescrição do procedimento criminal para um crime punível com prisão de 10 anos?', '5 anos', '10 anos', '15 anos', '20 anos', 'B', 'Art. 118º CP: o prazo de prescrição é igual ao máximo da pena, com o limite mínimo de 5 anos. Para crime com pena máxima de 10 anos, a prescrição ocorre em 10 anos.', 'Médio'),
('Direito Penal', 'penal', 'O que é a imputabilidade diminuída?', 'Situação em que o agente não pode ser julgado', 'Situação em que a anomalia psíquica atenua sensivelmente a culpa sem a excluir', 'Crime praticado por menor de 16 anos', 'Situação de embriaguez voluntária', 'B', 'Art. 20º/2 CP: se a anomalia psíquica atenua sensivelmente a culpa sem a excluir, a pena pode ser especialmente atenuada. Distingue-se da inimputabilidade que exclui a culpa.', 'Difícil'),
('Direito Penal', 'penal', 'O crime de burla simples é punível com:', 'Pena de multa apenas', 'Prisão até 3 anos ou pena de multa', 'Prisão de 1 a 5 anos', 'Prisão até 8 anos', 'B', 'Art. 217º CP: a burla simples é punível com prisão até 3 anos ou pena de multa. A burla qualificada (Art. 218º) tem moldura mais grave, podendo atingir 8 anos.', 'Médio'),

-- Direito do Trabalho
('Direito do Trabalho', 'trabalho', 'Qual o período mínimo de férias anuais a que o trabalhador tem direito?', '20 dias úteis', '22 dias úteis', '25 dias úteis', '30 dias úteis', 'B', 'Art. 238º CT: o período mínimo de férias é de 22 dias úteis. O trabalhador tem direito a pelo menos 22 dias úteis de férias remuneradas por ano.', 'Fácil'),
('Direito do Trabalho', 'trabalho', 'Qual a duração máxima do período normal de trabalho?', '8 horas por dia e 40 horas por semana', '8 horas por dia e 44 horas por semana', '10 horas por dia e 48 horas por semana', '7 horas por dia e 35 horas por semana', 'A', 'Art. 203º CT: o período normal de trabalho não pode exceder 8 horas por dia e 40 horas por semana, sem prejuízo de regimes especiais e de adaptabilidade.', 'Fácil'),
('Direito do Trabalho', 'trabalho', 'Em que consiste o direito de oposição ao despedimento?', 'Trabalhador pode continuar a trabalhar ignorando a carta de despedimento', 'Trabalhador pode impugnar o despedimento em tribunal nos 60 dias seguintes', 'Trabalhador pode negociar coletivamente contra o despedimento', 'Sindicato pode vetar o despedimento', 'B', 'Art. 387º CT: o trabalhador pode impugnar o despedimento no prazo de 60 dias a contar do conhecimento ou da data em que a decisão produziu efeitos.', 'Médio'),
('Direito do Trabalho', 'trabalho', 'O que é o princípio do tratamento mais favorável ao trabalhador?', 'Trabalhadores mais antigos têm sempre melhores condições', 'Normas de hierarquia inferior prevalecem sobre as superiores se forem mais favoráveis ao trabalhador', 'Empregador deve sempre conceder benefícios acima do mínimo legal', 'Trabalhadores jovens têm condições especiais', 'B', 'Art. 3º CT: as fontes inferiores (contrato individual) prevalecem sobre as superiores (lei, IRCT) quando estabeleçam condições mais favoráveis ao trabalhador. É a aplicação do princípio da norma mais favorável.', 'Difícil'),

-- Direito Constitucional
('Direito Constitucional', 'constitucional', 'Em que artigo da CRP está consagrado o direito à habitação?', 'Art. 24º', 'Art. 48º', 'Art. 65º', 'Art. 71º', 'C', 'Art. 65º CRP: todos têm direito, para si e para a sua família, a uma habitação de dimensão adequada, em condições de higiene e conforto e que preserve a intimidade pessoal e a privacidade familiar.', 'Fácil'),
('Direito Constitucional', 'constitucional', 'Qual o órgão de soberania que tem competência exclusiva para declarar o estado de emergência?', 'Assembleia da República', 'Presidente da República', 'Governo', 'Tribunal Constitucional', 'B', 'Art. 138º CRP: o estado de emergência é declarado pelo Presidente da República, ouvido o Governo e com autorização da Assembleia da República, ou sua comissão permanente.', 'Médio'),
('Direito Constitucional', 'constitucional', 'O que é a fiscalização sucessiva abstrata da constitucionalidade?', 'Fiscalização feita antes da publicação das leis', 'Apreciação pelo TC da constitucionalidade de normas em vigor, a pedido de certas entidades', 'Recurso de constitucionalidade em processo judicial', 'Fiscalização feita pelos cidadãos', 'B', 'Art. 281º CRP: o TC aprecia e declara a inconstitucionalidade ou ilegalidade de normas jurídicas em vigor, com força obrigatória geral, mediante pedido do PR, AR, Governo, Provedores ou representantes regionais.', 'Difícil'),
('Direito Constitucional', 'constitucional', 'Quantas vezes pode o Presidente da República exercer mandato consecutivo?', 'Ilimitado', 'Máximo 2 mandatos consecutivos', 'Apenas 1 mandato', 'Máximo 3 mandatos', 'B', 'Art. 123º CRP: ninguém pode ser eleito Presidente da República mais de duas vezes, sejam ou não consecutivas. Após dois mandatos consecutivos, há um período de interdição de mais um mandato.', 'Fácil'),

-- Direito do Consumidor
('Direito do Consumidor', 'consumidor', 'Qual o prazo de garantia para bens de consumo novos?', '1 ano', '2 anos', '3 anos', '5 anos', 'B', 'DL 67/2003 (transposta pela Diretiva 2019/771): a garantia mínima para bens de consumo novos é de 2 anos a contar da entrega. Para bens usados pode ser reduzida a 1 ano.', 'Fácil'),
('Direito do Consumidor', 'consumidor', 'O direito de livre resolução em contratos celebrados à distância é de:', '7 dias', '14 dias', '30 dias', '60 dias', 'B', 'DL 24/2014 (Diretiva 2011/83/UE): o consumidor tem 14 dias para exercer o direito de livre resolução nos contratos celebrados à distância ou fora do estabelecimento, sem necessidade de indicar motivo.', 'Fácil');

-- =====================================================
-- SEED DATA - CONQUISTAS
-- =====================================================
INSERT INTO achievements (name, description, icon, xp_reward, condition_type, condition_value, rarity) VALUES
('Primeiro Caso', 'Resolveste o teu primeiro caso jurídico', '⚖️', 100, 'cases_solved', 1, 'Comum'),
('Advogado em Formação', 'Resolveste 5 casos jurídicos', '🎓', 250, 'cases_solved', 5, 'Comum'),
('Jurisconsulto', 'Resolveste 20 casos jurídicos', '🏛️', 500, 'cases_solved', 20, 'Raro'),
('Mestre do Direito', 'Resolveste 50 casos jurídicos', '👑', 1000, 'cases_solved', 50, 'Épico'),
('Primeira Vitória Quiz', 'Completaste o teu primeiro quiz', '🧠', 50, 'quiz_score', 1, 'Comum'),
('Perfeccionista', 'Obtiveste 100% num quiz', '💯', 300, 'quiz_score', 100, 'Raro'),
('Sequência de Fogo', 'Mantiveste uma sequência de 7 dias', '🔥', 200, 'streak', 7, 'Raro'),
('Imparável', 'Mantiveste uma sequência de 30 dias', '⚡', 750, 'streak', 30, 'Épico'),
('1000 XP', 'Atingiste 1.000 pontos de XP', '🌟', 100, 'xp_total', 1000, 'Comum'),
('Especialista', 'Atingiste 10.000 pontos de XP', '🎯', 500, 'xp_total', 10000, 'Épico'),
('Cartas Dominadas', 'Dominaste 20 flash cards', '📚', 150, 'cards_mastered', 20, 'Comum'),
('Bibliotecário', 'Dominaste 100 flash cards', '🏅', 400, 'cards_mastered', 100, 'Raro');

SET FOREIGN_KEY_CHECKS = 1;
