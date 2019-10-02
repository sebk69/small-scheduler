<?php
/**
 * This file is a part of SmallScheduler
 * Copyright (c) 2019 Sébastien Kus
 * Under GNU GPL Licence
 */

namespace App\Controller;


use App\SmallSchedulerModelBundle\Dao\Parameter;
use App\SmallSchedulerModelBundle\Dao\Task;
use App\SmallSchedulerModelBundle\Dao\TaskExecutionLog;
use Sebk\SmallOrmBundle\Dao\DaoEmptyException;
use Sebk\SmallOrmBundle\Factory\Dao;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParameterController
{
    /**
     * @Route("/api/parameter/{key}", methods={"GET"})
     */
    public function getParameter($key, Dao $daoFactory, Request $request)
    {
        /** @var Parameter $daoParameter */
        $daoParameter = $daoFactory->get("SmallSchedulerModelBundle", "Parameter");

        try {
            /** @var \App\SmallSchedulerModelBundle\Model\Parameter $parameterModel */
            $parameterModel = $daoParameter->findOneBy(["key" => $key]);
        } catch (DaoEmptyException $e) {
            /** @var \App\SmallSchedulerModelBundle\Model\Parameter $parameterModel */
            $parameterModel = $daoParameter->newModel();
            $parameterModel->setKey($key);
        }

        // Return response
        return new Response(json_encode($parameterModel));
    }

    /**
     * @Route("/api/parameter", methods={"POST"})
     */
    public function setParameter(Dao $daoFactory, Request $request)
    {
        $data = json_decode($request->getContent(), false);

        /** @var Parameter $daoParameter */
        $daoParameter = $daoFactory->get("SmallSchedulerModelBundle", "Parameter");
        /** @var \App\SmallSchedulerModelBundle\Model\Parameter $parameter */
        $parameter = $daoParameter->makeModelFromStdClass($data);

        $parameter->persist();

        // Return response
        return new Response(json_encode($parameter));
    }

}