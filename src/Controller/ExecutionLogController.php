<?php
/**
 * This file is a part of SmallScheduler
 * Copyright (c) 2019 Sébastien Kus
 * Under GNU GPL Licence
 */

namespace App\Controller;


use App\SmallSchedulerModelBundle\Dao\Task;
use App\SmallSchedulerModelBundle\Dao\TaskExecutionLog;
use Sebk\SmallOrmBundle\Dao\DaoEmptyException;
use Sebk\SmallOrmBundle\Factory\Dao;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExecutionLogController
{
    /**
     * @Route("/api/taskExecutionLogs/{taskId}", methods={"GET"})
     */
    public function getLogs($taskId, Dao $daoFactory, Request $request)
    {
        // Check task exists
        /** @var Task $daoTask */
        $daoTask = $daoFactory->get("SmallSchedulerModelBundle", "Task");
        try {
            $daoTask->findOneBy(["id" => $taskId]);
        } catch (DaoEmptyException $e) {
            return new Response("Task don't exists", Response::HTTP_BAD_REQUEST);
        }

        // Check date format
        if (!empty($request->get("from"))) {
            if(\DateTime::createFromFormat("Y-m-d H:i:s", $request->get("from")) === false) {
                return new Response("Date from is in wrong format", "400");
            }
        }
        if (!empty($request->get("to"))) {
            if(\DateTime::createFromFormat("Y-m-d H:i:s", $request->get("to")) === false) {
                return new Response("Date to is in wrong format", "400");
            }
        }

        // Default to 50 entries
        $entries = $request->get("maxEntries");
        if(empty($entries) || !is_numeric($entries)) {
            $entries = 50;
        }

        // Get logs
        /** @var TaskExecutionLog $daoLogs */
        $daoLogs = $daoFactory->get("SmallSchedulerModelBundle", "TaskExecutionLog");
        $logs = $daoLogs->getForTask($taskId, $entries, $request->get("from"), $request->get("to"));

        // Return response
        return new Response(json_encode($logs));
    }

    /**
     * @Route("/api/taskExecutionLogs/errors/{number}", methods={"GET"})
     */
    public function getLastLogs($number, Dao $daoFactory) {
        // Instance to dao
        /** @var \App\SmallSchedulerModelBundle\Dao\TaskExecutionLog $daoLogs */
        $daoLogs = $daoFactory->get("SmallSchedulerModelBundle", "TaskExecutionLog");

        $logs = $daoLogs->getLastsErrors($number);

        return new Response(json_encode($logs));
    }
}