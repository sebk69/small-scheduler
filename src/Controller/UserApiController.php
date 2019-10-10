<?php
/**
 * This file is a part of SmallScheduler
 * Copyright (c) 2019 Sébastien Kus
 * Under GNU GPL Licence
 */

namespace App\Controller;


use App\Security\LostPassword;
use App\Security\ResetPassword;
use App\Security\ResetPasswordForm;
use App\SmallSchedulerModelBundle\Dao\Token;
use Sebk\SmallOrmBundle\Dao\AbstractDao;
use Sebk\SmallOrmBundle\Dao\DaoEmptyException;
use Sebk\SmallOrmBundle\Factory\Dao;
use Sebk\SmallUserBundle\Controller\AbstractUserApiController;
use Sebk\SmallUserBundle\Security\UserProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    /**
     * @route("/security/passwordLost", methods={"POST"})
     * @param LostPassword $lostPassword
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function sendLostPasswordEmail(LostPassword $lostPassword, Request $request)
    {
        // Get data from body
        $data = json_decode($request->getContent(), true);

        $lostPassword->sendEmail($data["username"]);

        return new Response("");
    }

    /**
     * @route("/security/resetPassword", methods={"GET"}, name="reset_password_form")
     * @param Dao $daoFactory
     * @param Request $request
     * @return Response
     * @throws DaoEmptyException
     * @throws \ReflectionException
     * @throws \Sebk\SmallOrmBundle\Dao\DaoException
     * @throws \Sebk\SmallOrmBundle\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmBundle\Factory\DaoNotFoundException
     */
    public function resetPasswordForm(Dao $daoFactory, Request $request)
    {
        /** @var Token $tokenDao */
        $tokenDao = $daoFactory->get("SmallSchedulerModelBundle", "Token");

        try {
            /** @var \App\SmallSchedulerModelBundle\Model\Token $token */
            $token = $tokenDao->findOneBy(["token" => $request->get("token")]);
        } catch (\Exception $e) {
            return new Response("Invalid token", Response::HTTP_UNAUTHORIZED);
        }
        $tokenData = json_decode($token->getData(), true);
        if($tokenData["expirationDate"] > (new \DateTime())->format("U")) {
            $data = (new ResetPassword())
                ->setToken($request->get("token"))
            ;
            $form = $this->createForm(ResetPasswordForm::class, $data);

            return $this->render('security/resetPassword.html.twig', ["form" => $form->createView()]);
        } else {
            return new Response("Token expired", Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @route("/security/resetPassword", methods={"POST"}, name="reset_password_submit")
     * @param Dao $daoFactory
     * @param UserProvider $userProvider
     * @param Request $request
     * @return Response
     * @throws DaoEmptyException
     * @throws \ReflectionException
     * @throws \Sebk\SmallOrmBundle\Dao\DaoException
     * @throws \Sebk\SmallOrmBundle\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmBundle\Factory\DaoNotFoundException
     */
    public function resetPasswordSubmit(Dao $daoFactory, UserProvider $userProvider, Request $request)
    {
        $data = new ResetPassword();
        $form = $this->createForm(ResetPasswordForm::class, $data);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if($data->getPassword() == $data->getPasswordConfirm()) {
                /** @var Token $tokenDao */
                $tokenDao = $daoFactory->get("SmallSchedulerModelBundle", "Token");
                /** @var \App\SmallSchedulerModelBundle\Model\Token $token */
                $token = $tokenDao->findOneBy(["token" => $data->getToken()]);
                $tokenData = json_decode($token->getData(), true);
                if($tokenData["expirationDate"] > time()) {
                    try {
                        $user = $userProvider->loadUserById($tokenData["userId"]);
                    } catch (DaoEmptyException $e) {
                        return new Response("Invalid token", Response::HTTP_UNAUTHORIZED);
                    }

                    try {
                        $userProvider->updateUser($user, $data->getPassword());
                    } catch (\Exception $e) {
                        $this->addFlash($e->getMessage());
                        return $this->render('security/resetPassword.html.twig', ["form" => $form->createView()]);
                    }

                    $token->delete();

                    return $this->render('security/resetPasswordDone.html.twig');
                } else {
                    return new Response("Token expired", Response::HTTP_UNAUTHORIZED);
                }
            } else {
                $this->addFlash("error", "Password and confirmation don't match");
                return $this->render('security/resetPassword.html.twig', ["form" => $form->createView()]);
            }
        } else {
            return new Response("Invalid token", Response::HTTP_UNAUTHORIZED);
        }
    }
}