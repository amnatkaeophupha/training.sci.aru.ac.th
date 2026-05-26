-- =========================================
-- Table: training_batches
-- =========================================

DROP TABLE IF EXISTS training_batches;

CREATE TABLE training_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL COMMENT 'FK ไป training_courses',
    batch_no VARCHAR(50) NULL COMMENT 'รุ่นที่ เช่น รุ่นที่ 1',

    start_date DATE NULL COMMENT 'วันที่เริ่มอบรม',
    end_date DATE NULL COMMENT 'วันที่สิ้นสุดอบรม',

    start_time TIME NULL COMMENT 'เวลาเริ่ม',
    end_time TIME NULL COMMENT 'เวลาสิ้นสุด',

    registration_start DATETIME NULL COMMENT 'วันเวลาเริ่มรับสมัคร',
    registration_end DATETIME NULL COMMENT 'วันเวลาปิดรับสมัคร',

    capacity INT DEFAULT 0 COMMENT 'จำนวนรับ',

    status TINYINT DEFAULT 1,

    created_at DATETIME NULL,
    updated_at DATETIME NULL,

    INDEX idx_course (course_id),
    INDEX idx_status (status),
    INDEX idx_date (start_date, end_date),

    CONSTRAINT fk_batches_course
        FOREIGN KEY (course_id)
        REFERENCES training_courses(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Sample Data
-- =========================================

INSERT INTO training_batches (
    course_id,
    batch_no,
    start_date,
    end_date,
    start_time,
    end_time,
    registration_start,
    registration_end,
    capacity,
    status,
    created_at
) VALUES
(
    1,
    'รุ่นที่ 1',
    '2026-06-18',
    '2026-06-19',
    '09:00:00',
    '16:00:00',
    '2026-05-25 08:30:00',
    '2026-06-15 16:30:00',
    30,
    1,
    NOW()
),
(
    1,
    'รุ่นที่ 2',
    '2026-07-10',
    '2026-07-11',
    '09:00:00',
    '16:00:00',
    '2026-06-01 08:30:00',
    '2026-07-05 16:30:00',
    30,
    1,
    NOW()
);