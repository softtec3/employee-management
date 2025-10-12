-- Add passkey column to employee_log table
ALTER TABLE employee_log ADD COLUMN passkey VARCHAR(5) DEFAULT '12345';

-- Update existing records with default passkey (you can change these manually)
UPDATE employee_log SET passkey = '12345' WHERE passkey IS NULL;

-- Sample data if table doesn't exist yet
/*
CREATE TABLE employee_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    passkey VARCHAR(5) DEFAULT '12345',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample employee with passkey
INSERT INTO employee_log (employee_id, password, name, passkey) VALUES 
('test123', 'password123', 'Test User', '12345');
*/