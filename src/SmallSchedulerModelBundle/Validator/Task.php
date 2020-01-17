<?php

namespace App\SmallSchedulerModelBundle\Validator;


use Sebk\SmallOrmBundle\Validator\AbstractValidator;

class Task extends AbstractValidator
{
    /**
     * Validate a task
     * @return bool
     */
    public function validate()
    {
        // Init
        $this->message = "";
        $validated = true;

        // Check group exists
        try {
            $this->model->loadToOne("taskGroup");
            if ($this->model->getTaskGroup() === null) {
                $this->message .= "Group is not exists\n";
                $validated = false;
            }
        } catch (\Exception $e) {
            $this->message .= "Group is not exists\n";
            $validated = false;
        }

        // Check command is not empty
        if (!$this->testNonEmpty("command")) {
            $this->message .= "Command is required\n";
            $validated = false;
        }

        // Check cron syntax
        if (!$this->model->checkCronSyntax()) {
            $this->message .= "There is a syntax error in schedule\n";
            $validated = false;
        } else {
            try {
                $this->model->timeToLaunch(date('i G j n w'));
            } catch(\ParseError $e) {
                $this->message .= "There is a syntax error in schedule\n";
                $validated = false;
            }
        }

        return $validated;
    }
}