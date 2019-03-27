<?php
namespace App\SmallSchedulerModelBundle\Dao;

use Sebk\SmallOrmBundle\Dao\AbstractDao;
use App\SmallSchedulerModelBundle\Model\Task;
use Sebk\SmallOrmBundle\Dao\DaoEmptyException;
use Sebk\SmallUserBundle\Model\User;

class TaskChangeLog extends AbstractDao
{
    const CREATE_STRING_LOG = "Task as been created";
    const CHANGE_SCHEDULE_STRING_LOG = "Schedule has been changed";
    const CHANGE_COMMAND_STRING_LOG = "Command has been changed";
    const CHANGE_QUEUE_STRING_LOG = "Queue has been changed";
    const DELETE_STRING_LOG = "Task has been removed";
    const ENABLE_STRING_LOG = "Task has been enabled";
    const DISABLE_STRING_LOG = "Task has been disabled";

    protected function build()
    {
        $this->setDbTableName("task_change_log")
            ->setModelName("TaskChangeLog")
            ->addPrimaryKey("id", "id")
            ->addField("task_id", "taskId")
            ->addField("user_id", "userId")
            ->addField("action", "action")
            ->addField("date", "date")
            ->addToOne("taskChangeLogTask", ["taskId" => "id"], "Task")
            ->addToOne("taskChangeLogUser", ["userId" => "id"], "User", "SebkSmallUserBundle")
        ;
    }

    /**
     * Create log from task
     * @param Task $task
     * @param User $user
     * @return \App\SmallSchedulerModelBundle\Model\TaskChangeLog
     * @throws \ReflectionException
     * @throws \Sebk\SmallOrmBundle\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmBundle\Factory\DaoNotFoundException
     */
    public function createFromTask(Task $task, $userId)
    {
        // Create new model
        /** @var \App\SmallSchedulerModelBundle\Model\TaskChangeLog $taskChangeLog */
        $taskChangeLog = $this->newModel();

        // Set properties
        $taskChangeLog->setTaskId($task->getId());
        $taskChangeLog->setUserId($userId);
        $taskChangeLog->setDate(date("Y-m-d H:i:s"));

        // Load db task to compare changes
        try {
            /** @var Task $oldTask */
            $oldTask = $this->daoFactory->get("SmallSchedulerModelBundle", "Task")->findOneBy(["id" => $task->getId()]);
        } catch (DaoEmptyException $e) {
            $taskChangeLog->setAction(static::CREATE_STRING_LOG);

            return $taskChangeLog;
        }

        // Compare changes
        $task->normalizeCommand();
        $changeStrings = [];
        if($task->getCronString() != $oldTask->getCronString()) {
            $changeStrings[] = static::CHANGE_SCHEDULE_STRING_LOG;
        }
        if($task->getCommand() != $oldTask->getCommand()) {
            $changeStrings[] = static::CHANGE_COMMAND_STRING_LOG;
        }
        if($task->getQueue() != $oldTask->getQueue()) {
            $changeStrings[] = static::CHANGE_QUEUE_STRING_LOG;
        }
        $taskChangeLog->setAction(implode(" / ", $changeStrings));

        if(count($changeStrings) > 0) {
            // Return model
            return $taskChangeLog;
        } else {
            return null;
        }
    }

    /**
     * Delete logs of a task
     * @param $taskId
     * @throws \Sebk\SmallOrmBundle\Database\ConnectionException
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\BracketException
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\QueryBuilderException
     */
    public function deleteTask($taskId)
    {
        $query = $this->createDeleteBuilder();
        $query->where()
            ->firstCondition($query->getFieldForCondition("taskId"), "=", ":taskId");
        $query->setParameter("taskId", $taskId);

        $this->executeDelete($query);
    }
}