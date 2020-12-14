<?php

namespace MoriniereRobinDev\WebFramework\MvcrBase;

abstract class Router
{
    protected $request;
    protected $controllerClassName;
    protected $controllerAction;
    protected $controllerId;

    public function __construct($request)
    {
        $this->request = $request;
        $this->parseRequest();
    }

    public function getControllerClassName()
    {
        return $this->controllerClassName;
    }

    public function getControllerAction()
    {
        return $this->controllerAction;
    }

    public function getControllerId()
    {
        return $this->controllerId;
    }


    abstract public function parseRequest();
}
