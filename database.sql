-- Autronicas Inventory Management System Database Schema
-- For use with XAMPP MySQL/phpMyAdmin

CREATE DATABASE IF NOT EXISTS autronicas_db;
USE autronicas_db;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inventory table for product management
CREATE TABLE IF NOT EXISTS inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    min_quantity INT NOT NULL DEFAULT 0,
    unit_price DECIMAL(10, 2) NOT NULL,
    srp_private DECIMAL(10, 2) NOT NULL,
    srp_lgu DECIMAL(10, 2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sales table for sales/job orders summary
CREATE TABLE IF NOT EXISTS sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_order_no INT UNIQUE NOT NULL,
    date DATE NOT NULL,
    vehicle_plate VARCHAR(50) NOT NULL,
    labor_total DECIMAL(10, 2) NOT NULL DEFAULT 0,
    parts_total DECIMAL(10, 2) NOT NULL DEFAULT 0,
    unit_price DECIMAL(10, 2) NOT NULL DEFAULT 0,
    srp_total DECIMAL(10, 2) NOT NULL,
    profit DECIMAL(10, 2) NOT NULL DEFAULT 0,
    confirmed BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Job orders table for detailed job order information
CREATE TABLE IF NOT EXISTS job_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_order_no INT NOT NULL,
    type ENUM('Private', 'LGU') NOT NULL,
    customer_name VARCHAR(255),
    address TEXT,
    contact_no VARCHAR(50),
    model VARCHAR(100),
    plate_no VARCHAR(50),
    motor_chasis VARCHAR(100),
    time_in VARCHAR(50),
    date DATE NOT NULL,
    vehicle_color VARCHAR(50),
    fuel_level VARCHAR(50),
    engine_number VARCHAR(100),
    labor_data TEXT,
    parts_data TEXT,
    total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_job_order_no (job_order_no),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

