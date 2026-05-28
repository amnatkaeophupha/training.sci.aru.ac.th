SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

START TRANSACTION;

INSERT INTO training_categories (name, slug, description, sort_order, is_active, created_at, updated_at) VALUES
('Data Science', 'data-science', 'หลักสูตรด้านการวิเคราะห์ข้อมูล สถิติ และการใช้เครื่องมือดิจิทัลเพื่อสนับสนุนงานวิจัยและชุมชน', 1, 1, NOW(), NOW()),
('Food & Lab', 'food-lab', 'หลักสูตรด้านมาตรฐานห้องปฏิบัติการ ความปลอดภัย และงานอาหาร', 2, 1, NOW(), NOW()),
('Digital Skill', 'digital-skill', 'หลักสูตรทักษะดิจิทัล สื่อออนไลน์ และระบบสนับสนุนงานอบรม', 3, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active),
    updated_at = NOW();

SET @data_category_id = (SELECT id FROM training_categories WHERE slug = 'data-science' LIMIT 1);
SET @food_category_id = (SELECT id FROM training_categories WHERE slug = 'food-lab' LIMIT 1);
SET @digital_category_id = (SELECT id FROM training_categories WHERE slug = 'digital-skill' LIMIT 1);

INSERT INTO training_courses (
    category_id, title, slug, short_description, description, cover_image, level,
    training_type, location, duration_text, capacity, fee, status, is_featured,
    published_at, created_at, updated_at
) VALUES
(
    @data_category_id,
    'การวิเคราะห์ข้อมูลด้วย Python สำหรับงานวิจัยและชุมชน',
    'python-data-analysis',
    'เรียนรู้การจัดการข้อมูล สร้างกราฟ และสรุปผลเชิงสถิติสำหรับงานวิชาการและงานบริการท้องถิ่น',
    'เรียนรู้การจัดการข้อมูล สร้างกราฟ และสรุปผลเชิงสถิติสำหรับงานวิชาการ งานบริการวิชาการ และการพัฒนาชุมชนท้องถิ่น เหมาะสำหรับนักศึกษา บุคลากร นักวิจัย และผู้สนใจทั่วไป',
    '',
    'เริ่มต้นถึงปานกลาง',
    'onsite',
    'ห้องปฏิบัติการคอมพิวเตอร์',
    '2 วัน',
    30,
    1500.00,
    2,
    1,
    '2026-05-25 08:30:00',
    NOW(),
    NOW()
),
(
    @food_category_id,
    'มาตรฐานห้องปฏิบัติการและความปลอดภัยทางอาหาร',
    'food-lab-safety',
    'เตรียมความพร้อมด้านเอกสาร การตรวจประเมิน และแนวปฏิบัติด้านความปลอดภัยสำหรับหน่วยงานและผู้ประกอบการ',
    'อบรมแนวทางการจัดการห้องปฏิบัติการและความปลอดภัยทางอาหาร ครอบคลุมเอกสารที่จำเป็น การเตรียมตรวจประเมิน และแนวปฏิบัติที่เหมาะสมสำหรับหน่วยงานและผู้ประกอบการ',
    '',
    'พื้นฐาน',
    'onsite',
    'อบรมในพื้นที่',
    '1 วัน',
    40,
    900.00,
    2,
    1,
    '2026-05-25 08:30:00',
    NOW(),
    NOW()
),
(
    @digital_category_id,
    'สร้างสื่อดิจิทัลและระบบลงทะเบียนสำหรับงานอบรม',
    'digital-media-registration',
    'พัฒนาทักษะการออกแบบสื่อประชาสัมพันธ์ แบบฟอร์มรับสมัคร และแดชบอร์ดติดตามผลสำหรับผู้จัดโครงการ',
    'เรียนรู้การออกแบบสื่อประชาสัมพันธ์ การสร้างแบบฟอร์มรับสมัครออนไลน์ และแนวคิดการทำแดชบอร์ดติดตามผล เพื่อช่วยให้การจัดโครงการอบรมมีประสิทธิภาพมากขึ้น',
    '',
    'เริ่มต้น',
    'online',
    'ออนไลน์',
    '3 ชั่วโมง',
    60,
    0.00,
    2,
    1,
    '2026-05-25 08:30:00',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    category_id = VALUES(category_id),
    title = VALUES(title),
    short_description = VALUES(short_description),
    description = VALUES(description),
    level = VALUES(level),
    training_type = VALUES(training_type),
    location = VALUES(location),
    duration_text = VALUES(duration_text),
    capacity = VALUES(capacity),
    fee = VALUES(fee),
    status = VALUES(status),
    is_featured = VALUES(is_featured),
    published_at = VALUES(published_at),
    updated_at = NOW();

SET @python_course_id = (SELECT id FROM training_courses WHERE slug = 'python-data-analysis' LIMIT 1);
SET @food_course_id = (SELECT id FROM training_courses WHERE slug = 'food-lab-safety' LIMIT 1);
SET @digital_course_id = (SELECT id FROM training_courses WHERE slug = 'digital-media-registration' LIMIT 1);

DELETE FROM training_course_details WHERE course_id IN (@python_course_id, @food_course_id, @digital_course_id);
DELETE FROM training_course_instructors WHERE course_id IN (@python_course_id, @food_course_id, @digital_course_id);
DELETE FROM training_documents WHERE course_id IN (@python_course_id, @food_course_id, @digital_course_id);
DELETE FROM training_batches WHERE course_id IN (@python_course_id, @food_course_id, @digital_course_id);

INSERT INTO training_batches (
    course_id, batch_no, start_date, end_date, start_time, end_time,
    registration_start, registration_end, capacity, status, created_at, updated_at
) VALUES
(@python_course_id, 'รุ่นที่ 1', '2026-06-18', '2026-06-19', '09:00:00', '16:00:00', '2026-05-25 08:30:00', '2026-06-15 16:30:00', 30, 1, NOW(), NOW()),
(@python_course_id, 'รุ่นที่ 2', '2026-07-10', '2026-07-11', '09:00:00', '16:00:00', '2026-06-01 08:30:00', '2026-07-05 16:30:00', 30, 1, NOW(), NOW()),
(@food_course_id, 'รุ่นที่ 1', '2026-06-25', '2026-06-25', '09:00:00', '16:00:00', '2026-05-25 08:30:00', '2026-06-22 16:30:00', 40, 1, NOW(), NOW()),
(@digital_course_id, 'รุ่นที่ 1', '2026-07-02', '2026-07-02', '13:00:00', '16:00:00', '2026-05-25 08:30:00', '2026-06-30 16:30:00', 60, 1, NOW(), NOW());

INSERT INTO training_course_details (course_id, section_type, title, content, sort_order, created_at, updated_at) VALUES
(@python_course_id, 'learning', 'เข้าใจแนวคิดสำคัญของหลักสูตร', 'สามารถนำแนวคิดการวิเคราะห์ข้อมูลไปใช้กับงานจริงได้', 1, NOW(), NOW()),
(@python_course_id, 'learning', 'ฝึกปฏิบัติผ่านกรณีศึกษา', 'ใช้เครื่องมือที่เหมาะสมกับบริบทของผู้เข้าอบรม เช่น การจัดการข้อมูล การสร้างกราฟ และการสรุปผล', 2, NOW(), NOW()),
(@python_course_id, 'learning', 'รับคำแนะนำจากวิทยากร', 'รับคำแนะนำจากทีมวิทยากรคณะวิทยาศาสตร์และเทคโนโลยี', 3, NOW(), NOW()),
(@python_course_id, 'qualification', 'กลุ่มเป้าหมาย', 'นักศึกษา บุคลากร นักวิจัย และผู้สนใจทั่วไป', 1, NOW(), NOW()),
(@python_course_id, 'document', 'เอกสารประกอบการอบรม', 'ผู้เข้าอบรมจะได้รับเอกสารประกอบการอบรมและแนวทางต่อยอดหลังจบหลักสูตร', 1, NOW(), NOW()),
(@python_course_id, 'note', 'การเตรียมตัว', 'ตรวจสอบข้อมูลรอบอบรมและเตรียมข้อมูลผู้สมัครให้พร้อมก่อนส่งแบบฟอร์ม', 1, NOW(), NOW()),
(@food_course_id, 'learning', 'มาตรฐานและความปลอดภัย', 'เรียนรู้เอกสาร การตรวจประเมิน และแนวปฏิบัติด้านความปลอดภัยทางอาหาร', 1, NOW(), NOW()),
(@food_course_id, 'qualification', 'กลุ่มเป้าหมาย', 'หน่วยงาน ผู้ประกอบการ และผู้รับผิดชอบงานห้องปฏิบัติการหรืออาหาร', 1, NOW(), NOW()),
(@digital_course_id, 'learning', 'สื่อและระบบรับสมัคร', 'ฝึกออกแบบสื่อประชาสัมพันธ์ แบบฟอร์มรับสมัคร และแดชบอร์ดติดตามผลสำหรับงานอบรม', 1, NOW(), NOW()),
(@digital_course_id, 'qualification', 'กลุ่มเป้าหมาย', 'บุคลากรและผู้จัดโครงการอบรมที่ต้องการพัฒนาทักษะดิจิทัล', 1, NOW(), NOW());

INSERT INTO training_instructors (
    name, position, department, email, phone, photo, bio, is_active, created_at, updated_at
) SELECT 'อาจารย์สมชาย ใจดี', 'อาจารย์ประจำหลักสูตรเทคโนโลยีสารสนเทศ', 'คณะวิทยาศาสตร์และเทคโนโลยี', 'somchai@example.com', '035-000-000', 'uploads/instructors/somchai.jpg', 'เชี่ยวชาญด้านการพัฒนาโปรแกรม การวิเคราะห์ข้อมูล และเทคโนโลยีสารสนเทศ', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM training_instructors WHERE email = 'somchai@example.com');

INSERT INTO training_instructors (
    name, position, department, email, phone, photo, bio, is_active, created_at, updated_at
) SELECT 'อาจารย์สุรีย์ พัฒนา', 'นักวิชาการคอมพิวเตอร์', 'คณะวิทยาศาสตร์และเทคโนโลยี', 'suree@example.com', '035-000-001', 'uploads/instructors/suree.jpg', 'เชี่ยวชาญด้านฐานข้อมูล เว็บไซต์ และระบบสารสนเทศสำหรับงานอบรม', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM training_instructors WHERE email = 'suree@example.com');

INSERT INTO training_instructors (
    name, position, department, email, phone, photo, bio, is_active, created_at, updated_at
) SELECT 'ทีมวิทยากรคณะวิทยาศาสตร์และเทคโนโลยี', 'ทีมวิทยากร', 'คณะวิทยาศาสตร์และเทคโนโลยี', 'training@sci.aru.ac.th', '0 3527 6555', '', 'ทีมวิทยากรผู้รับผิดชอบหลักสูตรระยะสั้นและการอบรมเฉพาะทางของคณะ', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM training_instructors WHERE email = 'training@sci.aru.ac.th');

SET @somchai_id = (SELECT id FROM training_instructors WHERE email = 'somchai@example.com' LIMIT 1);
SET @suree_id = (SELECT id FROM training_instructors WHERE email = 'suree@example.com' LIMIT 1);
SET @team_id = (SELECT id FROM training_instructors WHERE email = 'training@sci.aru.ac.th' LIMIT 1);

INSERT INTO training_course_instructors (course_id, instructor_id, role, sort_order, created_at, updated_at) VALUES
(@python_course_id, @somchai_id, 'วิทยากรหลัก', 1, NOW(), NOW()),
(@python_course_id, @suree_id, 'ผู้ช่วยวิทยากร', 2, NOW(), NOW()),
(@food_course_id, @team_id, 'ทีมวิทยากร', 1, NOW(), NOW()),
(@digital_course_id, @suree_id, 'วิทยากรหลัก', 1, NOW(), NOW());

INSERT INTO training_documents (
    course_id, title, file_path, file_type, file_size, sort_order, is_public, created_at, updated_at
) VALUES
(@python_course_id, 'กำหนดการอบรม Python Data Analysis', 'uploads/documents/schedule_python_course.pdf', 'pdf', 245000, 1, 1, NOW(), NOW()),
(@python_course_id, 'เอกสารประกอบการอบรม Python', 'uploads/documents/python_training_material.pdf', 'pdf', 1250000, 2, 1, NOW(), NOW()),
(@food_course_id, 'แนวทางเตรียมเอกสารและตรวจประเมิน', 'uploads/documents/food_lab_checklist.pdf', 'pdf', 320000, 1, 1, NOW(), NOW()),
(@digital_course_id, 'คู่มือสร้างสื่อและแบบฟอร์มรับสมัคร', 'uploads/documents/digital_training_guide.pdf', 'pdf', 410000, 1, 1, NOW(), NOW());

COMMIT;
