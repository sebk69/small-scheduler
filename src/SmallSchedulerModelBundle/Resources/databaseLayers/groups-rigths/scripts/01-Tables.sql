START TRANSACTION;

CREATE TABLE `small_scheduler`.`user_group` (
    `id_user_group` INT NOT NULL,
    `id_user` INT NOT NULL,
    `id_group` INT NOT NULL,
    PRIMARY KEY (`id_user_group`),
    INDEX `userGroupUser_idx` (`id_user` ASC),
    INDEX `userGroupGroup_idx` (`id_group` ASC),
    CONSTRAINT `userGroupUser`
        FOREIGN KEY (`id_user`)
            REFERENCES `small_scheduler`.`user` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
    CONSTRAINT `userGroupGroup`
        FOREIGN KEY (`id_group`)
            REFERENCES `small_scheduler`.`group` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION);

COMMIT;