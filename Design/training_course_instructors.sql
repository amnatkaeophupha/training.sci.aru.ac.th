-- =========================================
-- Table: training_course_instructors
-- =========================================

DROP TABLE IF EXISTS training_course_instructors;

CREATE TABLE training_course_instructors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL COMMENT 'FK ไป training_courses',
    instructor_id INT NOT NULL COMMENT 'FK ไป training_instructors',
    role VARCHAR(100) NULL COMMENT 'บทบาท เช่น วิทยากร / ผู้รับผิดชอบ',
    sort_order INT DEFAULT 0 COMMENT 'ลำดับการแสดงผล',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,

    INDEX idx_course (course_id),
    INDEX idx_instructor (instructor_id),

    CONSTRAINT fk_course_instructor_course
        FOREIGN KEY (course_id)
        REFERENCES training_courses(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_course_instructor_instructor
        FOREIGN KEY (instructor_id)
        REFERENCES training_instructors(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Sample Data
-- =========================================

INSERT INTO training_course_instructors (
    course_id,
    instructor_id,
    role,
    sort_order,
    created_at
) VALUES
(1, 1, 'วิทยากรหลัก', 1, NOW()),
(1, 2, 'ผู้ช่วยวิทยากร', 2, NOW());