<?php

namespace MoriniereRobinDev\WebFramework\MvcrBase;

abstract class Router {
    protected $request;
    protected $controllerClassName;
    protected $controllerAction;

    public function __construct($request){
        $this->request = $request;
        $this->parseRequest();
    }
    
    public function getControllerClassName(){
        return $this->controllerClassName;
    }

    public function getControllerAction(){
        return $this->controllerAction;
    }
    
    
    public abstract function parseRequest();


}