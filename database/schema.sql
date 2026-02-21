-- Client Gallery System - Database Schema
-- Run this in phpMyAdmin (or your MySQL client) after creating the database.

-- Clients: client ID, access code, and their gallery folder name
CREATE TABLE IF NOT EXISTS clients (
  client_id   VARCHAR(50) PRIMARY KEY,
  access_code VARCHAR(64) NOT NULL,
  folder      VARCHAR(100) NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Selections: which images each client has marked as favorites
CREATE TABLE IF NOT EXISTS selections (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  client_id  VARCHAR(50) NOT NULL,
  image      VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY one_per_client_image (client_id, image)
);

-- Example client (change the access code in production!)
INSERT INTO clients (client_id, access_code, folder) VALUES
('smith', 'X7kP92', 'client_smith')
ON DUPLICATE KEY UPDATE access_code = VALUES(access_code), folder = VALUES(folder);
