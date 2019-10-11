<?php
/**
 * This file is a part of SmallScheduler
 * Copyright (c) 2019 Sébastien Kus
 * Under GNU GPL Licence
 */

namespace App\Scheduler;


use App\SmallSchedulerModelBundle\Dao\Task;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Sebk\SmallOrmBundle\Factory\Dao;

class Submit
{
    const QUEUE_PREFIX = "SmallScheduler#";

    protected $daoFactory;

    public function __construct(Dao $daoFactory)
    {
        $this->daoFactory = $daoFactory;
    }

    /**
     * Test tasks and submit if necessary
     * @throws \ReflectionException
     * @throws \Sebk\SmallOrmBundle\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmBundle\Factory\DaoNotFoundException
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\BracketException
     * @throws \Sebk\SmallOrmBundle\QueryBuilder\QueryBuilderException
     */
    public function testTasks()
    {
        // Get dao
        /** @var Task $taskDao */
        $taskDao = $this->daoFactory->get("SmallSchedulerModelBundle", "Task");

        // Get tasks
        $tasks = $taskDao->listAllTasks();

        // for each task
        foreach ($tasks as $task) {
            // Is it time to launch ?
            if ($task->getEnabled() == 1 && $task->timeToLaunch()) {
                // Check not already launched for this time
                if($task->getSentTrace() != $task->getCurrentTrace()) {
                    // Submit
                    $this->submitTask($task);
                }
            }
        }
    }

    /**
     * Get queue name for a queue number
     * @param $queueNumber
     * @return string
     */
    protected function getQueueName($queueNumber)
    {
        return static::QUEUE_PREFIX.$queueNumber;
    }

    /**
     * Submit task to message broker
     * @param \App\SmallSchedulerModelBundle\Model\Task $task
     */
    public function submitTask(\App\SmallSchedulerModelBundle\Model\Task $task)
    {
        // Initialize message broker
        $connection = new AMQPStreamConnection("message-broker", 5672, "guest", "guest");
        $channel = $connection->channel();
        $channel->queue_declare($this->getQueueName($task->getQueue()), false, false, false, false);

        // Create message
        $message = [
            "id" => $task->getId(),
            "command" => $task->getCommand(),
        ];

        // Send message
        $channel->basic_publish(new AMQPMessage(json_encode($message)), "", $this->getQueueName($task->getQueue()));

        // Save time trace
        $task->saveSentTrace();

        // Close connection
        $channel->close();
        $connection->close();
    }

}