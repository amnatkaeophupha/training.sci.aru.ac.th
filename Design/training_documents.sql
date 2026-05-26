-- =========================================
-- Table: training_documents
-- =========================================

DROP TABLE IF EXISTS training_documents;

CREATE TABLE training_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL COMMENT 'FK ไป training_courses',
    title VARCHAR(255) NOT NULL COMMENT 'ชื่อเอกสาร',
    file_path VARCHAR(255) NOT NULL COMMENT 'path ไฟล์',
    file_type VARCHAR(50) NULL COMMENT 'pdf/doc/image',
    file_size INT NULL COMMENT 'ขนาดไฟล์ (bytes)',
    sort_order INT DEFAULT 0 COMMENT 'ลำดับการแสดงผล',
    is_public TINYINT(1) DEFAULT 1 COMMENT '1=public, 0=private',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,

    INDEX idx_course (course_id),

    CONSTRAINT fk_documents_course
        FOREIGN KEY (course_id)
        REFERENCES training_courses(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Sample Data
-- =========================================

INSERT INTO training_documents (
    course_id,
    title,
    file_path,
    file_type,
    file_size,
    sort_order,
    is_public,
    created_at
) VALUES
(
    1,
    'กำหนดการอบรม',
    'uploads/documents/schedule_python_course.pdf',
    'pdf',
    245000,
    1,
    1,
    NOW()
),
(
    1,
    'เอกสารประกอบการอบรม',
    'uploads/documents/python_training_material.pdf',
    'pdf',
    1250000,
    2,
    1,
    NOW()
);