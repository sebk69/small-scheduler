<?php
namespace App\SmallSchedulerModelBundle\Dao;

use Sebk\SmallOrmBundle\Dao\AbstractDao;

class Group extends AbstractDao
{
    protected function build()
    {
        $this->setDbTableName("group")
            ->setModelName("Group")
            ->addPrimaryKey("id", "id")
            ->addField("creation_user_id", "creationUserId")
            ->addField("label", "label")
            ->addField("trash", "trash", 0)
            ->addToOne("groupCreationUser", ["creationUserId" => "id"], "User", "SebkSmallUserBundle")
            ->addToMany("tasks", ["id" => "groupId"], "Task")
            ->addToMany("tasksFailuresNotifications", ["id" => "groupId"], "TaskFailureNotification")
        ;
    }
}