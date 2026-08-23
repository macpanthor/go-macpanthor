-- go.macpanthor.com — URL shortener database schema
-- Run:  mysql -u root -p < schema.sql

CREATE DATABASE IF NOT EXISTS macpanthor_go
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE macpanthor_go;

CREATE TABLE IF NOT EXISTS links (
    id           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    short_code   VARCHAR(10)      NOT NULL,
    original_url TEXT             NOT NULL,
    clicks       INT UNSIGNED     NOT NULL DEFAULT 0,
    created_at   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_links_short_code (short_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
