<?php

namespace MoriniereRobinDev\DevoirApp\Model;

use MoriniereRobinDev\WebFramework;

class ViewApp extends WebFramework\View\View {

// ################ Home Page ################ //
    public function makeHomePage() {
        $title = "Bienvenue !";
        
        $content = '<form action="index.php?obj=pdf&action=displayConnexionSucces" method="POST">';
            $content .= '<input type="text" name="login" placeholder="Login" value="">';
            $content .= '<input type="text" name="password" placeholder="Password" value="">';
            $content .= '<button type="submit">Se connecter</button>';
        $content .= '</form>';
        
        $content .= "Un site sur des poèmes.";
        
        $this->setPart('title', $title);
        $this->setPart('content', $content);
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
    
    public function show(){
        $title = "Page details fichier";
        $content = "detail du fichier : id";
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }
    
    
// ################ Detail Page ################ //
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
    
    
// ################ Unknown User Page ################ //    
    public function unknownUserPage() {
        $title = "Accès privé";
        $content = "Vous devez vous connecté pour accéder à cette page.";
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
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
    
    
// ################ Display Connexion ################ //
    
    public function displayConnexionSucces(){
        $this->control->POSTredirect("index.php", "<p class='feedback'>Vous êtes bien connecté en tant que ".$_SESSION['user']['statut']."</p>");  
    }
        
    public function displayConnexionFailure(){
        $this->control->POSTredirect("index.php", "<p class='feedback'>Erreurs dans le formulaire</p>");
    }
        
    public function displayRequireConnexion(){
        $this->control->POSTredirect("index.php", "<p class='feedback'>Connexion requise pour accèder à cette page</p>");
    }
        
    public function displayDeconnexionSucces(){
        $this->control->POSTredirect("index.php", "<p class='feedback'>Déconnexion réussi</p>");
    }
    
}