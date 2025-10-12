-- Complete Database Structure for Employee Profile System
-- Run these SQL commands in phpMyAdmin or MySQL

-- 1. Personal Details Table
CREATE TABLE IF NOT EXISTS employee_personal_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    first_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) DEFAULT NULL,
    fathers_name VARCHAR(100) DEFAULT NULL,
    present_street_1 VARCHAR(255) DEFAULT NULL,
    present_street_2 VARCHAR(255) DEFAULT NULL,
    present_city VARCHAR(100) DEFAULT NULL,
    present_state VARCHAR(100) DEFAULT NULL,
    present_zipcode VARCHAR(20) DEFAULT NULL,
    present_country VARCHAR(100) DEFAULT 'United States',
    permanent_street_1 VARCHAR(255) DEFAULT NULL,
    permanent_street_2 VARCHAR(255) DEFAULT NULL,
    permanent_city VARCHAR(100) DEFAULT NULL,
    permanent_state VARCHAR(100) DEFAULT NULL,
    permanent_zipcode VARCHAR(20) DEFAULT NULL,
    permanent_country VARCHAR(100) DEFAULT 'United States',
    contact_number VARCHAR(20) DEFAULT NULL,
    number_type VARCHAR(20) DEFAULT 'usa',
    status ENUM('pending', 'review', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employee_log(employee_id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee (employee_id)
);

-- 2. Essential Documents Table
CREATE TABLE IF NOT EXISTS employee_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL,
    document_type ENUM('passport', 'drivingLicense', 'voterCard') NOT NULL,
    passport_no VARCHAR(50) DEFAULT NULL,
    passport_photo VARCHAR(255) DEFAULT NULL,
    driving_license_no VARCHAR(50) DEFAULT NULL,
    driving_license_front VARCHAR(255) DEFAULT NULL,
    driving_license_back VARCHAR(255) DEFAULT NULL,
    voter_card_no VARCHAR(50) DEFAULT NULL,
    voter_card_front VARCHAR(255) DEFAULT NULL,
    voter_card_back VARCHAR(255) DEFAULT NULL,
    ssn_no VARCHAR(20) DEFAULT NULL,
    ssn_photo VARCHAR(255) DEFAULT NULL,
    itin_no VARCHAR(20) DEFAULT NULL,
    itin_photo VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'review', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employee_log(employee_id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_doc (employee_id)
);

-- 3. Emergency Contacts Table
CREATE TABLE IF NOT EXISTS employee_emergency_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL,
    contact_1_name VARCHAR(100) DEFAULT NULL,
    contact_1_relation VARCHAR(50) DEFAULT NULL,
    contact_1_phone VARCHAR(20) DEFAULT NULL,
    contact_2_name VARCHAR(100) DEFAULT NULL,
    contact_2_relation VARCHAR(50) DEFAULT NULL,
    contact_2_phone VARCHAR(20) DEFAULT NULL,
    contact_3_name VARCHAR(100) DEFAULT NULL,
    contact_3_relation VARCHAR(50) DEFAULT NULL,
    contact_3_phone VARCHAR(20) DEFAULT NULL,
    status ENUM('pending', 'review', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employee_log(employee_id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_contact (employee_id)
);

-- 4. Sample Data Insert (Replace with actual employee IDs from your employee_log table)
-- Insert sample personal details
INSERT INTO employee_personal_details (employee_id, first_name, last_name, fathers_name, present_city, present_state, contact_number) 
VALUES 
('test123', 'John', 'Doe', 'Robert Doe', 'New York', 'NY', '+1234567890')
ON DUPLICATE KEY UPDATE first_name = VALUES(first_name);

-- Insert sample documents data
INSERT INTO employee_documents (employee_id, document_type, ssn_no) 
VALUES 
('test123', 'passport', '123456789')
ON DUPLICATE KEY UPDATE ssn_no = VALUES(ssn_no);

-- Insert sample emergency contacts
INSERT INTO employee_emergency_contacts (employee_id, contact_1_name, contact_1_relation, contact_1_phone) 
VALUES 
('test123', 'Jane Doe', 'Wife', '+1987654321')
ON DUPLICATE KEY UPDATE contact_1_name = VALUES(contact_1_name);

-- Check if the tables were created successfully
SHOW TABLES LIKE 'employee_%';