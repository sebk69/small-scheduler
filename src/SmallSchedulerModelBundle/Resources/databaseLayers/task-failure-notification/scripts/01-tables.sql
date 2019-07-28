START TRANSACTION;

CREATE TABLE `task_failure_notification` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `user_id` INT NOT NULL ,
    `group_id` INT NOT NULL ,
PRIMARY KEY (`id`)) ENGINE = InnoDB;

ALTER TABLE `task_failure_notification`
    ADD CONSTRAINT `task_failure_notification_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `user`(`id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT;
ALTER TABLE `task_failure_notification`
    ADD CONSTRAINT `task_failure_notification_group`
    FOREIGN KEY (`group_id`)
    REFERENCES `group`(`id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT;

COMMIT;