<?php
/**
 * This file is a part of SmallScheduler
 * Copyright (c) 2019 Sébastien Kus
 * Under GNU GPL Licence
 */

namespace App\Scheduler;


use App\SmallSchedulerModelBundle\Dao\Task;
use App\SmallSchedulerModelBundle\Dao\TaskExecutionLog;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Sebk\SmallOrmBundle\Factory\Dao;

class Callback
{
    const QUEUE_CALLBACK = "SmallSchedulerCallback";

    protected $daoFactory;

    public function __construct(Dao $daoFactory)
    {
        $this->daoFactory = $daoFactory;
    }

    protected function validate(string $taskId, string $queue, string $command, int $returnValue, string $stdout, string $stderr)
    {
        // Check task exists
        /** @var Task $daoTask */
        $daoTask = $this->daoFactory->get("SmallSchedulerModelBundle", "Task");
        $daoTask->findOneBy(["id" => $taskId]);

        // Create log
        /** @var TaskExecutionLog $daoLog */
        $daoLog = $this->daoFactory->get("SmallSchedulerModelBundle", "TaskExecutionLog");
        /** @var \App\SmallSchedulerModelBundle\Model\TaskExecutionLog $log */
        $log = $daoLog->newModel();
        $log->setTaskId($taskId);
        $log->setQueue($queue);
        $log->setCommand($command);
        $log->setReturnValue($returnValue);
        $log->setStdout($stdout);
        $log->setStderr($stderr);
        $log->setDate(date("Y-m-d H:i:s"));

        $log->persist();
    }

    public function callback(AMQPMessage $message)
    {
        $messageDecoded = json_decode($message->body, true);
        static::validate(
            $messageDecoded["id"],
            $messageDecoded["queue"],
            $messageDecoded["command"],
            $messageDecoded["returnValue"],
            $messageDecoded["stdout"],
            $messageDecoded["stderr"]
        );

        return true;
    }

    public function listen()
    {
        // Initialize message broker
        $connection = new AMQPStreamConnection("message-broker", 5672, "guest", "guest");
        $channel = $connection->channel();
        $channel->queue_declare(static::QUEUE_CALLBACK, false, false, false, false);

        // Consume
        $channel->basic_consume(static::QUEUE_CALLBACK, '', false, true, false, false, [$this, "callback"]);
        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}