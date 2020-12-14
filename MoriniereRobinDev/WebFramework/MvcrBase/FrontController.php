<?php

namespace MoriniereRobinDev\WebFramework\MvcrBase;

use MoriniereRobinDev\PoemApp\Model;
use MoriniereRobinDev\PoemApp\Router;
use MoriniereRobinDev\WebFramework\Service\Authentification;

class FrontController
{
    protected $request;
    protected $response;
    protected $template;

    public function __construct($request, $response, $template)
    {
        $this->request = $request;
        $this->response = $response;
        $this->template = $template;
    }

    public function execute()
    {
        $view = new \MoriniereRobinDev\DevoirApp\Model\ViewApp($this->template, $this);
        $authManager = new \MoriniereRobinDev\WebFramework\Service\Authentification\AuthManager();

        $router = new \MoriniereRobinDev\DevoirApp\Router\RouterApp($this->request);
        $className = $router->getControllerClassName();
        $action = $router->getControllerAction();
        $id = $router->getControllerId();

        $controller = new $className($this->request, $this->response, $view, $authManager); //, $control
        $controller->execute($action, $id);

        if ($this->request->isAjaxRequest()) {
            $content = $view->getPart('content');
        } else {
            $content = $view->render();
        }

        $this->response->send($content);
    }

    public function POSTredirect($url, $feedback)
    {
        $_SESSION['feedback'] = $feedback;
        header("Location: ".htmlspecialchars_decode($url), true, 303);
        die;
    }
}
