-- Database schema for Church Monthly Account Management

-- Create database (optional)
-- CREATE DATABASE IF NOT EXISTS church_accounts CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE church_accounts;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS monthly_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  month TINYINT NOT NULL,
  year SMALLINT NOT NULL,
  service_day VARCHAR(20) NOT NULL DEFAULT 'Sunday',
  total_collection DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  bank_deposit DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  bank_slip_no VARCHAR(100),
  prepared_by VARCHAR(100),
  prepared_date DATE,
  verified_by VARCHAR(100),
  verified_date DATE,
  approved_name VARCHAR(150) DEFAULT 'Rev. K. Sumithra N. Fernando',
  approved_date DATE,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_monthly_accounts_users FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS weekly_collections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  account_id INT NOT NULL,
  week TINYINT NOT NULL,
  service_date DATE,
  collection_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  other_offerings DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  thanks_offering DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  christian_service DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  monthly_offering DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT fk_weekly_account FOREIGN KEY (account_id) REFERENCES monthly_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional generic offerings table (not required if using weekly breakdown above)
CREATE TABLE IF NOT EXISTS offerings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  account_id INT NOT NULL,
  type VARCHAR(50) NOT NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT fk_offerings_account FOREIGN KEY (account_id) REFERENCES monthly_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed example admin (update password hash after running app to avoid plain text)
-- INSERT INTO users (name, email, role, password) VALUES ('Treasurer', 'admin@example.com', 'admin', '<bcrypt_hash_here>');
