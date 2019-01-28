<?php
/**
 * This file is a part of SmallScheduler
 * Copyright (c) 2019 Sébastien Kus
 * Under GNU GPL Licence
 */

namespace App\Command;


use App\Scheduler\Callback;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SchedulerCommand
 * @package App\Command
 */
class CallbackCommand extends Command
{
    protected $callback;


    public function __construct(Callback $callback)
    {
        $this->callback = $callback;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('small-scheduler:callback')
            ->setDescription('Scheduler callback from execution')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->callback->listen();
    }

}