-- Recommended usage:
-- 1. Apply this on a fresh database for the initial MVP setup.
-- 2. If some tables already exist, review the script before re-running,
--    especially the foreign key added to users.default_book_id.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
    id CHAR(36) NOT NULL,
    default_book_id CHAR(36) DEFAULT NULL,
    name VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(50) NOT NULL,
    country_name VARCHAR(100) DEFAULT NULL,
    country_code VARCHAR(10) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    timezone VARCHAR(50) DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    email_verified_at DATETIME DEFAULT NULL,
    password_hash VARCHAR(255) DEFAULT NULL,
    google_id VARCHAR(255) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    locale VARCHAR(10) NOT NULL DEFAULT 'en',
    status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
    last_login_at DATETIME DEFAULT NULL,
    plan ENUM('free', 'monthly', 'yearly') NOT NULL DEFAULT 'free',
    license_expires_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_phone (phone),
    KEY idx_users_default_book_id (default_book_id),
    KEY idx_users_google_id (google_id),
    KEY idx_users_plan (plan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ci_sessions (
    id VARCHAR(128) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    timestamp BIGINT UNSIGNED NOT NULL DEFAULT 0,
    data BLOB NOT NULL,
    PRIMARY KEY (id),
    KEY ci_sessions_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_email_verifications (
    id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    code VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_email_verifications_user_id (user_id),
    KEY idx_user_email_verifications_expires_at (expires_at),
    CONSTRAINT fk_user_email_verifications_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_password_reset_tokens_user_id (user_id),
    KEY idx_password_reset_tokens_expires_at (expires_at),
    CONSTRAINT fk_password_reset_tokens_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS book_types (
    type_key VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    requires_currency TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (type_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS books (
    id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    type_key VARCHAR(50) NOT NULL,
    currency_code CHAR(3) DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    icon VARCHAR(50) DEFAULT NULL,
    color VARCHAR(20) DEFAULT NULL,
    settings_json JSON DEFAULT NULL,
    show_cents TINYINT(1) NOT NULL DEFAULT 1,
    thousand_separator ENUM('comma','dot','space') NOT NULL DEFAULT 'comma',
    is_archived TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    last_opened_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_books_user_id (user_id),
    KEY idx_books_type_key (type_key),
    KEY idx_books_currency_code (currency_code),
    KEY idx_books_user_archived (user_id, is_archived),
    CONSTRAINT fk_books_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_books_type
        FOREIGN KEY (type_key) REFERENCES book_types(type_key)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_notes (
    id CHAR(36) NOT NULL,
    book_id CHAR(36) NOT NULL,
    created_by CHAR(36) DEFAULT NULL,
    title VARCHAR(255) DEFAULT NULL,
    content LONGTEXT DEFAULT NULL,
    color VARCHAR(20) DEFAULT NULL,
    position INT NOT NULL DEFAULT 0,
    is_pinned TINYINT(1) NOT NULL DEFAULT 0,
    is_archived TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_notes_book_id (book_id),
    KEY idx_notes_created_by (created_by),
    KEY idx_notes_book_archived (book_id, is_archived),
    KEY idx_notes_book_position (book_id, position),
    KEY idx_notes_book_pinned (book_id, is_pinned),
    CONSTRAINT fk_notes_book
        FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_notes_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_finance_categories (
    id CHAR(36) NOT NULL,
    book_id CHAR(36) NOT NULL,
    created_by CHAR(36) DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    color VARCHAR(20) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_finance_categories_book_id (book_id),
    KEY idx_finance_categories_created_by (created_by),
    KEY idx_finance_categories_book_type (book_id, type),
    CONSTRAINT fk_finance_categories_book
        FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_finance_categories_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_finance_transactions (
    id CHAR(36) NOT NULL,
    book_id CHAR(36) NOT NULL,
    created_by CHAR(36) DEFAULT NULL,
    category_id CHAR(36) DEFAULT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency_code CHAR(3) NOT NULL,
    transaction_date DATE NOT NULL,
    note TEXT DEFAULT NULL,
    reference VARCHAR(100) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_finance_transactions_book_id (book_id),
    KEY idx_finance_transactions_created_by (created_by),
    KEY idx_finance_transactions_category_id (category_id),
    KEY idx_finance_transactions_book_date (book_id, transaction_date),
    KEY idx_finance_transactions_book_type (book_id, type),
    CONSTRAINT fk_finance_transactions_book
        FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_finance_transactions_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_finance_transactions_category
        FOREIGN KEY (category_id) REFERENCES app_finance_categories(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_minishop_categories (
    id CHAR(36) NOT NULL,
    book_id CHAR(36) NOT NULL,
    created_by CHAR(36) DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_minishop_categories_book_id (book_id),
    KEY idx_minishop_categories_created_by (created_by),
    KEY idx_minishop_categories_book_deleted (book_id, deleted_at),
    KEY idx_minishop_categories_book_sort (book_id, sort_order),
    CONSTRAINT fk_minishop_categories_book
        FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_minishop_categories_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_minishop_products (
    id CHAR(36) NOT NULL,
    book_id CHAR(36) NOT NULL,
    created_by CHAR(36) DEFAULT NULL,
    category_id CHAR(36) DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) DEFAULT NULL,
    price DECIMAL(15,2) NOT NULL,
    quantity DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    low_stock_alert DECIMAL(10,3) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_minishop_products_book_id (book_id),
    KEY idx_minishop_products_created_by (created_by),
    KEY idx_minishop_products_category_id (category_id),
    KEY idx_minishop_products_book_category (book_id, category_id),
    KEY idx_minishop_products_book_active (book_id, is_active),
    KEY idx_minishop_products_book_deleted (book_id, deleted_at),
    KEY idx_minishop_products_book_quantity (book_id, quantity),
    CONSTRAINT fk_minishop_products_book
        FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_minishop_products_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_minishop_products_category
        FOREIGN KEY (category_id) REFERENCES app_minishop_categories(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_minishop_customers (
    id CHAR(36) NOT NULL,
    book_id CHAR(36) NOT NULL,
    created_by CHAR(36) DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    reminder_at DATETIME DEFAULT NULL,
    reminder_note VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_minishop_customers_book_id (book_id),
    KEY idx_minishop_customers_created_by (created_by),
    KEY idx_minishop_customers_book_deleted (book_id, deleted_at),
    KEY idx_minishop_customers_book_reminder (book_id, reminder_at),
    KEY idx_minishop_customers_book_phone (book_id, phone),
    CONSTRAINT fk_minishop_customers_book
        FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_minishop_customers_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_minishop_sales (
    id CHAR(36) NOT NULL,
    book_id CHAR(36) NOT NULL,
    created_by CHAR(36) DEFAULT NULL,
    customer_id CHAR(36) DEFAULT NULL,
    currency_code CHAR(3) NOT NULL,
    subtotal_amount DECIMAL(15,2) NOT NULL,
    discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(15,2) NOT NULL,
    paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    due_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    payment_status ENUM('unpaid', 'partial', 'paid') NOT NULL DEFAULT 'unpaid',
    note TEXT DEFAULT NULL,
    sold_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_minishop_sales_book_id (book_id),
    KEY idx_minishop_sales_created_by (created_by),
    KEY idx_minishop_sales_customer_id (customer_id),
    KEY idx_minishop_sales_book_sold_at (book_id, sold_at),
    KEY idx_minishop_sales_book_customer (book_id, customer_id),
    KEY idx_minishop_sales_book_payment_status (book_id, payment_status),
    KEY idx_minishop_sales_book_deleted (book_id, deleted_at),
    CONSTRAINT fk_minishop_sales_book
        FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_minishop_sales_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_minishop_sales_customer
        FOREIGN KEY (customer_id) REFERENCES app_minishop_customers(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_minishop_sale_items (
    id CHAR(36) NOT NULL,
    sale_id CHAR(36) NOT NULL,
    product_id CHAR(36) DEFAULT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(100) DEFAULT NULL,
    quantity DECIMAL(10,3) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    line_total DECIMAL(15,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_minishop_sale_items_sale_id (sale_id),
    KEY idx_minishop_sale_items_product_id (product_id),
    CONSTRAINT fk_minishop_sale_items_sale
        FOREIGN KEY (sale_id) REFERENCES app_minishop_sales(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_minishop_sale_items_product
        FOREIGN KEY (product_id) REFERENCES app_minishop_products(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_minishop_sale_payments (
    id CHAR(36) NOT NULL,
    sale_id CHAR(36) NOT NULL,
    created_by CHAR(36) DEFAULT NULL,
    currency_code CHAR(3) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method ENUM('cash', 'card') NOT NULL DEFAULT 'cash',
    paid_at DATETIME NOT NULL,
    note VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_minishop_sale_payments_sale_id (sale_id),
    KEY idx_minishop_sale_payments_created_by (created_by),
    KEY idx_minishop_sale_payments_paid_at (paid_at),
    CONSTRAINT fk_minishop_sale_payments_sale
        FOREIGN KEY (sale_id) REFERENCES app_minishop_sales(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_minishop_sale_payments_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users
    ADD CONSTRAINT fk_users_default_book
    FOREIGN KEY (default_book_id) REFERENCES books(id)
    ON DELETE SET NULL;

INSERT INTO book_types (type_key, name, description, requires_currency, is_active, created_at, updated_at)
VALUES
    ('notes', 'Notes', 'Book type for note taking', 0, 1, NOW(), NOW()),
    ('finance', 'Finance', 'Book type for income and expense tracking', 1, 1, NOW(), NOW()),
    ('minishop', 'Minishop', 'Book type for small shop sales and inventory tracking', 1, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    requires_currency = VALUES(requires_currency),
    is_active = VALUES(is_active),
    updated_at = VALUES(updated_at);

SET FOREIGN_KEY_CHECKS = 1;
