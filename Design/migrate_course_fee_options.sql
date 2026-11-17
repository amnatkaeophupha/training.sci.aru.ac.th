-- MariaDB 10.4+, additive and repeatable. Backfill only unsnapshotted registrations.
ALTER TABLE training_courses
    ADD COLUMN IF NOT EXISTS fee_mode VARCHAR(20) NOT NULL DEFAULT 'fixed',
    ADD COLUMN IF NOT EXISTS pricing_revision INT UNSIGNED NOT NULL DEFAULT 1;
CREATE TABLE IF NOT EXISTS training_course_fee_options (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    label VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    is_active TINYINT NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    INDEX idx_fee_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Remove current course options together with their course; application snapshots remain independent.
SET @fee_fk_sql = IF(EXISTS(
    SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='training_course_fee_options' AND CONSTRAINT_NAME='fk_training_fee_course'
), 'SELECT 1', 'ALTER TABLE training_course_fee_options ADD CONSTRAINT fk_training_fee_course FOREIGN KEY (course_id) REFERENCES training_courses(id) ON DELETE CASCADE');
PREPARE fee_fk_statement FROM @fee_fk_sql;
EXECUTE fee_fk_statement;
DEALLOCATE PREPARE fee_fk_statement;
ALTER TABLE training_registrations
    ADD COLUMN IF NOT EXISTS fee_mode_snapshot VARCHAR(20) NULL,
    ADD COLUMN IF NOT EXISTS fee_options_snapshot LONGTEXT NULL,
    ADD COLUMN IF NOT EXISTS fee_option_id INT NULL,
    ADD COLUMN IF NOT EXISTS fee_label_snapshot VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS unit_fee_snapshot DECIMAL(10,2) NULL;
UPDATE training_registrations r
JOIN training_batches b ON b.id=r.batch_id JOIN training_courses c ON c.id=b.course_id
SET r.fee_mode_snapshot='fixed', r.unit_fee_snapshot=c.fee,
    r.fee_label_snapshot='ราคาเดิม',r.fee_option_id=0,
    r.fee_options_snapshot=JSON_ARRAY(JSON_OBJECT('id',0,'label','ราคาเดิม','amount',CAST(c.fee AS CHAR)))
WHERE r.unit_fee_snapshot IS NULL;
