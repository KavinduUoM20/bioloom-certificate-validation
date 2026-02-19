-- Bioloom Islands Pvt Ltd - Certificate & Badge Issuing
-- MySQL schema for certificate_recipients

CREATE DATABASE IF NOT EXISTS bioloom_certs DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bioloom_certs;

CREATE TABLE certificate_recipients (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE COMMENT 'UUID v4 for the recipient',
    first_name      VARCHAR(100) NOT NULL,
    last_name       VARCHAR(100) NOT NULL,
    email           VARCHAR(255) NOT NULL,
    completion_status TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=completed, 0=not completed',
    cert_issued     TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=email/link sent, 0=not sent',
    token           VARCHAR(64) NULL UNIQUE COMMENT 'Verification token for certificate link',
    date_created    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_updated    DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_email (email),
    INDEX idx_completion (completion_status),
    INDEX idx_created (date_created)
) ENGINE=InnoDB;
