-- Create the database
CREATE DATABASE IF NOT EXISTS cybersphere_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cybersphere_db;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) UNIQUE,
  full_name VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO admins (username, password, full_name) VALUES
('admin', MD5('admin123'), 'Administrator')
ON DUPLICATE KEY UPDATE username=username;

-- Computers table
CREATE TABLE IF NOT EXISTS computers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  computer_name VARCHAR(100) NOT NULL UNIQUE,
  ip_address VARCHAR(45),
  status VARCHAR(50) DEFAULT 'Available',
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User entries table
CREATE TABLE IF NOT EXISTS user_entries (
  entry_id INT AUTO_INCREMENT PRIMARY KEY,
  user_name VARCHAR(100) NOT NULL,
  computer_id INT NOT NULL,
  in_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  out_time TIMESTAMP NULL DEFAULT NULL,
  duration_minutes INT NULL,
  price_per_hour DECIMAL(10, 2) NULL,
  total_amount DECIMAL(10, 2) NULL,
  remarks TEXT,
  status VARCHAR(50) DEFAULT 'Active',
  admin_id INT,
  FOREIGN KEY (computer_id) REFERENCES computers(id),
  FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
);