<?php
namespace App\SmallSchedulerModelBundle\Dao;

use Sebk\SmallOrmBundle\Dao\AbstractDao;

class TaskExecutionLog extends AbstractDao
{
    protected function build()
    {
        $this->setDbTableName("task_execution_log")
            ->setModelName("TaskExecutionLog")
            ->addPrimaryKey("id", "id")
            ->addField("task_id", "taskId")
            ->addField("queue", "queue")
            ->addField("command", "command")
            ->addField("return_value", "returnValue")
            ->addField("stdout", "stdout")
            ->addField("stderr", "stderr")
            ->addField("date", "date")
            ->addToOne("executionLogTask", ["taskId" => "id"], "Task")
        ;
    }

    /**
     * Get execution logs for a task
     * @param $taskId
     * @return \App\SmallSchedulerModelBundle\Model\TaskExecutionLog[]
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\BracketException
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\QueryBuilderException
     */
    public function getForTask($taskId, $maxEntries, $fromString, $toString)
    {
        $query = $this->createQueryBuilder("taskExecutionLog");

        $query->where()
            ->firstCondition($query->getFieldForCondition("taskId"), "=", ":taskId");
        $query->setParameter("taskId", $taskId);

        if(!empty($fromString)) {
            $query->getWhere()
                ->andCondition($query->getFieldForCondition("date"), ">=", ":fromString");
            $query->setParameter("fromString", $fromString);
        }

        if(!empty($toString)) {
            $query->getWhere()
                ->andCondition($query->getFieldForCondition("date"), "<=", ":toString");
            $query->setParameter("toString", $toString);
        }

        $query->addOrderBy("date", null, "DESC");

        $query->limit(0, $maxEntries);

        return $this->getResult($query);
    }

    /**
     * Get lasts errors
     * @param $number
     * @return \App\SmallSchedulerModelBundle\Model\TaskExecutionLog[]
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\BracketException
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\QueryBuilderException
     */
    public function getLastsErrors($number)
    {
        $query = $this->createQueryBuilder("taskExecutionLog")
            ->innerJoin("taskExecutionLog", "executionLogTask")->endJoin()
        ;

        $query->where()
            ->firstCondition($query->getFieldForCondition("returnValue"), "<>", 0)
        ;

        $query->addOrderBy("date", null, "DESC");

        $query->limit(0, $number);

        return $this->getResult($query);
    }

    /**
     * Purge logs for a task and a period
     * @param $taskId
     * @param $days
     * @throws \Sebk\SmallOrmBundle\Database\ConnectionException
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\BracketException
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\QueryBuilderException
     */
    public function purge($taskId, $days)
    {
        // Set date limit
        $date = new \DateTime();
        $date->sub(new \DateInterval("P".$days."D"));

        // create query
        $query = $this->createDeleteBuilder();
        $query->where()
            ->firstCondition($query->getFieldForCondition("taskId"), "=", ":taskId")
            ->andCondition($query->getFieldForCondition("date"), "<", ":date");

        $query->setParameter("taskId", $taskId)
            ->setParameter("date", $date->format("Y-m-d H:i:s"));

        $this->executeDelete($query);
    }
}