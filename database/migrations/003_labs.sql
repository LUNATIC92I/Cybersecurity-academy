-- 003: Practical labs, tasks with server-side answer specifications, attempts.

CREATE TABLE labs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL,
    path_id INT UNSIGNED NULL,
    course_id INT UNSIGNED NULL,
    module_id INT UNSIGNED NULL,
    lab_type ENUM('simulation','download','virtual') NOT NULL DEFAULT 'simulation',
    difficulty ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
    duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 45,
    pass_percent TINYINT UNSIGNED NOT NULL DEFAULT 70,
    max_attempts SMALLINT UNSIGNED NULL COMMENT 'NULL = unlimited',
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    risk_level ENUM('standard','elevated') NOT NULL DEFAULT 'standard' COMMENT 'Elevated requires a signed safety acknowledgement',
    dataset_file VARCHAR(190) NULL COMMENT 'File under database/content/datasets shown in the in-browser viewer',
    status ENUM('draft','in_review','published','archived') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_labs_slug (slug),
    KEY idx_labs_course (course_id),
    KEY idx_labs_status (status),
    CONSTRAINT fk_labs_path FOREIGN KEY (path_id) REFERENCES learning_paths(id) ON DELETE SET NULL,
    CONSTRAINT fk_labs_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    CONSTRAINT fk_labs_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lab_translations (
    lab_id INT UNSIGNED NOT NULL,
    locale ENUM('en','fr') NOT NULL,
    title VARCHAR(190) NOT NULL,
    summary TEXT NOT NULL,
    objectives TEXT NULL,
    scenario MEDIUMTEXT NULL,
    tools TEXT NULL,
    prerequisites TEXT NULL,
    setup MEDIUMTEXT NULL,
    instructions MEDIUMTEXT NULL,
    expected_results TEXT NULL,
    scoring TEXT NULL,
    solution MEDIUMTEXT NULL,
    cleanup TEXT NULL,
    lessons_learned TEXT NULL,
    translation_status ENUM('missing','draft','in_review','published') NOT NULL DEFAULT 'draft',
    PRIMARY KEY (lab_id, locale),
    CONSTRAINT fk_labt_lab FOREIGN KEY (lab_id) REFERENCES labs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lab_tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lab_id INT UNSIGNED NOT NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    task_type ENUM('text','number','choice','multi','query') NOT NULL DEFAULT 'text',
    points SMALLINT UNSIGNED NOT NULL DEFAULT 10,
    answer_spec JSON NOT NULL COMMENT 'Never sent to the browser',
    KEY idx_lab_tasks_lab (lab_id, sort_order),
    CONSTRAINT fk_lab_tasks_lab FOREIGN KEY (lab_id) REFERENCES labs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lab_task_translations (
    task_id INT UNSIGNED NOT NULL,
    locale ENUM('en','fr') NOT NULL,
    prompt TEXT NOT NULL,
    hint TEXT NULL,
    explanation TEXT NULL,
    options JSON NULL COMMENT 'Labels for choice/multi tasks keyed by option value',
    PRIMARY KEY (task_id, locale),
    CONSTRAINT fk_ltt_task FOREIGN KEY (task_id) REFERENCES lab_tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lab_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    lab_id INT UNSIGNED NOT NULL,
    attempt_no SMALLINT UNSIGNED NOT NULL,
    score SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    max_score SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    percent TINYINT UNSIGNED NOT NULL DEFAULT 0,
    passed TINYINT(1) NOT NULL DEFAULT 0,
    results JSON NULL COMMENT 'Per-task correctness, computed server-side',
    answers JSON NULL,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_lab_attempt_no (user_id, lab_id, attempt_no),
    KEY idx_lab_attempts_lab (lab_id),
    CONSTRAINT fk_la_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_la_lab FOREIGN KEY (lab_id) REFERENCES labs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lab_acknowledgements (
    user_id BIGINT UNSIGNED NOT NULL,
    lab_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NULL,
    accepted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, lab_id),
    CONSTRAINT fk_lack_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_lack_lab FOREIGN KEY (lab_id) REFERENCES labs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lesson_resources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT UNSIGNED NULL,
    lab_id INT UNSIGNED NULL,
    locale ENUM('en','fr') NULL COMMENT 'NULL = language-neutral',
    title VARCHAR(190) NOT NULL,
    storage_path VARCHAR(255) NOT NULL COMMENT 'Relative to storage/uploads or content/datasets; never web-accessible',
    original_name VARCHAR(190) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    size_bytes INT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_resources_lesson (lesson_id),
    KEY idx_resources_lab (lab_id),
    CONSTRAINT fk_res_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    CONSTRAINT fk_res_lab FOREIGN KEY (lab_id) REFERENCES labs(id) ON DELETE CASCADE,
    CONSTRAINT fk_res_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
