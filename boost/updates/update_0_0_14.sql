ALTER TABLE intern_student DROP COLUMN ugrad_major;
ALTER TABLE intern_student ADD COLUMN ugrad_major INT DEFAULT NULL REFERENCES intern_major(id);