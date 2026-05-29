ALTER TABLE training_registration_participants
ADD member_id INT NULL AFTER registration_id,
ADD participant_type VARCHAR(50) DEFAULT 'student' AFTER member_id,
ADD is_main_member TINYINT DEFAULT 0 AFTER participant_type;