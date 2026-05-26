CREATE TABLE training_registration_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,

    title_name VARCHAR(50),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    student_code VARCHAR(50),
    school_name VARCHAR(200),
    phone VARCHAR(50),
    email VARCHAR(190),

    status TINYINT DEFAULT 1 COMMENT '1=เข้าร่วม, 2=ยกเลิก',

    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    INDEX idx_registration (registration_id),

    FOREIGN KEY (registration_id) REFERENCES training_registrations(id) ON DELETE CASCADE
);