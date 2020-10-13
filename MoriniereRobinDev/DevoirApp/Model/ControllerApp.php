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
        $content = '<form action="index.php?obj=pdf&action=upload" method="POST" enctype="multipart/form-data">';
        $content .= '<input type="file" name="photo" id="fileUpload">';
        $content .= '<input type="text" name="titre" placeholder="titre">';
        $content .= '<button type="submit">Envoyer</button>';
        $content .= '</form>';
        
        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }
    
    public function upload(){
        // Vérifier si le formulaire a été soumis
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            $filename = $_FILES["photo"]["name"];

            // Vérifie si le fichier a été uploadé sans erreur.
            if(isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0){
                move_uploaded_file($_FILES["photo"]["tmp_name"], "DevoirApp/Model/upload/" . $_FILES["photo"]["name"]);
                echo "Votre fichier a été téléchargé avec succès.";

                // Code Json pour le fichier meta.txt
                $data2 = array("SourceFile" => "upload/".$filename,
                    "XMP-dc:Title" => $_POST['titre']);
                $data2Json = json_encode($data2, JSON_UNESCAPED_SLASHES);

                //echo $data2Json;
                //var_dump($data2);

                // Créer un fichier text vide;
                $metaTxt = fopen('DevoirApp/Model/upload/meta.txt', 'w');
                // Ecris dans un fichier
                fputs($metaTxt, $data2Json);        
                // Ferme le fichier
                fclose($metaTxt);

                // Métadonnée
                $data = shell_exec("exiftool -json upload/".$filename); // =meta.txt
                $metaData = json_decode($data, true); 

                //var_dump($metaData);
            } 
            else{
                echo "Error: " . $_FILES["photo"]["error"];
            }
        }
        
        $title = "Upload Success";
        $content = "Votre fichier à bien été enregistré";

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




















