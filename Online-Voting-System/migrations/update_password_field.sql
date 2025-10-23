-- Migration to update password field for bcrypt hashing
-- Bcrypt hashes are 60 characters long, so we need to increase the field size

USE polltest;

-- Update the password field to accommodate bcrypt hashes (60 characters)
ALTER TABLE loginusers MODIFY COLUMN password VARCHAR(255) NOT NULL;

-- Add password reset functionality fields
ALTER TABLE loginusers ADD COLUMN reset_token VARCHAR(64) NULL DEFAULT NULL;
ALTER TABLE loginusers ADD COLUMN reset_token_expires DATETIME NULL DEFAULT NULL;
ALTER TABLE loginusers ADD COLUMN password_changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add index for reset token lookups
ALTER TABLE loginusers ADD INDEX idx_reset_token (reset_token);

-- Update existing MD5 passwords to a temporary state (they will need to be reset)
-- We'll mark them for password reset by setting a flag
ALTER TABLE loginusers ADD COLUMN needs_password_reset TINYINT(1) DEFAULT 0;

-- Mark all existing users with MD5 passwords for reset
UPDATE loginusers SET needs_password_reset = 1 WHERE LENGTH(password) = 32;