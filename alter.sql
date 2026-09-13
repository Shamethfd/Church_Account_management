-- Run this after importing db.sql to add the missing approved_date column
ALTER TABLE monthly_accounts
  ADD COLUMN approved_date DATE AFTER approved_name;

ALTER TABLE monthly_accounts
  ADD COLUMN service ENUM('Sinhala','Tamil') NOT NULL DEFAULT 'Sinhala' AFTER service_day;

CREATE TABLE IF NOT EXISTS app_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  number VARCHAR(30) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  service ENUM('Sinhala','Tamil') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS families (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  family_uid VARCHAR(32) NOT NULL UNIQUE,
  family_name VARCHAR(150) NOT NULL,
  head_name VARCHAR(120) NOT NULL,
  phone VARCHAR(30),
  address_line VARCHAR(255),
  city VARCHAR(100),
  notes TEXT,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_families_name (family_name),
  CONSTRAINT fk_families_user FOREIGN KEY (created_by) REFERENCES app_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS family_members (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  family_id BIGINT NOT NULL,
  member_name VARCHAR(150) NOT NULL,
  age TINYINT UNSIGNED NULL,
  date_of_birth DATE NULL,
  job_title VARCHAR(150),
  relationship_to_head VARCHAR(80),
  phone VARCHAR(30),
  email VARCHAR(150),
  remarks TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_members_name (member_name),
  INDEX idx_members_family (family_id),
  CONSTRAINT fk_family_members_family FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
