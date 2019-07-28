<?php


namespace App\SmallSchedulerModelBundle\Dao;


class User extends \Sebk\SmallUserBundle\Dao\User
{
    public function build()
    {
        parent::build();

        $this->addToMany("taskFailureNotifications", ["id" => "userId"], "taskFailureNotification");
    }
}