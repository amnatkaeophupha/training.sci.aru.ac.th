CREATE TABLE IF NOT EXISTS training_surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status TINYINT NOT NULL DEFAULT 1 COMMENT '1=draft, 2=active, 3=archived',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS training_survey_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type VARCHAR(30) NOT NULL COMMENT 'rating,single_choice,multiple_choice,short_text,long_text',
    is_required TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    INDEX idx_survey_order (survey_id, sort_order),
    CONSTRAINT fk_survey_questions_survey FOREIGN KEY (survey_id) REFERENCES training_surveys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS training_survey_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    INDEX idx_question_order (question_id, sort_order),
    CONSTRAINT fk_survey_options_question FOREIGN KEY (question_id) REFERENCES training_survey_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS training_survey_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    course_id INT NOT NULL,
    batch_id INT NOT NULL,
    public_code CHAR(64) NOT NULL,
    open_at DATETIME NOT NULL,
    close_at DATETIME NOT NULL,
    status TINYINT NOT NULL DEFAULT 1 COMMENT '1=active, 2=closed, 3=cancelled',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    UNIQUE KEY unique_public_code (public_code),
    INDEX idx_batch_status (batch_id, status),
    CONSTRAINT fk_survey_assignments_survey FOREIGN KEY (survey_id) REFERENCES training_surveys(id) ON DELETE RESTRICT,
    CONSTRAINT fk_survey_assignments_course FOREIGN KEY (course_id) REFERENCES training_courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_survey_assignments_batch FOREIGN KEY (batch_id) REFERENCES training_batches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS training_survey_invitations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    participant_id INT NOT NULL,
    profile_confirmed_at DATETIME NULL,
    completed_at DATETIME NULL,
    certificate_status TINYINT NOT NULL DEFAULT 0 COMMENT '0=not_ready,1=ready,2=issued',
    certificate_reference VARCHAR(255) NULL,
    verify_attempts INT NOT NULL DEFAULT 0,
    verify_locked_until DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    UNIQUE KEY unique_assignment_participant (assignment_id, participant_id),
    INDEX idx_assignment_completed (assignment_id, completed_at),
    CONSTRAINT fk_survey_invitations_assignment FOREIGN KEY (assignment_id) REFERENCES training_survey_assignments(id) ON DELETE CASCADE,
    CONSTRAINT fk_survey_invitations_participant FOREIGN KEY (participant_id) REFERENCES training_registration_participants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS training_survey_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invitation_id INT NOT NULL,
    submitted_at DATETIME NOT NULL,
    UNIQUE KEY unique_invitation_response (invitation_id),
    CONSTRAINT fk_survey_responses_invitation FOREIGN KEY (invitation_id) REFERENCES training_survey_invitations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS training_survey_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    response_id INT NOT NULL,
    question_id INT NOT NULL,
    rating_value TINYINT NULL,
    text_value TEXT NULL,
    INDEX idx_response (response_id),
    INDEX idx_question (question_id),
    CONSTRAINT fk_survey_answers_response FOREIGN KEY (response_id) REFERENCES training_survey_responses(id) ON DELETE CASCADE,
    CONSTRAINT fk_survey_answers_question FOREIGN KEY (question_id) REFERENCES training_survey_questions(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS training_survey_answer_options (
    answer_id INT NOT NULL,
    option_id INT NOT NULL,
    PRIMARY KEY (answer_id, option_id),
    CONSTRAINT fk_answer_options_answer FOREIGN KEY (answer_id) REFERENCES training_survey_answers(id) ON DELETE CASCADE,
    CONSTRAINT fk_answer_options_option FOREIGN KEY (option_id) REFERENCES training_survey_options(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS training_survey_profile_audits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invitation_id INT NOT NULL,
    participant_id INT NOT NULL,
    before_data TEXT NULL,
    after_data TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NULL,
    INDEX idx_invitation (invitation_id),
    CONSTRAINT fk_survey_audits_invitation FOREIGN KEY (invitation_id) REFERENCES training_survey_invitations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
