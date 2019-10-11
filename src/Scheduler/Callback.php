<?php
/**
 * This file is a part of SmallScheduler
 * Copyright (c) 2019 Sébastien Kus
 * Under GNU GPL Licence
 */

namespace App\Scheduler;


use App\SmallSchedulerModelBundle\Dao\Parameter;
use App\SmallSchedulerModelBundle\Dao\Task;
use App\SmallSchedulerModelBundle\Dao\TaskExecutionLog;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Sebk\SmallOrmBundle\Factory\Dao;

class Callback
{
    const QUEUE_CALLBACK = "SmallSchedulerCallback";

    public $daoFactory;
    public $mailer;
    public $twig;

    public function __construct(Dao $daoFactory, \Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->daoFactory = $daoFactory;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * Get email from
     * @return mixed
     * @throws \ReflectionException
     * @throws \Sebk\SmallOrmBundle\Dao\DaoEmptyException
     * @throws \Sebk\SmallOrmBundle\Dao\DaoException
     * @throws \Sebk\SmallOrmBundle\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmBundle\Factory\DaoNotFoundException
     */
    public function getEmailFrom()
    {
        /** @var Parameter $daoParameter */
        $daoParameter = $this->daoFactory->get("SmallSchedulerModelBundle", "Parameter");
        /** @var \App\SmallSchedulerModelBundle\Model\Parameter $parameter */
        $parameter = $daoParameter->findOneBy(["key" => Parameter::EMAIL_FROM]);

        return $parameter->getValue();
    }

    /**
     * Validate task
     * @param string $taskId
     * @param string $queue
     * @param string $command
     * @param int $returnValue
     * @param string $stdout
     * @param string $stderr
     * @throws \ReflectionException
     * @throws \Sebk\SmallOrmBundle\Dao\DaoEmptyException
     * @throws \Sebk\SmallOrmBundle\Dao\DaoException
     * @throws \Sebk\SmallOrmBundle\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmBundle\Factory\DaoNotFoundException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function validate(string $taskId, string $queue, string $command, int $returnValue, string $stdout, string $stderr)
    {
        // Check task exists
        /** @var Task $daoTask */
        $daoTask = $this->daoFactory->get("SmallSchedulerModelBundle", "Task");
        /** @var \App\SmallSchedulerModelBundle\Model\Task $task */
        $task = $daoTask->findOneBy(["id" => $taskId]);

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

        // Send notification on failure
        if($returnValue != 0) {
            // Retrieve destination emails
            $task->loadToOne("taskGroup");
            $task->getTaskGroup()->loadToMany("tasksFailuresNotifications");
            $emails = [];
            foreach ($task->getTaskGroup()->getTasksFailuresNotifications() as $taskFailureNotification) {
                $taskFailureNotification->loadToOne("taskFailureNotificationUser");
                $emails[] = $taskFailureNotification->getTaskFailureNotificationUser()->getEmail();
            }

            // Send message
            $message = (new \Swift_Message("Small Scheduler - Task failure - ".$task->getTaskGroup()->getLabel()))
                ->setFrom($this->getEmailFrom())
                ->setTo($emails)
                ->setBody($this->twig->render("notifications/failureNotification.email.twig", [
                    "group" => $task->getTaskGroup()->getLabel(),
                    "queue" => $queue,
                    "command" => $command,
                    "returnValue" => $returnValue,
                    "stdOut" => $stdout,
                    "stdErr" => $stderr
                ]), "text/html");
            $this->mailer->send($message);
        }
    }

    /**
     * Callback message
     * @param AMQPMessage $message
     * @return bool
     * @throws \ReflectionException
     * @throws \Sebk\SmallOrmBundle\Dao\DaoEmptyException
     * @throws \Sebk\SmallOrmBundle\Dao\DaoException
     * @throws \Sebk\SmallOrmBundle\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmBundle\Factory\DaoNotFoundException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
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

    /**
     * Queue initialization
     * @throws \ErrorException
     */
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