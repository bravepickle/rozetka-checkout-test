CREATE DATABASE IF NOT EXISTS main_db;

use main_db;

CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    created_at DATETIME NOT NULL
) engine=InnoDB;

CREATE TABLE IF NOT EXISTS product_remainders (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    items_count INT NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE udx_product_id (product_id),
    FOREIGN KEY fk_product_id (product_id) REFERENCES products (id) ON DELETE CASCADE
) engine=InnoDB;
