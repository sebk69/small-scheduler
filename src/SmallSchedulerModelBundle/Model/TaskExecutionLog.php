<?php
namespace App\SmallSchedulerModelBundle\Model;

use Sebk\SmallOrmBundle\Dao\Model;
use \App\SmallSchedulerModelBundle\Dao\Parameter;

/**
 * @method getId()
 * @method setId($value)
 * @method getTaskId()
 * @method setTaskId($value)
 * @method getQueue()
 * @method setQueue($value)
 * @method getCommand()
 * @method setCommand($value)
 * @method getReturnValue()
 * @method setReturnValue($value)
 * @method getStdout()
 * @method setStdout($value)
 * @method getStderr()
 * @method setStderr($value)
 * @method getDate()
 * @method setDate($value)
 * @method \App\SmallSchedulerModelBundle\Model\Task getExecutionLogTask()
 */
class TaskExecutionLog extends Model
{
    public function beforeSave()
    {
        // Purge
        /** @var Parameter $daoParameter */
        $daoParameter = $this->container->get("sebk_small_orm_dao")->get("SmallSchedulerModelBundle", "Parameter");
        /** @var \App\SmallSchedulerModelBundle\Model\Parameter $parameter */
        $parameter = $daoParameter->findOneBy(["key" => Parameter::PURGE_EXECUTION_LOGS]);
        $this->getDao()->purge($this->getTaskId(), $parameter->getValue());
    }
}