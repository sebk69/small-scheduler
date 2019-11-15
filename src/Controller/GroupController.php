<?php
/**
 * This file is a part of SmallScheduler
 * Copyright (c) 2019 Sébastien Kus
 * Under GNU GPL Licence
 */

namespace App\Controller;

use App\SmallSchedulerModelBundle\Dao\Group;
use App\SmallSchedulerModelBundle\Dao\UserGroup;
use Sebk\SmallOrmBundle\Factory\Connections;
use Sebk\SmallOrmBundle\Factory\Dao;
use App\Security\Voter\GroupVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
        // Get all groups
        /** @var Group $groupDao */
        $groupDao = $daoFactory->get("SmallSchedulerModelBundle", "Group");
        $groups = $groupDao->findBy(["trash" => 0], [[null => "groupCreationUser"]]);

        // filter by rigths
        foreach ($groups as $key => $group) {
            try {
                $this->denyAccessUnlessGranted(GroupVoter::CONTROL, $group);
            } catch (AccessDeniedException $e) {
                unset($groups[$key]);
            }
        }

        return new Response(json_encode(array_values($groups)));
    }

    /**
     * @Route("/api/groups", methods={"POST"})
     */
    public function postGroups(Connections $connections, Dao $daoFactory, Request $request)
    {
        $connections->get()->startTransaction();
        // Instaciate dao
        /** @var Group $groupDao */
        $groupDao = $daoFactory->get("SmallSchedulerModelBundle", "Group");

        // Make model
        /** @var \App\SmallSchedulerModelBundle\Model\Group $group */
        $group = $groupDao->makeModelFromStdClass(json_decode($request->getContent()));

        // Validate
        if($group->getValidator()->validate()) {
            if($group->fromDb) {
                // Check rigths
                $this->denyAccessUnlessGranted(GroupVoter::CONTROL, $group);
                // persist
                $group->persist();
            } else {
                // persist group
                $group->persist();

                // The creator has rigths on group
                /** @var UserGroup $userGroupDao */
                $userGroupDao = $daoFactory->get("SmallSchedulerModelBundle", "UserGroup");
                /** @var \App\SmallSchedulerModelBundle\Model\UserGroup $userGroup */
                $userGroup = $userGroupDao->newModel();
                $userGroup->setUserId($this->getUser()->getId());
                $userGroup->setGroupId($group->getId());
                $userGroup->persist();
            }

            $connections->get()->commit();

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
        $this->denyAccessUnlessGranted(GroupVoter::CONTROL, $group);

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
