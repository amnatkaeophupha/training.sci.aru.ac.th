CREATE TABLE training_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id INT NOT NULL,
    member_id INT UNSIGNED NOT NULL,

    registration_code VARCHAR(50) UNIQUE,

    status TINYINT DEFAULT 1 COMMENT '1=รอชำระเงิน / รออนุมัติ, 2=อนุมัติแล้ว, 3=ไม่อนุมัติ, 4=ยกเลิก, 5=เข้าอบรมแล้ว',

    created_at DATETIME NULL,
    updated_at DATETIME NULL,

    INDEX idx_batch (batch_id),
        INDEX idx_member (member_id),

        CONSTRAINT fk_registrations_batch
            FOREIGN KEY (batch_id)
            REFERENCES training_batches(id)
            ON DELETE CASCADE,

        CONSTRAINT fk_registrations_member
            FOREIGN KEY (member_id)
            REFERENCES members(id)
            ON DELETE CASCADE,

        UNIQUE KEY unique_member_batch (member_id, batch_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

