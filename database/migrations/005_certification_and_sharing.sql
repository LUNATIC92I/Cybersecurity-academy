-- 005: Certificates with audit events, achievements, social sharing log.

CREATE TABLE certificates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    certificate_number VARCHAR(32) NOT NULL,
    user_id BIGINT UNSIGNED NULL COMMENT 'NULL after account erasure; the record stays verifiable',
    course_id INT UNSIGNED NULL,
    path_id INT UNSIGNED NULL,
    holder_name VARCHAR(120) NOT NULL COMMENT 'Snapshot at issue time',
    title_en VARCHAR(190) NOT NULL,
    title_fr VARCHAR(190) NOT NULL,
    path_name_en VARCHAR(160) NULL,
    path_name_fr VARCHAR(160) NULL,
    locale ENUM('en','fr') NOT NULL DEFAULT 'en',
    criteria JSON NOT NULL COMMENT 'Snapshot of the achievement evidence',
    status ENUM('valid','revoked') NOT NULL DEFAULT 'valid',
    issued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at DATETIME NULL,
    revoke_reason VARCHAR(255) NULL,
    signature CHAR(64) NOT NULL COMMENT 'HMAC-SHA256 of immutable fields with APP_KEY',
    is_public TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Holder opted into a public achievement page',
    share_token CHAR(32) NOT NULL,
    UNIQUE KEY uq_cert_number (certificate_number),
    UNIQUE KEY uq_cert_share (share_token),
    UNIQUE KEY uq_cert_user_course (user_id, course_id),
    KEY idx_cert_status (status),
    CONSTRAINT fk_cert_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_cert_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    CONSTRAINT fk_cert_path FOREIGN KEY (path_id) REFERENCES learning_paths(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE certificate_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    certificate_id BIGINT UNSIGNED NOT NULL,
    event ENUM('issued','revoked','reinstated','downloaded') NOT NULL,
    actor_id BIGINT UNSIGNED NULL,
    notes VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_cert_events_cert (certificate_id),
    CONSTRAINT fk_ce_cert FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE CASCADE,
    CONSTRAINT fk_ce_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE achievements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(60) NOT NULL,
    icon VARCHAR(40) NOT NULL DEFAULT 'award',
    criteria_type ENUM('lessons_completed','labs_passed','exams_passed','streak_days','certificates') NOT NULL,
    threshold INT UNSIGNED NOT NULL,
    UNIQUE KEY uq_achievements_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE achievement_translations (
    achievement_id INT UNSIGNED NOT NULL,
    locale ENUM('en','fr') NOT NULL,
    title VARCHAR(120) NOT NULL,
    description VARCHAR(255) NOT NULL,
    PRIMARY KEY (achievement_id, locale),
    CONSTRAINT fk_at_ach FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_achievements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    achievement_id INT UNSIGNED NOT NULL,
    earned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_public TINYINT(1) NOT NULL DEFAULT 0,
    share_token CHAR(32) NOT NULL,
    UNIQUE KEY uq_user_achievement (user_id, achievement_id),
    UNIQUE KEY uq_ua_share (share_token),
    CONSTRAINT fk_ua_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ua_ach FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE social_shares (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    subject_type ENUM('certificate','achievement','profile') NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    network ENUM('linkedin','facebook','tiktok','copy','download') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_shares_user (user_id),
    KEY idx_shares_subject (subject_type, subject_id),
    CONSTRAINT fk_shares_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
