-- 002: Learning paths, courses, modules, lessons (all with EN/FR translations),
-- resources, enrolments and progress.

CREATE TABLE learning_paths (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL COMMENT 'Used in certificate numbers, e.g. SOC',
    slug VARCHAR(80) NOT NULL,
    accent VARCHAR(20) NOT NULL DEFAULT 'blue',
    icon VARCHAR(40) NOT NULL DEFAULT 'shield',
    sort_order SMALLINT NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_paths_code (code),
    UNIQUE KEY uq_paths_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE learning_path_translations (
    path_id INT UNSIGNED NOT NULL,
    locale ENUM('en','fr') NOT NULL,
    title VARCHAR(160) NOT NULL,
    tagline VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    career_outcomes TEXT NULL COMMENT 'One outcome per line',
    translation_status ENUM('missing','draft','in_review','published') NOT NULL DEFAULT 'draft',
    PRIMARY KEY (path_id, locale),
    CONSTRAINT fk_lpt_path FOREIGN KEY (path_id) REFERENCES learning_paths(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL,
    level ENUM('beginner','intermediate','advanced','all') NOT NULL DEFAULT 'beginner',
    access_level ENUM('free','premium') NOT NULL DEFAULT 'premium',
    price_cents INT UNSIGNED NOT NULL DEFAULT 0,
    currency CHAR(3) NOT NULL DEFAULT 'USD',
    estimated_hours SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('draft','in_review','published','archived') NOT NULL DEFAULT 'draft',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    min_exam_score TINYINT UNSIGNED NOT NULL DEFAULT 70 COMMENT 'Completion rule: final exam passing %',
    require_all_labs TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Completion rule: required labs must be passed',
    certificate_enabled TINYINT(1) NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NULL,
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_courses_slug (slug),
    KEY idx_courses_status (status, is_featured),
    CONSTRAINT fk_courses_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE course_translations (
    course_id INT UNSIGNED NOT NULL,
    locale ENUM('en','fr') NOT NULL,
    title VARCHAR(190) NOT NULL,
    subtitle VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT NOT NULL,
    outcomes TEXT NULL COMMENT 'One learning outcome per line',
    audience TEXT NULL,
    certificate_title VARCHAR(190) NULL,
    translation_status ENUM('missing','draft','in_review','published') NOT NULL DEFAULT 'draft',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (course_id, locale),
    FULLTEXT KEY ft_course_translations (title, subtitle, description),
    CONSTRAINT fk_ct_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE learning_path_courses (
    path_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (path_id, course_id),
    KEY idx_lpc_course (course_id),
    CONSTRAINT fk_lpc_path FOREIGN KEY (path_id) REFERENCES learning_paths(id) ON DELETE CASCADE,
    CONSTRAINT fk_lpc_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE modules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    slug VARCHAR(120) NOT NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    level ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
    status ENUM('draft','in_review','published','archived') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_modules_course_slug (course_id, slug),
    KEY idx_modules_course (course_id, sort_order),
    CONSTRAINT fk_modules_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE module_translations (
    module_id INT UNSIGNED NOT NULL,
    locale ENUM('en','fr') NOT NULL,
    title VARCHAR(190) NOT NULL,
    summary TEXT NOT NULL,
    topics TEXT NULL COMMENT 'Syllabus topics, one per line',
    translation_status ENUM('missing','draft','in_review','published') NOT NULL DEFAULT 'draft',
    PRIMARY KEY (module_id, locale),
    CONSTRAINT fk_mt_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lessons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id INT UNSIGNED NOT NULL,
    slug VARCHAR(120) NOT NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    chapter_number SMALLINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Chapter grouping inside a module',
    level ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
    duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    is_preview TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Readable by signed-in users without enrolment',
    status ENUM('draft','in_review','published','archived') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_lessons_module_slug (module_id, slug),
    KEY idx_lessons_module (module_id, sort_order),
    KEY idx_lessons_status (status),
    CONSTRAINT fk_lessons_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lesson_translations (
    lesson_id INT UNSIGNED NOT NULL,
    locale ENUM('en','fr') NOT NULL,
    title VARCHAR(190) NOT NULL,
    chapter_title VARCHAR(190) NULL,
    summary TEXT NOT NULL,
    objectives TEXT NULL COMMENT 'One objective per line',
    prerequisites TEXT NULL COMMENT 'One prerequisite per line',
    content MEDIUMTEXT NOT NULL COMMENT 'Markdown, rendered with HTML escaping',
    assignment TEXT NULL,
    completion_criteria TEXT NULL,
    references_md TEXT NULL,
    translation_status ENUM('missing','draft','in_review','published') NOT NULL DEFAULT 'draft',
    reviewed_by BIGINT UNSIGNED NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (lesson_id, locale),
    CONSTRAINT fk_lt_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    CONSTRAINT fk_lt_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE enrollments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    access_source ENUM('free','subscription','purchase','grant') NOT NULL,
    enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    UNIQUE KEY uq_enrollments (user_id, course_id),
    KEY idx_enrollments_course (course_id),
    CONSTRAINT fk_enr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_enr_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lesson_progress (
    user_id BIGINT UNSIGNED NOT NULL,
    lesson_id INT UNSIGNED NOT NULL,
    status ENUM('started','completed') NOT NULL DEFAULT 'started',
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    last_viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    active_seconds INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Visible-tab time reported by heartbeat, capped server-side',
    PRIMARY KEY (user_id, lesson_id),
    KEY idx_lp_lesson (lesson_id),
    KEY idx_lp_completed (user_id, completed_at),
    CONSTRAINT fk_lp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_lp_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE course_progress (
    user_id BIGINT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    percent TINYINT UNSIGNED NOT NULL DEFAULT 0,
    lessons_completed SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    lessons_total SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    labs_passed SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    labs_total SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    exam_passed TINYINT(1) NOT NULL DEFAULT 0,
    last_lesson_id INT UNSIGNED NULL,
    completed_at DATETIME NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, course_id),
    KEY idx_cp_course (course_id),
    CONSTRAINT fk_cp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cp_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_cp_last_lesson FOREIGN KEY (last_lesson_id) REFERENCES lessons(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE learning_activity (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    activity_date DATE NOT NULL,
    type VARCHAR(40) NOT NULL,
    reference_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_activity_user_date (user_id, activity_date),
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
