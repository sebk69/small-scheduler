<?php
/**
 * This file is a part of SmallScheduler
 * Copyright (c) 2019 Sébastien Kus
 * Under GNU GPL Licence
 */

namespace App\Controller;

use App\SmallSchedulerModelBundle\Dao\Group;
use Sebk\SmallOrmBundle\Factory\Dao;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GroupController
 * @package App\Controller
 */
class GroupController extends Controller
{
    /**
     * @Route("/api/groups", methods={"GET"})
     */
    public function getGroups(Dao $daoFactory)
    {
        /** @var Group $groupDao */
        $groupDao = $daoFactory->get("SmallSchedulerModelBundle", "Group");

        return new Response(json_encode($groupDao->findBy(["trash" => 0], [[null => "groupCreationUser"]])));
    }

    /**
     * @Route("/api/groups", methods={"POST"})
     */
    public function postGroups(Dao $daoFactory, Request $request)
    {
        // Instaciate dao
        /** @var Group $groupDao */
        $groupDao = $daoFactory->get("SmallSchedulerModelBundle", "Group");

        // Make model
        /** @var \App\SmallSchedulerModelBundle\Model\Group $group */
        $group = $groupDao->makeModelFromStdClass(json_decode($request->getContent()));

        // Validate
        if($group->getValidator()->validate()) {
            // persist
            $group->persist();

            // Load user
            $group->loadToOne("groupCreationUser");
        } else {
            return new Response($group->getValidator()->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new Response(json_encode($group));
    }

    /**
     * @Route("/api/groups/{id}", methods={"DELETE"})
     */
    public function deleteGroup($id, Dao $daoFactory)
    {
        // Instaciate dao
        /** @var Group $groupDao */
        $groupDao = $daoFactory->get("SmallSchedulerModelBundle", "Group");

        // Load model
        /** @var \App\SmallSchedulerModelBundle\Model\Group $group */
        $group = $groupDao->findOneBy(["id" => $id]);

        // Validate
        if($group->getValidator()->validateDelete()) {
            // persist
            $group->setTrash(1);
            $group->persist();
        } else {
            return new Response($group->getValidator()->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new Response("");
    }
}
