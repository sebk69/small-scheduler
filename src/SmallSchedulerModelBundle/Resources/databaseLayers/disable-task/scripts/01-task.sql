ALTER TABLE `task`
ADD COLUMN `enabled` INT(1) NOT NULL DEFAULT 1 AFTER `sent_trace`;
