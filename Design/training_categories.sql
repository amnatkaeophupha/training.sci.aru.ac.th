-- =========================================
-- Table: training_categories
-- =========================================

DROP TABLE IF EXISTS training_categories;

CREATE TABLE training_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'ชื่อหมวดหมู่',
    slug VARCHAR(255) NOT NULL COMMENT 'slug ใช้ใน URL',
    description TEXT NULL COMMENT 'รายละเอียด',
    sort_order INT DEFAULT 0 COMMENT 'ลำดับการแสดงผล',
    is_active TINYINT(1) DEFAULT 1 COMMENT 'สถานะใช้งาน',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    UNIQUE KEY unique_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Sample Data
-- =========================================

INSERT INTO training_categories (name, slug, description, sort_order, is_active, created_at) VALUES
('Data Science', 'data-science', 'หลักสูตรด้านการวิเคราะห์ข้อมูล', 1, 1, NOW()),
('Food & Lab', 'food-lab', 'หลักสูตรด้านห้องปฏิบัติการและอาหาร', 2, 1, NOW()),
('Digital Skill', 'digital-skill', 'หลักสูตรทักษะดิจิทัล', 3, 1, NOW());