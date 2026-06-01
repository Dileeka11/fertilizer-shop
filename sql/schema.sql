-- =====================================================================
-- Epaladeniya Agro City - Database Schema
-- DB: fertilizer_shop_db
-- Engine: InnoDB, Charset: utf8mb4
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `fertilizer_shop_db`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fertilizer_shop_db`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `sale_items`;
DROP TABLE IF EXISTS `online_orders`;
DROP TABLE IF EXISTS `sales`;
DROP TABLE IF EXISTS `cart_items`;
DROP TABLE IF EXISTS `carts`;
DROP TABLE IF EXISTS `stock_movements`;
DROP TABLE IF EXISTS `fertilizer_details`;
DROP TABLE IF EXISTS `insecticide_details`;
DROP TABLE IF EXISTS `herbicide_details`;
DROP TABLE IF EXISTS `fungicide_details`;
DROP TABLE IF EXISTS `seed_details`;
DROP TABLE IF EXISTS `tool_details`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `suppliers`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `staff_users`;
DROP TABLE IF EXISTS `email_config`;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- categories
-- =====================================================================
CREATE TABLE `categories` (
    `category_id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_name` VARCHAR(60)  NOT NULL,
    `slug`          VARCHAR(60)  NOT NULL,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`category_id`),
    UNIQUE KEY `uq_category_name` (`category_name`),
    UNIQUE KEY `uq_category_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- suppliers
-- =====================================================================
CREATE TABLE `suppliers` (
    `supplier_no`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `supplier_id`     VARCHAR(20)  NOT NULL,
    `company_name`    VARCHAR(120) NOT NULL,
    `contact_person`  VARCHAR(120) DEFAULT NULL,
    `phone`           VARCHAR(20)  DEFAULT NULL,
    `email`           VARCHAR(120) DEFAULT NULL,
    `products_supplied` TEXT       DEFAULT NULL,
    `address`         VARCHAR(255) DEFAULT NULL,
    `status`          ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`supplier_no`),
    UNIQUE KEY `uq_supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- staff_users  (owner / cashier / operator)
-- =====================================================================
CREATE TABLE `staff_users` (
    `user_no`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     VARCHAR(20)  NOT NULL,
    `username`    VARCHAR(60)  NOT NULL,
    `password`    VARCHAR(255) NOT NULL,            -- password_hash (bcrypt)
    `full_name`   VARCHAR(120) NOT NULL,
    `email`       VARCHAR(120) NOT NULL,
    `phone`       VARCHAR(20)  DEFAULT NULL,
    `role`        ENUM('owner','cashier','operator') NOT NULL,
    `status`      ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
    `last_login`  TIMESTAMP NULL DEFAULT NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_no`),
    UNIQUE KEY `uq_user_id`   (`user_id`),
    UNIQUE KEY `uq_username`  (`username`),
    UNIQUE KEY `uq_staff_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- customers (online shoppers + walk-ins)
-- =====================================================================
CREATE TABLE `customers` (
    `customer_no`  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id`  VARCHAR(20)  NOT NULL,
    `first_name`   VARCHAR(80)  NOT NULL,
    `last_name`    VARCHAR(80)  DEFAULT NULL,
    `email`        VARCHAR(120) DEFAULT NULL,
    `password`     VARCHAR(255) DEFAULT NULL,       -- nullable for walk-ins
    `phone`        VARCHAR(20)  DEFAULT NULL,
    `address`      TEXT         DEFAULT NULL,
    `type`         ENUM('online','walkin') NOT NULL DEFAULT 'online',
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`customer_no`),
    UNIQUE KEY `uq_customer_id`    (`customer_id`),
    UNIQUE KEY `uq_customer_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- products
-- =====================================================================
CREATE TABLE `products` (
    `product_no`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`     VARCHAR(20)  NOT NULL,
    `category_id`    INT UNSIGNED NOT NULL,
    `supplier_no`    INT UNSIGNED DEFAULT NULL,
    `name`           VARCHAR(180) NOT NULL,
    `brand`          VARCHAR(120) DEFAULT NULL,
    `description`    TEXT         DEFAULT NULL,
    `image`          VARCHAR(255) DEFAULT NULL,
    `price`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `stock`          INT NOT NULL DEFAULT 0,
    `reorder_level`  INT NOT NULL DEFAULT 0,
    `discount`       DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `status`         ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`product_no`),
    UNIQUE KEY `uq_product_id` (`product_id`),
    KEY `idx_product_cat`      (`category_id`),
    KEY `idx_product_supplier` (`supplier_no`),
    CONSTRAINT `fk_product_cat`      FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_product_supplier` FOREIGN KEY (`supplier_no`) REFERENCES `suppliers`  (`supplier_no`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Category-specific details ---------------------------------------------
CREATE TABLE `fertilizer_details` (
    `product_no`   INT UNSIGNED NOT NULL,
    `npk_ratio`    VARCHAR(30)  DEFAULT NULL,
    `package_size` VARCHAR(30)  DEFAULT NULL,
    PRIMARY KEY (`product_no`),
    CONSTRAINT `fk_fert_product` FOREIGN KEY (`product_no`) REFERENCES `products`(`product_no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `insecticide_details` (
    `product_no`         INT UNSIGNED NOT NULL,
    `form`               VARCHAR(30)  DEFAULT NULL,
    `active_ingredient`  VARCHAR(120) DEFAULT NULL,
    `package_size`       VARCHAR(30)  DEFAULT NULL,
    PRIMARY KEY (`product_no`),
    CONSTRAINT `fk_ins_product` FOREIGN KEY (`product_no`) REFERENCES `products`(`product_no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `herbicide_details` (
    `product_no`   INT UNSIGNED NOT NULL,
    `form`         VARCHAR(30)  DEFAULT NULL,
    `package_size` VARCHAR(30)  DEFAULT NULL,
    PRIMARY KEY (`product_no`),
    CONSTRAINT `fk_herb_product` FOREIGN KEY (`product_no`) REFERENCES `products`(`product_no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `fungicide_details` (
    `product_no`        INT UNSIGNED NOT NULL,
    `disease_control`   VARCHAR(120) DEFAULT NULL,
    `package_size`      VARCHAR(30)  DEFAULT NULL,
    PRIMARY KEY (`product_no`),
    CONSTRAINT `fk_fung_product` FOREIGN KEY (`product_no`) REFERENCES `products`(`product_no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `seed_details` (
    `product_no`   INT UNSIGNED NOT NULL,
    `variety`      VARCHAR(120) DEFAULT NULL,
    `package_size` VARCHAR(30)  DEFAULT NULL,
    PRIMARY KEY (`product_no`),
    CONSTRAINT `fk_seed_product` FOREIGN KEY (`product_no`) REFERENCES `products`(`product_no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tool_details` (
    `product_no` INT UNSIGNED NOT NULL,
    `material`   VARCHAR(120) DEFAULT NULL,
    PRIMARY KEY (`product_no`),
    CONSTRAINT `fk_tool_product` FOREIGN KEY (`product_no`) REFERENCES `products`(`product_no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- carts (online cart for logged-in customers; sessions used for guests)
-- =====================================================================
CREATE TABLE `carts` (
    `cart_no`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_no` INT UNSIGNED NOT NULL,
    `status`      ENUM('active','converted','abandoned') NOT NULL DEFAULT 'active',
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`cart_no`),
    KEY `idx_cart_customer` (`customer_no`),
    CONSTRAINT `fk_cart_customer` FOREIGN KEY (`customer_no`) REFERENCES `customers`(`customer_no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cart_items` (
    `cart_item_no` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cart_no`      INT UNSIGNED NOT NULL,
    `product_no`   INT UNSIGNED NOT NULL,
    `qty`          INT NOT NULL DEFAULT 1,
    PRIMARY KEY (`cart_item_no`),
    KEY `idx_ci_cart`    (`cart_no`),
    KEY `idx_ci_product` (`product_no`),
    CONSTRAINT `fk_ci_cart`    FOREIGN KEY (`cart_no`)    REFERENCES `carts`(`cart_no`)       ON DELETE CASCADE,
    CONSTRAINT `fk_ci_product` FOREIGN KEY (`product_no`) REFERENCES `products`(`product_no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- sales (POS + ONLINE)
-- =====================================================================
CREATE TABLE `sales` (
    `sale_no`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sale_id`     VARCHAR(20)  NOT NULL,
    `customer_no` INT UNSIGNED DEFAULT NULL,
    `cashier_no`  INT UNSIGNED DEFAULT NULL,
    `sale_type`   ENUM('POS','ONLINE') NOT NULL DEFAULT 'POS',
    `subtotal`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `discount`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status`      ENUM('Pending','Paid','Cancelled','Refunded') NOT NULL DEFAULT 'Paid',
    `sale_date`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`sale_no`),
    UNIQUE KEY `uq_sale_id`   (`sale_id`),
    KEY `idx_sale_customer`   (`customer_no`),
    KEY `idx_sale_cashier`    (`cashier_no`),
    KEY `idx_sale_date`       (`sale_date`),
    CONSTRAINT `fk_sale_customer` FOREIGN KEY (`customer_no`) REFERENCES `customers`(`customer_no`)   ON DELETE SET NULL,
    CONSTRAINT `fk_sale_cashier`  FOREIGN KEY (`cashier_no`)  REFERENCES `staff_users`(`user_no`)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `sale_items` (
    `sale_item_no` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sale_no`      INT UNSIGNED NOT NULL,
    `product_no`   INT UNSIGNED NOT NULL,
    `quantity`     INT NOT NULL DEFAULT 1,
    `price`        DECIMAL(10,2) NOT NULL,
    `discount`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`sale_item_no`),
    KEY `idx_si_sale`    (`sale_no`),
    KEY `idx_si_product` (`product_no`),
    CONSTRAINT `fk_si_sale`    FOREIGN KEY (`sale_no`)    REFERENCES `sales`(`sale_no`)       ON DELETE CASCADE,
    CONSTRAINT `fk_si_product` FOREIGN KEY (`product_no`) REFERENCES `products`(`product_no`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `payments` (
    `payment_no`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sale_no`        INT UNSIGNED NOT NULL,
    `amount`         DECIMAL(10,2) NOT NULL,
    `payment_method` ENUM('Cash','Card','Cash on Delivery','Bank Transfer') NOT NULL DEFAULT 'Cash',
    `reference`      VARCHAR(60) DEFAULT NULL,
    `paid_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`payment_no`),
    KEY `idx_pay_sale` (`sale_no`),
    CONSTRAINT `fk_pay_sale` FOREIGN KEY (`sale_no`) REFERENCES `sales`(`sale_no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `online_orders` (
    `order_no`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`         VARCHAR(20)  NOT NULL,
    `customer_no`      INT UNSIGNED NOT NULL,
    `sale_no`          INT UNSIGNED NOT NULL,
    `shipping_address` TEXT         NOT NULL,
    `status`           ENUM('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`order_no`),
    UNIQUE KEY `uq_order_id` (`order_id`),
    KEY `idx_order_customer` (`customer_no`),
    CONSTRAINT `fk_order_customer` FOREIGN KEY (`customer_no`) REFERENCES `customers`(`customer_no`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_sale`     FOREIGN KEY (`sale_no`)     REFERENCES `sales`(`sale_no`)       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- stock_movements (audit trail of all stock changes)
-- =====================================================================
CREATE TABLE `stock_movements` (
    `movement_no` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_no`  INT UNSIGNED NOT NULL,
    `change_qty`  INT NOT NULL,
    `type`        ENUM('IN','OUT','ADJUST') NOT NULL,
    `reason`      VARCHAR(180) DEFAULT NULL,
    `user_no`     INT UNSIGNED DEFAULT NULL,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`movement_no`),
    KEY `idx_sm_product` (`product_no`),
    KEY `idx_sm_date`    (`created_at`),
    CONSTRAINT `fk_sm_product` FOREIGN KEY (`product_no`) REFERENCES `products`(`product_no`) ON DELETE CASCADE,
    CONSTRAINT `fk_sm_user`    FOREIGN KEY (`user_no`)    REFERENCES `staff_users`(`user_no`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- email_config (low-stock alert settings)
-- =====================================================================
CREATE TABLE `email_config` (
    `config_id`  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `to_email`   VARCHAR(120) NOT NULL,
    `from_email` VARCHAR(120) NOT NULL,
    `subject`    VARCHAR(180) NOT NULL,
    `message`    TEXT NOT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- Minimum bootstrap rows (NOT seed data, just required reference rows)
-- =====================================================================
-- 6 product categories (used by category-specific detail tables & UI dropdowns)
INSERT INTO `categories` (`category_id`,`category_name`,`slug`) VALUES
    (1,'Fertilizer','fertilizer'),
    (2,'Insecticide','insecticides'),
    (3,'Herbicide','herbicides'),
    (4,'Fungicide','fungicides'),
    (5,'Seed','seeds'),
    (6,'Tool','tools');

-- Walk-in customer (customer_no=1) used by POS for anonymous sales
INSERT INTO `customers` (`customer_no`,`customer_id`,`first_name`,`last_name`,`type`)
VALUES (1,'WALKIN','Walk-in','Customer','walkin');

-- Default email_config row
INSERT INTO `email_config` (`to_email`,`from_email`,`subject`,`message`) VALUES (
    'owner@agrocity.lk',
    'alerts@agrocity.lk',
    'Low Stock Alert: {product_name}',
    'Product ID: {id}\nName: {name}\nCategory: {category}\n{extra_fields}\nCurrent Stock: {stock}\nReorder Level: {reorder}\n\nPlease reorder soon.'
);

-- NOTE: a default owner account is created by running /fertilizer-shop/setup.php
-- in the browser (one-time). It uses PHP's password_hash() to produce a
-- valid bcrypt hash for the seeded credentials.
