ALTER TABLE training_payments
MODIFY status TINYINT DEFAULT 1 COMMENT '1=รอชำระเงิน, 2=รอตรวจสอบสลิป, 3=ชำระเงินแล้ว, 4=ไม่ผ่านการตรวจสอบ';

ALTER TABLE training_payments
ADD INDEX idx_registration (registration_id),
ADD INDEX idx_status (status);
