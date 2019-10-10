<?php
/**
 * This file is a part of SmallScheduler
 * Copyright (c) 2019 Sébastien Kus
 * Under GNU GPL Licence
 */


namespace App\Controller;


use App\SmallSchedulerModelBundle\Dao\TaskFailureNotification;
use Sebk\SmallOrmBundle\Factory\Connections;
use Sebk\SmallOrmBundle\Factory\Dao;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskFailureNotificationController extends Controller
{
    /**
     * @Route("/api/tasks/failure-notification/myself", methods={"GET"})
     */
    public function listForMyself(Dao $daoFactory, Request $request) {
        // get user state
        /** @var TaskFailureNotification $daoTaskFailureNotification */
        $daoTaskFailureNotification = $daoFactory->get("SmallSchedulerModelBundle", "TaskFailureNotification");
        $state = $daoTaskFailureNotification->stateForUser($this->getUser()->getId());

        // reponse
        return new Response(json_encode($state));
    }

    /**
     * @Route("/api/tasks/failure-notification", methods={"POST"})
     */
    public function post(Connections $connections, Dao $daoFactory, Request $request) {
        // Get body
        $data = json_decode($request->getContent());

        // Foreach element
        /** @var TaskFailureNotification $daoTaskFailureNotification */
        $daoTaskFailureNotification = $daoFactory->get("SmallSchedulerModelBundle", "TaskFailureNotification");
        $connections->get()->startTransaction();
        foreach ($data as $element) {
            /** @var \App\SmallSchedulerModelBundle\Model\TaskFailureNotification $taskFailureNotification */
            $taskFailureNotification = $daoTaskFailureNotification->makeModelFromStdClass($element);

            // Check rigths
            if($taskFailureNotification->getUserId() != $this->getUser()->getId()) {
                return new Response("Forbidden", Response::HTTP_UNAUTHORIZED);
            }

            // Update database
            if($taskFailureNotification->getActive() == "1" && !$taskFailureNotification->fromDb) {
                $taskFailureNotification->persist();
            } elseif($taskFailureNotification->getActive() == "0" && $taskFailureNotification->fromDb) {
                $taskFailureNotification->delete();
            }
        }
        $connections->get()->commit();

        // List updated
        return $this->listForMyself($daoFactory, $request);
    }
}