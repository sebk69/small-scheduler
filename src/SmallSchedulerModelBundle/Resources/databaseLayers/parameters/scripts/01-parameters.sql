START TRANSACTION;

CREATE TABLE `parameter` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `key` VARCHAR(255) NOT NULL ,
    `value` VARCHAR(255) NOT NULL , PRIMARY KEY (`id`)
) ENGINE = InnoDB;

COMMIT;