<?php
/**
 * This file is a part of SmallScheduler
 * Copyright (c) 2019 Sébastien Kus
 * Under GNU GPL Licence
 */

namespace App\Command;


use App\Scheduler\Submit;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SchedulerCommand
 * @package App\Command
 */
class SchedulerCommand extends Command
{
    protected $submit;

    /**
     * SchedulerCommand constructor.
     * @param Submit $submit
     */
    public function __construct(Submit $submit)
    {
        $this->submit = $submit;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('small-scheduler:scheduler-exec')
            ->setDescription('Scheduler : execute crons')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->submit->testTasks();
    }

}