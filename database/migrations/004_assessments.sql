-- 004: Question banks, quizzes/exams, attempts and server-side scored responses.

CREATE TABLE question_banks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    module_id INT UNSIGNED NULL,
    code VARCHAR(80) NOT NULL,
    name VARCHAR(190) NOT NULL,
    UNIQUE KEY uq_banks_code (code),
    CONSTRAINT fk_banks_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_banks_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bank_id INT UNSIGNED NOT NULL,
    question_type ENUM('single','multiple','true_false','scenario','matching','numeric','text') NOT NULL,
    difficulty ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium',
    points SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    answer_spec JSON NULL COMMENT 'numeric: {value,tolerance}; text: {accepted:[...]} — never sent to the browser',
    status ENUM('active','retired') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_questions_bank (bank_id, status, difficulty),
    CONSTRAINT fk_questions_bank FOREIGN KEY (bank_id) REFERENCES question_banks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE question_translations (
    question_id INT UNSIGNED NOT NULL,
    locale ENUM('en','fr') NOT NULL,
    stem TEXT NOT NULL,
    scenario TEXT NULL,
    explanation TEXT NULL,
    translation_status ENUM('missing','draft','in_review','published') NOT NULL DEFAULT 'draft',
    PRIMARY KEY (question_id, locale),
    CONSTRAINT fk_qt_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE answer_options (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id INT UNSIGNED NOT NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    match_key VARCHAR(40) NULL COMMENT 'Matching questions: identifier of the correct right-hand item',
    KEY idx_options_question (question_id),
    CONSTRAINT fk_options_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE answer_option_translations (
    option_id INT UNSIGNED NOT NULL,
    locale ENUM('en','fr') NOT NULL,
    label TEXT NOT NULL,
    match_label TEXT NULL COMMENT 'Matching questions: right-hand label',
    PRIMARY KEY (option_id, locale),
    CONSTRAINT fk_aot_option FOREIGN KEY (option_id) REFERENCES answer_options(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE quizzes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    module_id INT UNSIGNED NULL,
    slug VARCHAR(120) NOT NULL,
    kind ENUM('knowledge_check','module_quiz','final_exam') NOT NULL DEFAULT 'module_quiz',
    question_count SMALLINT UNSIGNED NOT NULL DEFAULT 10,
    duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 20,
    pass_percent TINYINT UNSIGNED NOT NULL DEFAULT 70,
    max_attempts SMALLINT UNSIGNED NULL COMMENT 'NULL = unlimited',
    retake_cooldown_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    show_explanations ENUM('never','after_submit','after_pass') NOT NULL DEFAULT 'after_submit',
    status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_quizzes_slug (slug),
    KEY idx_quizzes_course (course_id, kind),
    CONSTRAINT fk_quizzes_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_quizzes_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE quiz_translations (
    quiz_id INT UNSIGNED NOT NULL,
    locale ENUM('en','fr') NOT NULL,
    title VARCHAR(190) NOT NULL,
    description TEXT NULL,
    translation_status ENUM('missing','draft','in_review','published') NOT NULL DEFAULT 'draft',
    PRIMARY KEY (quiz_id, locale),
    CONSTRAINT fk_quizt_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE quiz_banks (
    quiz_id INT UNSIGNED NOT NULL,
    bank_id INT UNSIGNED NOT NULL,
    pick_count SMALLINT UNSIGNED NOT NULL COMMENT 'Random questions drawn from this bank per attempt',
    PRIMARY KEY (quiz_id, bank_id),
    CONSTRAINT fk_qb_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    CONSTRAINT fk_qb_bank FOREIGN KEY (bank_id) REFERENCES question_banks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE quiz_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    quiz_id INT UNSIGNED NOT NULL,
    attempt_no SMALLINT UNSIGNED NOT NULL,
    status ENUM('in_progress','submitted','expired') NOT NULL DEFAULT 'in_progress',
    locale ENUM('en','fr') NOT NULL DEFAULT 'en',
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    submitted_at DATETIME NULL,
    score_points SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    max_points SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    percent TINYINT UNSIGNED NOT NULL DEFAULT 0,
    passed TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_quiz_attempt_no (user_id, quiz_id, attempt_no),
    KEY idx_qa_quiz (quiz_id, status),
    CONSTRAINT fk_qa_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_qa_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE quiz_attempt_questions (
    attempt_id BIGINT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    sort_order SMALLINT NOT NULL,
    option_order JSON NULL,
    PRIMARY KEY (attempt_id, question_id),
    CONSTRAINT fk_qaq_attempt FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    CONSTRAINT fk_qaq_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE quiz_responses (
    attempt_id BIGINT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    response JSON NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    points_awarded DECIMAL(6,2) NOT NULL DEFAULT 0,
    PRIMARY KEY (attempt_id, question_id),
    CONSTRAINT fk_qr_attempt FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    CONSTRAINT fk_qr_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
