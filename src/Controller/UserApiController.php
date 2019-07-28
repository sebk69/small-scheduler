<?php
/**
 * This file is a part of SmallScheduler
 * Copyright (c) 2019 Sébastien Kus
 * Under GNU GPL Licence
 */

namespace App\Controller;


use Sebk\SmallOrmBundle\Dao\AbstractDao;
use Sebk\SmallUserBundle\Controller\AbstractUserApiController;
use Sebk\SmallUserBundle\Security\UserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserApiController
 * @package App\Controller
 */
class UserApiController extends AbstractUserApiController
{
    /**
     * Get user DAO
     * @return AbstractDao
     */
    public function getUserDao(): AbstractDao
    {
        return $this->container->get("sebk_small_orm_dao")->get("SmallSchedulerModelBundle", "User");
    }

    /**
     * @Route("/api/login_check", methods={"POST"})
     */
    public function loginCheck() {}

    /**
     * @Route("/api/users/myself", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getUserMyself(Request $request)
    {
        return parent::getUserById(null, $request);
    }

    /**
     * @route("/api/users", methods={"PUT"})
     * @param UserProvider $userProvider
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function putUser(UserProvider $userProvider, Request $request)
    {
        return parent::putUser($userProvider, $request);
    }

    /**
     * @route("/api/users/password", methods={"POST"})
     * @param UserProvider $userProvider
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkPassword(UserProvider $userProvider, Request $request)
    {
        return parent::checkPassword($userProvider, $request);
    }
}