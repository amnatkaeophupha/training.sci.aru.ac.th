ALTER TABLE training_registrations
MODIFY status TINYINT DEFAULT 1 COMMENT '1=รอชำระเงิน / รออนุมัติ, 2=อนุมัติแล้ว, 3=ไม่อนุมัติ, 4=ยกเลิก, 5=เข้าอบรมแล้ว';
