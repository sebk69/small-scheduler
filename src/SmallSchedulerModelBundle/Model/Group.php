<?php
namespace App\SmallSchedulerModelBundle\Model;

use Sebk\SmallOrmBundle\Dao\Model;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @method getId()
 * @method setId($value)
 * @method getCreationUserId()
 * @method setCreationUserId($value)
 * @method getLabel()
 * @method setLabel($value)
 * @method getTrash()
 * @method setTrash($value)
 * @method \Sebk\SmallUserBundle\Model\User getGroupCreationUser()
 * @method \App\SmallSchedulerModelBundle\Model\Task[] getTasks()
 * @method \App\SmallSchedulerModelBundle\Model\TaskFailureNotification[] getTasksFailuresNotifications()
 * @method \App\SmallSchedulerModelBundle\Model\UserGroup[] getUsersGroup()
 */
class Group extends Model
{
    public function beforeSave()
    {
        // Get security token
        /** @var TokenStorageInterface $token */
        $token = $this->container->get("security.token_storage");
        if($this->getCreationUserId() === null && $token !== null) {
            // Set creation user
            $this->setCreationUserId($token->getToken()->getUser()->getId());
        }
    }
}