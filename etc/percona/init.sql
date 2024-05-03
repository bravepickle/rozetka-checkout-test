CREATE DATABASE IF NOT EXISTS main_db;

use main_db;

CREATE TABLE IF NOT EXISTS product_remainders (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    items_count INT NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE udx_product_id (product_id)
) engine=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    price_total DECIMAL(10, 2) NOT NULL,
    payload JSON NOT NULL,
    status ENUM('pending', 'in_progress', 'cancelled', 'done') NOT NULL DEFAULT 'pending',
    items_count INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) engine=InnoDB;
