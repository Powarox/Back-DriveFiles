<?php

namespace MoriniereRobinDev\DevoirApp\Model;

class ControllerApp {
    protected $request;
    protected $response;
    protected $view;

    public function __construct($request, $response, $view, $authManager, $control){
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->authManager = $authManager;
        $this->control = $control;
        
        $menu = array(
			"Accueil" => 'index.php',
			"Upload"         => '?obj=pdf&amp;action=makeUploadPage',
			"Liste fichier"  => '?obj=pdf&amp;action=makeListPage',
			"Information"    => '?obj=pdf&amp;action=makeInformationPage',
		);
        $this->view->setPart('menu', $menu);
    }
    
    public function execute($action){
        if(method_exists($this, $action)){             
            return $this->$action();
        }
        else if(method_exists($this->view, $action)){
            return $this->view->$action();
        }
        else {
            throw new Exception("Action : {$action} non trouvée");
        }
    }
    
    public function defaultAction(){
        if($this->authManager->isUserConnected()){
            return $this->view->makeUserConnectedHomePage();
        }
        return  $this->view->makeHomePage();
    }

    
// ################ Upload ################ //    
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


}




