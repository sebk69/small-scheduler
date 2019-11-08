<?php
namespace App\SmallSchedulerModelBundle\Dao;

use Sebk\SmallOrmBundle\Dao\AbstractDao;
use Sebk\SmallOrmBundle\Dao\DaoEmptyException;

class TaskFailureNotification extends AbstractDao
{
    protected function build()
    {
        $this->setDbTableName("task_failure_notification")
            ->setModelName("TaskFailureNotification")
            ->addPrimaryKey("id", "id")
            ->addField("user_id", "userId")
            ->addField("group_id", "groupId")
            ->addToOne("taskFailureNotificationGroup", ["groupId" => "id"], "Group")
            ->addToOne("taskFailureNotificationUser", ["userId" => "id"], "User")
        ;
    }

    /**
     * Get state of groups notifications for a user
     * @param $userId
     * @return array
     * @throws \ReflectionException
     * @throws \Sebk\SmallOrmBundle\Dao\DaoException
     * @throws \Sebk\SmallOrmBundle\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmBundle\Factory\DaoNotFoundException
     */
    public function stateForUser($userId)
    {
        // List groups
        /** @var Group $daoGroup */
        $daoGroup = $this->daoFactory->get("SmallSchedulerModelBundle", "Group");
        /** @var \App\SmallSchedulerModelBundle\Model\Group[] $groups */
        $groups = $daoGroup->findBy(["trash" => "0"]);

        // Build state
        $result = [];
        foreach ($groups as $group) {
            try {
                /** @var \App\SmallSchedulerModelBundle\Model\TaskFailureNotification $model */
                $model = $this->findOneBy(["userId" => $userId, "groupId" => $group->getId()]);
                $model->setActive("1");
            } catch (DaoEmptyException $e) {
                /** @var \App\SmallSchedulerModelBundle\Model\TaskFailureNotification $model */
                $model = $this->newModel();
                $model->setUserId($userId);
                $model->setGroupId($group->getId());
                $model->setActive("0");
            }
            $model->loadToOne("taskFailureNotificationGroup");
            $result[] = $model;
        }

        return $result;
    }
}