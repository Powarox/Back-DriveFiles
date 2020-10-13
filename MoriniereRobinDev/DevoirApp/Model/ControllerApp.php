<?php

namespace MoriniereRobinDev\DevoirApp\Model;

//use MoriniereRobinDev\DevoirApp\Model\images;

class ControllerApp {
    protected $request;
    protected $response;
    protected $view;
    
    // post --> index.php?obj=pdf&action=show

    public function __construct($request, $response, $view, $authManager){
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->authManager = $authManager;
        
        // create menu 
        $menu = array(
			"Accueil" => 'index.php',
			"Upload"         => '?obj=pdf&amp;action=uploadPage',
			"Liste fichier"  => '?obj=pdf&amp;action=listPage',
			"Information"    => '?obj=pdf&amp;action=informationPage',
		);
        $this->view->setPart('menu', $menu);
    }
    
    public function execute($action){
        if(method_exists($this, $action)){
            return $this->$action();
        }
        else {
            throw new Exception("Action : {$action} non trouvée");
        }
    }
    
    public function defaultAction(){
        if($this->authManager->isUserConnected()){
            return $this->makeUserConnectedHomePage();
        }
        return  $this->makeHomePage();
    }

    public function show(){
        $title = "Page details fichier";
        $content = "detail du fichier : id";
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }
    
    public function uploadPage(){
        $title = "Page d'upload";
        $content = "ajouter un fichier : ";
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }
    
    public function listPage(){
        $title = "Page liste fichier";
        $content = "Voici la liste des fichier";
        $content .= "Modifier : ";
        $content .= "Supprimer : ";
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }
    
    public function informationPage(){
        $title = "Page d'information devoir";
        $content = "Detail tech";
        $content .= "Login / Password : ";
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }

    public function unknownPoem() {
        $title = "Poème inconnu ou non trouvé";
        $content = "Choisir un poème dans la liste.";
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }

    public function makeHomePage() {
        $title = "Bienvenue !";
        
        $content = '<form action="index.php" method="POST">';
            $content .= '<input type="text" name="login" placeholder="Login" value="">';
            $content .= '<input type="text" name="password" placeholder="Password" value="">';
            $content .= '<button type="submit">Se connecter</button>';
        $content .= '</form>';
        
        $content .= "Un site sur des poèmes.";
        
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }
    
    public function makeUserConnectedHomePage() {        
        $title = "Bienvenue !";
        
        $content = '<p>'.$_SESSION['user']['nom'].'</p>';
        $content .= '<p>'.$_SESSION['user']['prenom'].'</p>';
        $content .= '<p>'.$_SESSION['user']['statut'].'</p>';
        
       /* $content. = '<form action="'.$this->authManager->disconnectUser().'">';
            $content .= '<button type="submit">Deconnexion</button>';
        $content .= '</form>';*/
        
        $content .= "Un site sur des poèmes.";
        
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);  
    }
}




















