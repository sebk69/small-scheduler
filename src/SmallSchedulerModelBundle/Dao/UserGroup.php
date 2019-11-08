<?php
namespace App\SmallSchedulerModelBundle\Dao;

use Sebk\SmallOrmBundle\Dao\AbstractDao;

class UserGroup extends AbstractDao
{
    protected function build()
    {
        $this->setDbTableName("user_group")
            ->setModelName("UserGroup")
            ->addPrimaryKey("id", "id")
            ->addField("user_id", "userId")
            ->addField("group_id", "groupId")
            ->addToOne("userGroupGroup", ["groupId" => "id"], "Group")
            ->addToOne("userGroupUser", ["userId" => "id"], "User")
        ;
    }
}