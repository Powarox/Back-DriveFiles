<?php

namespace MoriniereRobinDev\DevoirApp\Router;

use MoriniereRobinDev\DevoirApp\Router;
use MoriniereRobinDev\DevoirApp\Model;
use MoriniereRobinDev\WebFramework;

class RouterApp extends WebFramework\MvcrBase\Router{
    public function parseRequest(){
        // un nom de package est-il spécifié dans l'URL ?
        $package = $this->request->getGetParam('obj');

        // Regarder quel contrôleur instancier
        switch ($package) {
            case '':
                $this->controllerClassName = 'MoriniereRobinDev\DevoirApp\Model\ControllerApp';
                break;

            default:
                $this->controllerClassName = 'MoriniereRobinDev\DevoirApp\Model\ControllerApp';
        }

        // si le paramètre 'action' n'existe pas alors l'action sera 'defaultAction'
        $this->controllerAction = $this->request->getGetParam('action', 'defaultAction');
        $this->controllerId = $this->request->getGetParam('id', 'defaultId');
    }
}
