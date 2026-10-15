-- 006: Plans, subscriptions, orders, payments (no card data ever stored),
-- coupons, access grants, webhooks, support and announcements.

CREATE TABLE plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) NOT NULL,
    billing_interval ENUM('none','month','year') NOT NULL DEFAULT 'month',
    price_cents INT UNSIGNED NOT NULL DEFAULT 0,
    currency CHAR(3) NOT NULL DEFAULT 'USD',
    includes_all_courses TINYINT(1) NOT NULL DEFAULT 1,
    is_contact_sales TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_plans_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE plan_translations (
    plan_id INT UNSIGNED NOT NULL,
    locale ENUM('en','fr') NOT NULL,
    name VARCHAR(80) NOT NULL,
    description VARCHAR(255) NOT NULL,
    features TEXT NOT NULL COMMENT 'One feature per line',
    PRIMARY KEY (plan_id, locale),
    CONSTRAINT fk_plt_plan FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE coupons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) NOT NULL,
    percent_off TINYINT UNSIGNED NULL,
    amount_off_cents INT UNSIGNED NULL,
    max_redemptions INT UNSIGNED NULL,
    redeemed_count INT UNSIGNED NOT NULL DEFAULT 0,
    expires_at DATETIME NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_coupons_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    status ENUM('pending','active','canceled','expired') NOT NULL DEFAULT 'pending',
    provider VARCHAR(30) NOT NULL,
    provider_reference VARCHAR(120) NULL,
    current_period_start DATETIME NULL,
    current_period_end DATETIME NULL,
    canceled_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_subs_user (user_id, status),
    CONSTRAINT fk_subs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_subs_plan FOREIGN KEY (plan_id) REFERENCES plans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference CHAR(20) NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    item_type ENUM('plan','course','path') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    subtotal_cents INT UNSIGNED NOT NULL,
    discount_cents INT UNSIGNED NOT NULL DEFAULT 0,
    total_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) NOT NULL,
    coupon_id INT UNSIGNED NULL,
    status ENUM('pending','paid','failed','canceled','refunded') NOT NULL DEFAULT 'pending',
    provider VARCHAR(30) NOT NULL,
    provider_session VARCHAR(190) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_at DATETIME NULL,
    UNIQUE KEY uq_orders_reference (reference),
    KEY idx_orders_user (user_id),
    KEY idx_orders_provider_session (provider_session),
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_orders_coupon FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(30) NOT NULL,
    provider_transaction_id VARCHAR(190) NOT NULL,
    amount_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) NOT NULL,
    status ENUM('succeeded','failed','refunded') NOT NULL,
    verified_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_payments_txn (provider, provider_transaction_id),
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE webhook_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider VARCHAR(30) NOT NULL,
    event_id VARCHAR(190) NOT NULL,
    event_type VARCHAR(120) NOT NULL,
    processed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_webhook_event (provider, event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE access_grants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL COMMENT 'NULL with path_id NULL = all courses',
    path_id INT UNSIGNED NULL,
    source ENUM('purchase','admin','enterprise') NOT NULL DEFAULT 'admin',
    granted_by BIGINT UNSIGNED NULL,
    reason VARCHAR(255) NULL,
    expires_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_grants_user (user_id),
    CONSTRAINT fk_grants_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_grants_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_grants_path FOREIGN KEY (path_id) REFERENCES learning_paths(id) ON DELETE CASCADE,
    CONSTRAINT fk_grants_admin FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference CHAR(12) NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    category ENUM('general','technical','billing','enterprise','certificate','security') NOT NULL DEFAULT 'general',
    subject VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('open','pending','closed') NOT NULL DEFAULT 'open',
    locale ENUM('en','fr') NOT NULL DEFAULT 'en',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tickets_reference (reference),
    KEY idx_tickets_status (status),
    CONSTRAINT fk_tickets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ticket_replies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    author_id BIGINT UNSIGNED NULL,
    is_staff TINYINT(1) NOT NULL DEFAULT 0,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_replies_ticket (ticket_id),
    CONSTRAINT fk_replies_ticket FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_replies_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE announcements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    level ENUM('info','success','warning') NOT NULL DEFAULT 'info',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ann_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE announcement_translations (
    announcement_id INT UNSIGNED NOT NULL,
    locale ENUM('en','fr') NOT NULL,
    title VARCHAR(190) NOT NULL,
    body TEXT NOT NULL,
    PRIMARY KEY (announcement_id, locale),
    CONSTRAINT fk_annt_ann FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
