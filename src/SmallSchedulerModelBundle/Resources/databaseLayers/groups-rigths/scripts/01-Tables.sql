START TRANSACTION;

CREATE TABLE `small_scheduler`.`user_group` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `group_id` INT NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `userGroupUser_idx` (`user_id` ASC),
    INDEX `userGroupGroup_idx` (`group_id` ASC),
    CONSTRAINT `userGroupUser`
        FOREIGN KEY (`user_id`)
            REFERENCES `small_scheduler`.`user` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
    CONSTRAINT `userGroupGroup`
        FOREIGN KEY (`group_id`)
            REFERENCES `small_scheduler`.`group` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION);

COMMIT;