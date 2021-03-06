<?php
/**
 * This file is a part of SmallScheduler
 * Copyright (c) 2019 Sébastien Kus
 * Under GNU GPL Licence
 */

namespace App\Controller;


use App\Scheduler\Submit;
use App\Security\Voter\GroupVoter;
use App\SmallSchedulerModelBundle\Dao\Group;
use App\SmallSchedulerModelBundle\Dao\Task;
use App\SmallSchedulerModelBundle\Dao\TaskChangeLog;
use Sebk\SmallOrmBundle\Dao\DaoEmptyException;
use Sebk\SmallOrmBundle\Factory\Connections;
use Sebk\SmallOrmBundle\Factory\Dao;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends Controller
{
    /**
     * @Route("/api/tasks/groups/{groupId}", methods={"GET"})
     */
    public function listTasksForGroup($groupId, Dao $daoFactory, Request $request)
    {
        // Check group exists
        /** @var Group $daoGroup */
        $daoGroup = $daoFactory->get("SmallSchedulerModelBundle", "Group");
        try {
            $group = $daoGroup->findOneBy(["id" => $groupId]);
        } catch (DaoEmptyException $e) {
            return new Response("This group don't exists", 404);
        }
        $this->denyAccessUnlessGranted(GroupVoter::CONTROL, $group);

        // Get tasks
        /** @var Task $daoTask */
        $daoTask = $daoFactory->get("SmallSchedulerModelBundle", "Task");
        /** @var \App\SmallSchedulerModelBundle\Model\Task[] $tasks */
        $tasks = $daoTask->listTaskForGroup($groupId);

        // Response
        return new Response(json_encode($tasks));
    }

    /**
     * @Route("/api/tasks", methods={"POST"})
     */
    public function postTask(Dao $daoFactory, Request $request, Connections $connections)
    {
        // Instaciate dao
        /** @var Task $daoTask */
        $daoTask = $daoFactory->get("SmallSchedulerModelBundle", "Task");

        // Make model
        /** @var \App\SmallSchedulerModelBundle\Model\Task $task */
        $task = $daoTask->makeModelFromStdClass(json_decode($request->getContent()));

        // Check rigths
        /** @var Group $daoGroup */
        $daoGroup = $daoFactory->get("SmallSchedulerModelBundle", "Group");
        $this->denyAccessUnlessGranted(GroupVoter::CONTROL, $daoGroup->findOneBy(["id" => $task->getGroupId()]));

        // Validate
        if($task->getValidator()->validate()) {
            //Begin transaction
            $connections->get()->startTransaction();

            // Create log
            /** @var TaskChangeLog $daoTaskChangeLog */
            $daoTaskChangeLog = $daoFactory->get("SmallSchedulerModelBundle", "TaskChangeLog");
            $log = $daoTaskChangeLog->createFromTask($task, $this->getUser()->getId());

            if($task->fromDb) {
                // Persist log
                if ($log !== null) {
                    $log->persist();
                }
            }

            // Persist
            $task->persist();

            if($log !== null && !$log->fromDb) {
                $log->setTaskId($task->getId());
                $log->persist();
            }

            // Commit
            $connections->get()->commit();
        } else {
            return new Response($task->getValidator()->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new Response(json_encode($task));
    }

    /**
     * @Route("/api/tasks/{id}", methods={"DELETE"})
     */
    public function deleteTask($id, Dao $daoFactory, Connections $connections)
    {
        // Instaciate dao
        /** @var Task $daoTask */
        $daoTask = $daoFactory->get("SmallSchedulerModelBundle", "Task");
        /** @var TaskChangeLog $daoTaskChangeLog */
        $daoTaskChangeLog = $daoFactory->get("SmallSchedulerModelBundle", "TaskChangeLog");

        // Build model
        /** @var \App\SmallSchedulerModelBundle\Model\Task $task */
        $task = $daoTask->findOneBy(["id" => $id]);
        $task->loadToOne("taskGroup");
        $this->denyAccessUnlessGranted(GroupVoter::CONTROL, $task->getTaskGroup());

        // Delete
        $connections->get()->startTransaction();

        // Create log
        /** @var \App\SmallSchedulerModelBundle\Model\TaskChangeLog $log */
        $log = $daoTaskChangeLog->newModel();
        $log->setTaskId($task->getId());
        $log->setUserId($this->getUser()->getId());
        $log->setDate(date("Y-m-d H:i:s"));
        $log->setAction(TaskChangeLog::DELETE_STRING_LOG);
        $log->persist();

        // Delete task
        $task->setTrash(1);
        $task->persist();

        // Commit
        $connections->get()->commit();

        return new Response("");
    }

    /**
     * @route("/api/tasks/{id}/toggleEnabled", methods={"POST"})
     */
    public function toggleTask($id, Dao $daoFactory, Connections $connections)
    {
        // Instaciate dao
        /** @var Task $daoTask */
        $daoTask = $daoFactory->get("SmallSchedulerModelBundle", "Task");
        /** @var TaskChangeLog $daoTaskChangeLog */
        $daoTaskChangeLog = $daoFactory->get("SmallSchedulerModelBundle", "TaskChangeLog");

        // Build model
        /** @var \App\SmallSchedulerModelBundle\Model\Task $task */
        $task = $daoTask->findOneBy(["id" => $id]);

        // Toggle enabled
        if($task->getEnabled() == "1") {
            $task->setEnabled("0");
        } else {
            $task->setEnabled("1");
        }

        // Perist task
        $connections->get()->startTransaction();

        // Create log
        /** @var \App\SmallSchedulerModelBundle\Model\TaskChangeLog $log */
        $log = $daoTaskChangeLog->newModel();
        $log->setTaskId($task->getId());
        $log->setUserId($this->getUser()->getId());
        $log->setDate(date("Y-m-d H:i:s"));
        $log->setAction($task->getEnabled() == "1" ? TaskChangeLog::ENABLE_STRING_LOG : TaskChangeLog::DISABLE_STRING_LOG);
        $log->persist();

        $task->persist();

        $connections->get()->commit();

        // Response task
        return new Response(json_encode($task));
    }

    /**
     * @route("/api/tasks/{id}/execute", methods={"POST"})
     */
    public function execute($id, Dao $daoFactory, Submit $submit, Request $request)
    {
        // Instaciate dao
        /** @var Task $daoTask */
        $daoTask = $daoFactory->get("SmallSchedulerModelBundle", "Task");

        // Get task
        try {
            $task = $daoTask->findOneBy(["id" => $id]);
        } catch (\Exception $e) {
            return new Response("Task not found", Response::HTTP_BAD_REQUEST);
        }

        // submit it
        $submit->submitTask($task);

        return new Response("");
    }
}
