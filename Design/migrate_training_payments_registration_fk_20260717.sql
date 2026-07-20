-- Remove orphan rows before adding the relationship constraint.
DELETE p
FROM training_payments p
LEFT JOIN training_registrations r ON r.id = p.registration_id
WHERE r.id IS NULL;

ALTER TABLE training_payments
    ADD CONSTRAINT fk_training_payments_registration
    FOREIGN KEY (registration_id)
    REFERENCES training_registrations(id)
    ON DELETE CASCADE;
