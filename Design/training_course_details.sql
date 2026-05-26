-- =========================================
-- Table: training_course_details
-- =========================================

DROP TABLE IF EXISTS training_course_details;

CREATE TABLE training_course_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL COMMENT 'FK ไป training_courses',
    section_type VARCHAR(50) NOT NULL COMMENT 'learning/qualification/document/note',
    title VARCHAR(255) NULL COMMENT 'หัวข้อ',
    content TEXT NULL COMMENT 'รายละเอียด',
    sort_order INT DEFAULT 0 COMMENT 'ลำดับการแสดงผล',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,

    INDEX idx_course (course_id),
    INDEX idx_type (section_type),

    CONSTRAINT fk_course_details_course
        FOREIGN KEY (course_id)
        REFERENCES training_courses(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Sample Data
-- =========================================

INSERT INTO training_course_details (
    course_id,
    section_type,
    title,
    content,
    sort_order,
    created_at
) VALUES
-- สิ่งที่จะได้เรียนรู้
(1, 'learning', 'การใช้ Python วิเคราะห์ข้อมูล', 'เรียนรู้การใช้ Pandas และ Numpy', 1, NOW()),
(1, 'learning', 'การสร้างกราฟ', 'ใช้ Matplotlib และ Seaborn สร้าง Visualization', 2, NOW()),

-- คุณสมบัติผู้เข้าอบรม
(1, 'qualification', 'พื้นฐานคอมพิวเตอร์', 'สามารถใช้งานคอมพิวเตอร์เบื้องต้นได้', 1, NOW()),
(1, 'qualification', 'ไม่จำเป็นต้องมีพื้นฐาน Python', 'เริ่มจากพื้นฐานได้', 2, NOW()),

-- เอกสารที่ต้องใช้
(1, 'document', 'บัตรประชาชน', 'ใช้สำหรับลงทะเบียนหน้างาน', 1, NOW()),

-- หมายเหตุ
(1, 'note', 'หมายเหตุ', 'ผู้เข้าอบรมต้องนำ Notebook มาเอง', 1, NOW());