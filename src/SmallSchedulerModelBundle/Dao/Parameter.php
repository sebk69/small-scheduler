<?php
namespace App\SmallSchedulerModelBundle\Dao;

use Sebk\SmallOrmBundle\Dao\AbstractDao;

class Parameter extends AbstractDao
{
    const PURGE_EXECUTION_LOGS = "purge-execution-logs";

    protected function build()
    {
        $this->setDbTableName("parameter")
            ->setModelName("Parameter")
            ->addPrimaryKey("id", "id")
            ->addField("key", "key")
            ->addField("value", "value")
        ;
    }
}