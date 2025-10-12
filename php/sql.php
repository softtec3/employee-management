CREATE TABLE employee_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL
);

<!-- ----------------------------------------------------------- -->

INSERT INTO employee_log (employee_id, password, name) VALUES 
('test123', 'password123', 'Test User');

<!-- ----------------------------------------------------------- -->
ALTER TABLE employee_log ADD COLUMN passkey VARCHAR(5) DEFAULT '12345';
UPDATE employee_log SET passkey = '54321' WHERE employee_id = 'your_employee_id';
<!-- ----------------------------------------------------------- -->
