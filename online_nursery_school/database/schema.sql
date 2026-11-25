CREATE DATABASE IF NOT EXISTS nursery_school;
USE nursery_school;

DROP TABLE IF EXISTS fees;
DROP TABLE IF EXISTS pupils;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS parents;
DROP TABLE IF EXISTS classes;

CREATE TABLE parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    address VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'staff', 'parent') NOT NULL DEFAULT 'staff',
    parent_id INT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_parent FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE,
    CONSTRAINT chk_users_parent_role CHECK (
        (role = 'parent' AND parent_id IS NOT NULL)
        OR (role IN ('admin', 'staff') AND parent_id IS NULL)
    )
) ENGINE=InnoDB;

CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL UNIQUE,
    teacher_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE pupils (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    class_id INT NOT NULL,
    parent_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pupils_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    CONSTRAINT fk_pupils_parent FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pupil_id INT NOT NULL,
    term VARCHAR(20) NOT NULL,
    amount_due DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    balance DECIMAL(10,2) GENERATED ALWAYS AS (amount_due - amount_paid) STORED,
    payment_status ENUM('Pending', 'Partial', 'Paid') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_fees_pupil FOREIGN KEY (pupil_id) REFERENCES pupils(id) ON DELETE CASCADE,
    CONSTRAINT chk_fees_amount_due CHECK (amount_due > 0),
    CONSTRAINT chk_fees_amount_paid CHECK (amount_paid >= 0 AND amount_paid <= amount_due),
    CONSTRAINT chk_fees_status CHECK (
        (amount_paid = 0 AND payment_status = 'Pending')
        OR (amount_paid > 0 AND amount_paid < amount_due AND payment_status = 'Partial')
        OR (amount_paid = amount_due AND payment_status = 'Paid')
    )
) ENGINE=InnoDB;
