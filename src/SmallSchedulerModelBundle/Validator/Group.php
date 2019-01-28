<?php

namespace App\SmallSchedulerModelBundle\Validator;


use Sebk\SmallOrmBundle\Validator\AbstractValidator;

/**
 * Class Group
 * @package App\SmallSchedulerModelBundle\Validator
 */
class Group extends AbstractValidator
{

    /**
     * Validate a group
     * @return bool
     */
    public function validate()
    {
        // Init
        $this->message = "";
        $validated = true;

        // Check label is not empty
        if(!$this->testNonEmpty("label")) {
            $this->message .= "Label is mandatory\n";
            $validated = false;
        }

        // Check label is unique
        if(!$this->testUniqueWithDeterminant("trash", 0,"label")) {
            $this->message = "Label must be unique\n";
            $validated = false;
        }

        // Return validation
        return $validated;
    }

    /**
     * Validate removing group
     * @return bool
     * @throws \Sebk\SmallOrmBundle\Dao\DaoException
     */
    public function validateDelete()
    {
        // Init
        $this->message = "";
        $validated = true;

        // Check there is no task left
        /** @var \App\SmallSchedulerModelBundle\Dao\Task $taskDao */
        $taskDao = $this->daoFactory->get("SmallSchedulerModelBundle", "Task");
        $tasks = $taskDao->listTaskForGroup($this->model->getId());
        if(count($tasks) > 0) {
            $this->message = "You can't delete group until it has no task left";
            $validated = false;
        }

        // Return validation
        return $validated;
    }

}