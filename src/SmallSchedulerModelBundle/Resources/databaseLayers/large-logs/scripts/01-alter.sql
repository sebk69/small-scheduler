START TRANSACTION;

ALTER TABLE `task_execution_log`
MODIFY `stdout` LONGTEXT;

ALTER TABLE `task_execution_log`
MODIFY `stderr` LONGTEXT;

COMMIT;
