<?php
/**
 * This file is a part of SmallScheduler
 * Copyright (c) 2019 Sébastien Kus
 * Under GNU GPL Licence
 */


namespace App\Controller;


use App\SmallSchedulerModelBundle\Dao\TaskFailureNotification;
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
}