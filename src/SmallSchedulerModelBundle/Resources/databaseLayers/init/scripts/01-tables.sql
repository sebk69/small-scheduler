START TRANSACTION;

CREATE TABLE IF NOT EXISTS `group` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `creation_user_id` INT NOT NULL,
  `label` VARCHAR(255) NOT NULL,
  `trash` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `creation_user_idx` (`creation_user_id` ASC),
  CONSTRAINT `group_creation_user`
    FOREIGN KEY (`creation_user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `task` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `group_id` INT NOT NULL,
  `scheduled_minute` VARCHAR(255) NOT NULL,
  `scheduled_hour` VARCHAR(255) NOT NULL,
  `scheduled_day` VARCHAR(255) NOT NULL,
  `scheduled_month` VARCHAR(255) NOT NULL,
  `scheduled_weekday` VARCHAR(255) NOT NULL,
  `command` TEXT NOT NULL,
  `queue` INT NOT NULL,
  `trash` INT NOT NULL,
  `sent_trace` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX `group` (`group_id` ASC),
  CONSTRAINT `task_group`
    FOREIGN KEY (`group_id`)
    REFERENCES `group` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `task_execution_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `task_id` INT NOT NULL,
  `queue` INT NOT NULL,
  `command` TEXT NOT NULL,
  `return_value` INT NOT NULL,
  `stdout` TEXT NOT NULL,
  `stderr` TEXT NOT NULL,
  `date` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `execution_log_task_idx` (`task_id` ASC),
  CONSTRAINT `execution_log_task`
    FOREIGN KEY (`task_id`)
    REFERENCES `task` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `task_change_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `task_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `action` VARCHAR(255) NOT NULL,
  `date` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `task_change_log_task_idx` (`task_id` ASC),
  INDEX `task_change_log_user_idx` (`user_id` ASC),
  CONSTRAINT `task_change_log_task`
    FOREIGN KEY (`task_id`)
    REFERENCES `task` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `task_change_log_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

COMMIT;