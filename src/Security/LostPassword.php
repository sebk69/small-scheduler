<?php

/**
 *  This file is a part of SmallScheduler
 *  Copyright 2019 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Security;


use App\SmallSchedulerModelBundle\Dao\Token;
use Sebk\SmallOrmBundle\Factory\Dao;
use Sebk\SmallUserBundle\Security\UserProvider;

class LostPassword
{
    protected $daoFactory;
    protected $userProvider;
    protected $mailer;
    protected $twig;

    public function __construct(Dao $daoFactory, UserProvider $userProvider, \Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->daoFactory = $daoFactory;
        $this->userProvider = $userProvider;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendEmail($username)
    {
        // Get user
        $userModel = $this->userProvider->getModelByUsername($username);

        // Generate token
        $data = [
            "userId" => $userModel->getId(),
            "expirationDate" => date('U', strtotime("+1 day"))
        ];
        /** @var Token $tokenDao */
        $tokenDao = $this->daoFactory->get("SmallSchedulerModelBundle", "Token");
        $token = $tokenDao->generate($data);

        // Send message
        $message = (new \Swift_Message("Lost password"))
            ->setFrom($userModel->getEmail())
            ->setTo($userModel->getEmail())
            ->setBody($this->twig->render("security/lostPassword.email.twig", [
                "token" => $token->getToken(),
            ]), "text/html");
        if(!$this->mailer->send($message)) {
            throw new \Exception("Fail to send email");
        }
    }
}