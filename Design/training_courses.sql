-- =========================================
-- Table: training_courses
-- =========================================

DROP TABLE IF EXISTS training_courses;

CREATE TABLE training_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL COMMENT 'FK ไป training_categories',
    title VARCHAR(255) NOT NULL COMMENT 'ชื่อหลักสูตร',
    slug VARCHAR(255) NOT NULL COMMENT 'slug ใช้ใน URL',
    short_description TEXT NULL COMMENT 'คำอธิบายสั้น',
    description TEXT NULL COMMENT 'รายละเอียดหลักสูตร',
    cover_image VARCHAR(255) NULL COMMENT 'รูปภาพ',
    level VARCHAR(100) NULL COMMENT 'ระดับ เช่น เริ่มต้น/กลาง/สูง',
    training_type VARCHAR(50) NULL COMMENT 'online / onsite / hybrid',
    location VARCHAR(255) NULL COMMENT 'สถานที่อบรม',
    duration_text VARCHAR(100) NULL COMMENT 'เช่น 2 วัน / 3 ชั่วโมง',
    capacity INT DEFAULT 0 COMMENT 'จำนวนรับ',
    fee DECIMAL(10,2) DEFAULT 0 COMMENT 'ค่าลงทะเบียน',
    status TINYINT DEFAULT 1 COMMENT '1=ร่าง, 2=เปิด, 3=ปิด',
    is_featured TINYINT(1) DEFAULT 0 COMMENT 'แสดงหน้าแรก',
    published_at DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,

    INDEX idx_category (category_id),
    UNIQUE KEY unique_slug (slug),

    CONSTRAINT fk_courses_category
        FOREIGN KEY (category_id)
        REFERENCES training_categories(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Sample Data
-- =========================================

INSERT INTO training_courses (
    category_id,
    title,
    slug,
    short_description,
    description,
    level,
    training_type,
    location,
    duration_text,
    capacity,
    fee,
    status,
    is_featured,
    published_at,
    created_at
) VALUES
(
    1,
    'การวิเคราะห์ข้อมูลด้วย Python สำหรับงานวิจัยและชุมชน',
    'python-data-analysis',
    'เรียนรู้การจัดการข้อมูล สร้างกราฟ และสรุปผลเชิงสถิติ',
    'เหมาะสำหรับนักวิจัย บุคลากร และผู้สนใจด้าน Data Science',
    'เริ่มต้นถึงปานกลาง',
    'onsite',
    'ห้องปฏิบัติการคอมพิวเตอร์',
    '2 วัน',
    30,
    1500.00,
    1,
    1,
    NOW(),
    NOW()
);