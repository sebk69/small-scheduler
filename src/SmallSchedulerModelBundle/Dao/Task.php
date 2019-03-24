<?php
namespace App\SmallSchedulerModelBundle\Dao;

use Sebk\SmallOrmBundle\Dao\AbstractDao;

class Task extends AbstractDao
{
    protected function build()
    {
        $this->setDbTableName("task")
            ->setModelName("Task")
            ->addPrimaryKey("id", "id")
            ->addField("group_id", "groupId")
            ->addField("scheduled_minute", "scheduledMinute")
            ->addField("scheduled_hour", "scheduledHour")
            ->addField("scheduled_day", "scheduledDay")
            ->addField("scheduled_month", "scheduledMonth")
            ->addField("scheduled_weekday", "scheduledWeekday")
            ->addField("command", "command")
            ->addField("queue", "queue")
            ->addField("trash", "trash", "0")
            ->addField("sent_trace", "sentTrace")
            ->addField("enabled", "enabled", "1")
            ->addToOne("taskGroup", ["groupId" => "id"], "Group")
            ->addToMany("tasksChangesLogs", ["id" => "taskId"], "TaskChangeLog")
            ->addToMany("tasksExecutionsLogs", ["id" => "taskId"], "TaskExecutionLog")
        ;
    }

    /**
     * List task for a group
     * @param $groupId
     * @param bool $withLogs
     * @return \Sebk\SmallOrmBundle\Dao\Model[]
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\BracketException
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\QueryBuilderException
     */
    public function listTaskForGroup($groupId, $withLogs = false)
    {
        $query = $this->createQueryBuilder("task");
        if($withLogs) {
            $query->leftJoin("task", "tasksChangesLogs")->endJoin()
                ->leftJoin("tasksChangesLogs", "taskChangeLogUser")->endJoin();
        }

        $query->where()
            ->firstCondition($query->getFieldForCondition("groupId"), "=", ":groupId")
            ->andCondition($query->getFieldForCondition("trash"), "=", 0)
        ;
        $query->setParameter("groupId", $groupId);

        if($withLogs) {
            $query->addOrderBy("date", "tasksChangesLogs", "DESC");
        }

        return $this->getResult($query);
    }

    /**
     * List all task that has not been removed
     * @param bool $withLogs
     * @return \App\SmallSchedulerModelBundle\Model\Task[]
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\BracketException
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\QueryBuilderException
     */
    public function listAllTasks($withLogs = false)
    {
        $query = $this->createQueryBuilder("task");
        if($withLogs) {
            $query->leftJoin("task", "tasksChangesLogs")->endJoin()
                ->leftJoin("tasksChangesLogs", "taskChangeLogUser")->endJoin();
        }

        $query->where()
            ->firstCondition($query->getFieldForCondition("trash"), "=", 0)
        ;

        if($withLogs) {
            $query->addOrderBy("date", "tasksChangesLogs", "DESC");
        }

        return $this->getResult($query);
    }
}