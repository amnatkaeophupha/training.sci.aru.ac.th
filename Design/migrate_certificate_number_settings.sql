-- MariaDB 10.4+ (the project's current database). Additive and safe to rerun.
-- Existing CERT numbers and template/name settings are preserved.
ALTER TABLE training_certificate_templates
    ADD COLUMN IF NOT EXISTS number_x DECIMAL(6,3) NOT NULL DEFAULT 70.707,
    ADD COLUMN IF NOT EXISTS number_y DECIMAL(6,3) NOT NULL DEFAULT 93.333,
    ADD COLUMN IF NOT EXISTS number_width DECIMAL(6,3) NOT NULL DEFAULT 25.253,
    ADD COLUMN IF NOT EXISTS number_font_size DECIMAL(6,2) NOT NULL DEFAULT 9.00,
    ADD COLUMN IF NOT EXISTS number_font_color VARCHAR(7) NOT NULL DEFAULT '#282828',
    ADD COLUMN IF NOT EXISTS number_align CHAR(1) NOT NULL DEFAULT 'R';

CREATE TABLE IF NOT EXISTS training_certificate_sequences (
    certificate_year SMALLINT UNSIGNED NOT NULL PRIMARY KEY,
    last_sequence INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO training_certificate_sequences (certificate_year,last_sequence)
SELECT CAST(SUBSTRING(certificate_no,5,4) AS UNSIGNED), MAX(CAST(RIGHT(certificate_no,6) AS UNSIGNED))
FROM training_certificates WHERE certificate_no REGEXP '^SCI-[0-9]{4}-[0-9]{6}$'
GROUP BY SUBSTRING(certificate_no,5,4)
ON DUPLICATE KEY UPDATE last_sequence=GREATEST(last_sequence,VALUES(last_sequence));
