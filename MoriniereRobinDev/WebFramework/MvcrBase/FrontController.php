<?php

namespace MoriniereRobinDev\WebFramework\MvcrBase;

use MoriniereRobinDev\PoemApp\Model;
use MoriniereRobinDev\PoemApp\Router;
use MoriniereRobinDev\WebFramework\Service\Authentification;

class FrontController {
    protected $request;
    protected $response;
    protected $template;
    
    public function __construct($request, $response, $template){
        $this->request = $request;
        $this->response = $response;
        $this->template = $template;
    }
    
    public function execute(){
        $view = new \MoriniereRobinDev\WebFramework\View\View($this->template);
    
        $authManager = new \MoriniereRobinDev\WebFramework\Service\Authentification\AuthManager();
        
        /*try{
            ...
        }
        catch(Exception e){
            echo 'exception'
        }*/
        

        if($authManager->isUserConnected()){
            echo 'user connected';
            //AuhtentificationHtml->affiche(information);
        }
        else {
            $postData = $this->request->getAllPostParams();
        
            if(key_exists('login', $postData)){
                $login = $postData['login'];
                if(key_exists('password', $postData)){
                    $password = $postData['password'];
                    $check = $authManager->checkAuth($login, $password);
                    if($check === 'login'){
                        echo 'Login erroné';
                        // Accueil + Feedback
                    }
                    else if($check === 'password'){
                        echo  'Password erroné';
                        // Accueil + Feedback
                    }
                    else{
                        echo 'Connexion succes';
                        // AuhtentificationHtml->affiche(connexion);
                    }
                }
            }
        }    

        //$authManager->disconnectUser();
        
        
        $router = new \MoriniereRobinDev\DevoirApp\Router\RouterApp($this->request);
        $className = $router->getControllerClassName();
        $action = $router->getControllerAction();

        $controller = new $className($this->request, $this->response, $view, $authManager);
        $controller->execute($action);
        
        if($this->request->isAjaxRequest()){
        	$content = $view->getPart('content');
        }
        else{
        	$content = $view->render();
        }
        
        $this->response->send($content);
    }

}