-- =========================================
-- Table: training_instructors
-- =========================================

DROP TABLE IF EXISTS training_instructors;

CREATE TABLE training_instructors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'ชื่อวิทยากร',
    position VARCHAR(255) NULL COMMENT 'ตำแหน่ง',
    department VARCHAR(255) NULL COMMENT 'หน่วยงาน/สังกัด',
    email VARCHAR(150) NULL COMMENT 'อีเมล',
    phone VARCHAR(50) NULL COMMENT 'เบอร์โทร',
    photo VARCHAR(255) NULL COMMENT 'รูปภาพวิทยากร',
    bio TEXT NULL COMMENT 'ประวัติ/ความเชี่ยวชาญ',
    is_active TINYINT(1) DEFAULT 1 COMMENT 'สถานะใช้งาน',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,

    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Sample Data
-- =========================================

INSERT INTO training_instructors (
    name,
    position,
    department,
    email,
    phone,
    photo,
    bio,
    is_active,
    created_at
) VALUES
(
    'อาจารย์สมชาย ใจดี',
    'อาจารย์ประจำหลักสูตรเทคโนโลยีสารสนเทศ',
    'คณะวิทยาศาสตร์และเทคโนโลยี',
    'somchai@example.com',
    '035-000-000',
    'uploads/instructors/somchai.jpg',
    'เชี่ยวชาญด้านการพัฒนาโปรแกรม การวิเคราะห์ข้อมูล และเทคโนโลยีสารสนเทศ',
    1,
    NOW()
),
(
    'อาจารย์สุรีย์ พัฒนา',
    'นักวิชาการคอมพิวเตอร์',
    'คณะวิทยาศาสตร์และเทคโนโลยี',
    'suree@example.com',
    '035-000-001',
    'uploads/instructors/suree.jpg',
    'เชี่ยวชาญด้านฐานข้อมูล เว็บไซต์ และระบบสารสนเทศ',
    1,
    NOW()
);