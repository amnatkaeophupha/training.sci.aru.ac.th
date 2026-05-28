-- Update training_batches status mapping:
-- 1 = เปิดรับสมัคร
-- 2 = ปิดรับสมัคร
-- 3 = เปิดรับเพิ่ม
-- 4 = ยกเลิก

UPDATE training_batches
SET status = 4
WHERE status = 3;
