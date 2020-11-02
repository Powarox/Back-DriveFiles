<?php

namespace MoriniereRobinDev\DevoirApp\Model;

use MoriniereRobinDev\WebFramework;

class ViewApp extends WebFramework\View\View {

// ################ Home Page ################ //
    
    public function makeHomePage() {
        $title = "Bienvenue !";
        $content = "Un site sur des poèmes.";
        
        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }
    
    
    
// ################ Upload ################ // 
    
    public function makeUploadPage(){
        $title = "Page d'upload";
        $content = '<form action="index.php?obj=pdf&action=upload" method="POST" enctype="multipart/form-data">';
        $content .= '<input type="file" name="photo" id="fileUpload">';
        $content .= '<input type="text" name="titre" placeholder="titre">';
        $content .= '<button type="submit">Envoyer</button>';
        $content .= '</form>';
        
        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }
    
    
    
// ################ List Page ################ //
    
    public function makeListPage(){
        $title = "Page liste fichier";
        $content = "Voici la liste des fichier";
        $content .= "Modifier : ";
        $content .= "Supprimer : ";
        
        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }
    
    
    
// ################ Details Page ################ //
    
    public function makeDetailsPage(){
        $title = "Page details fichier";
        $content = "detail du fichier : id";
        
        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }

    
    
// ################ Modification Page ################ //
    
    public function makeModificationDetailsPage(){
        $title = "Page details fichier";
        $content = "detail du fichier : id";
        
        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }
    
    
    
// ################ Information Page ################ //
    
    public function makeInformationPage(){
        $title = "Page d'information devoir";
        $content = "Detail tech";
        $content .= "Login / Password : ";
        
        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }
    
    
    
// ################ Unknown Page ################ //   
    
    public function unknownPdfPage() {
        $title = "Poème inconnu ou non trouvé";
        $content = "Choisir un poème dans la liste.";
        
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }
    
    
    
// ################ Connexion Page ################ // 

    public function makeConnexionPage($builder){
        $title = "Page de connexion";
        
        $data = $builder->getData();
            
        $loginRef = $builder->getLoginRef();
        $passwordRef = $builder->getPasswordRef();
            
        $errLogin = $builder->getErrors($loginRef);
        $errPassword = $builder->getErrors($passwordRef);
        
        $content = '<form class="box" action="index.php?obj=pdf&action=connexion" method="POST">';
        $content .= '<input type="text" name="'.$loginRef.'" placeholder="Login" value="'.self::htmlesc($data[$loginRef]).'">';
        if($errLogin !== null){
            $content .= '<span class="errors">'.$errLogin.'</span>';
        }
        $content .= '<input type="text" name="'.$passwordRef.'" placeholder="Password" value="'.self::htmlesc($data[$passwordRef]).'">';
        if($errPassword !== null){
            $content .= '<span class="errors">'.$errPassword.'</span>';
        }
        $content .= '<button type="submit">Se connecter</button>';
        $content .= '</form>';
        
        $content .= "Un site sur des poèmes.";
        
        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }
    
    public function makeUserConnectedPage(){
        $title = "Bienvenue " . self::htmlesc($_SESSION['user']['nom']);
        
        $content = '<p>'.self::htmlesc($_SESSION['user']['nom']).'</p>';
        $content .= '<p>'.self::htmlesc($_SESSION['user']['prenom']).'</p>';
        $content .= '<p>'.self::htmlesc($_SESSION['user']['statut']).'</p>';
        
        $content .= '<form action="index.php?obj=pdf&action=deconnexion" method="POST">';
        $content .= '<button type="submit">Deconnexion</button>';
        $content .= '</form>';
        
        $this->setPart('title', $title);
        $this->setPart('content', $content); 
    }
    
    
    
// ################ Display Connexion ################ //
    
    public function displayConnexionSucces(){
        $this->router->POSTredirect("index.php?obj=pdf&action=makeUserConnectedPage", "<p class='feedback'>Vous êtes bien connecté en tant que ".$_SESSION['user']['statut']."</p>");  
    }
        
    public function displayRequireConnexion(){
        $this->router->POSTredirect("index.php?obj=pdf&action=askConnexion", "<p class='feedback'>Connexion requise pour accèder à cette page</p>");
    }
        
    public function displayDeconnexionSucces(){
        $this->router->POSTredirect("index.php?obj=pdf&action=askConnexion", "<p class='feedback'>Déconnexion réussi</p>");
    }
    
    
    
// ################ Display Upload ################ //
    
    public function displayUploadSucces(){
        $this->router->POSTredirect("index.php?obj=pdf&action=showDetailsFile", "<p class='feedback'>Votre fichier à bien été enregistré</p>");
    }
    
    public function displayUploadFailure(){
        $this->router->POSTredirect("index.php?obj=pdf&action=askConnexion", "<p class='feedback'>Erreur lors de l'upload</p>");
    }
    
    
    
// ################ Utilitaire ################ //
        
    public static function htmlesc($str){
        return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }    
}