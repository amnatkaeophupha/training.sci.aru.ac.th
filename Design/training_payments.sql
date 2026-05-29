CREATE TABLE training_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    payment_code VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NULL,
    payment_slip VARCHAR(255) NULL,
    paid_at DATETIME NULL,
    status TINYINT DEFAULT 1 COMMENT '1=รอชำระเงิน, 2=รอตรวจสอบสลิป, 3=ชำระเงินแล้ว, 4=ไม่ผ่านการตรวจสอบ',
    approved_by INT NULL,
    approved_at DATETIME NULL,
    note TEXT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    INDEX idx_registration (registration_id),
    INDEX idx_status (status)
);
