<?php
namespace App\SmallSchedulerModelBundle\Dao;

use Sebk\SmallOrmBundle\Dao\AbstractDao;

class Token extends AbstractDao
{
    protected function build()
    {
        $this->setDbTableName("token")
            ->setModelName("Token")
            ->addPrimaryKey("id", "id")
            ->addField("token", "token")
            ->addField("data", "data")
        ;
    }
}