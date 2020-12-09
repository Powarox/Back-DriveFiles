<?php

namespace MoriniereRobinDev\DevoirApp\Model;

class ControllerApp {
    protected $request;
    protected $response;
    protected $view;
    protected $currentConnexionBuilder;

    public function __construct($request, $response, $view, $authManager){
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->authManager = $authManager;

        $this->currentConnexionBuilder = key_exists('currentConnexionBuilder', $_SESSION) ? $_SESSION['currentConnexionBuilder'] : null;

        $feedback = key_exists('feedback', $_SESSION) ? $_SESSION['feedback'] : '';
        $_SESSION['feedback'] = '';

        $menu = array(
			"Accueil"        => 'index.php?obj=pdf&amp;action=showFiles',
			"Upload"         => 'index.php?obj=pdf&amp;action=askUpload',
			"Liste fichier"  => 'index.php?obj=pdf&amp;action=showListFiles',
			"Information"    => 'index.php?obj=pdf&amp;action=makeInformationPage',
            "Sign In"        => 'index.php?obj=pdf&amp;action=askConnexion'
		);

        if(!empty($_SESSION['user'])){
            array_pop($menu);
            $menu["Sign Out"] = 'index.php?obj=pdf&action=deconnexion';
        }

        $this->view->setPart('menu', $menu);
        $this->view->setPart('feedback', $feedback);
    }

    public function __destruct(){
        $_SESSION['currentConnexionBuilder'] = $this->currentConnexionBuilder;
    }

    public function execute($action, $id){
        if(empty($_SESSION['user'])){
            if($action == 'askUpload' || $action == 'showListFiles'){
                $this->view->displayRequireConnexion($action);
            }
        }
        if(method_exists($this, $action)){
            if($id != 'defaultId'){
                return $this->$action($id);
            }
            else{
                return $this->$action();
            }
        }
        else if(method_exists($this->view, $action)){
            return $this->view->$action();
        }
        else{
            $this->defaultAction();
        }
    }

    public function defaultAction(){
        $this->showFiles();
    }



// ################ Accueil ################ //
    public function showFiles(){
        $files = $this->getUploadDocuments();

        foreach($files as $f){
            $f = $this->getFileWithoutExtention($f);
        }

        $this->view->makeHomePage($files);
    }


// ################ Upload ################ //
    public function askUpload(){
        $this->view->makeUploadPage();
    }

    public function upload(){
        // echo '<script>console.log("Upload php")</script>';

        // Vérifier si le formulaire a été soumis
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            $filename = $_FILES["pdf"]["name"];
            // Vérifie si le fichier a été uploadé sans erreur.
            if(isset($_FILES["pdf"]) && $_FILES["pdf"]["error"] == 0){
                // Enregistre le pdf dans Upload/Documents
                move_uploaded_file($_FILES["pdf"]["tmp_name"], "DevoirApp/Model/Upload/Documents/".$filename);

                // Enleve l'extension fichier .pdf
                $name = $this->getFileWithoutExtention($filename);

                // Créer une image du pdf et save dans Upload/Images
                exec('convert  DevoirApp/Model/Upload/Documents/'.$filename.'[0]  DevoirApp/Model/Upload/FirstPages/'.$name.'.jpg');

                // $output = shell_exec('/usr/local/bin/exiftool -G1 '.$file.'.pdf > metadata.txt 2>&1');
                // =meta.txt   ." > metadata.txt"

                // Extraction Métadonnée
                $data = shell_exec("exiftool -json DevoirApp/Model/Upload/Documents/".$filename);
                $metaData = json_decode($data, true);

                // Créer un fichier contenant les métadata
                $metaTxt = fopen('DevoirApp/Model/Upload/Metadata/'.$name.'.json', 'w');
                fputs($metaTxt, $data);
                fclose($metaTxt);

                // var_dump($metaData);
            }
            else{
                $this->view->displayUploadFailure($_FILES["pdf"]["error"]);
            }
        }
        $this->view->displayUploadSucces($name);
    }


// ################ Details File ################ //
    public function showDetailsFile($filename){
        $jsonData = file_get_contents('DevoirApp/Model/Upload/Metadata/'.$filename.'.json');
        $data = json_decode($jsonData, true);

        // {'Author', 'Title', 'Language', 'Format', 'Date', 'Creator', 'Producer',  'Contributor' : [], 'Description'}
        //
        // {'FileName', 'FileSize', 'FileModifyDate', 'FileAccessDate', 'FileInodeChangeDate', 'FilePermissions', 'FileType', 'FileTypeExtension'}

        // Get File Suivant
        $fileSuiv = "";
        $filePrec = "";
        $files = $this->getUploadDocuments();
        foreach($files as $key => $value){
            if($value == $filename){
                if(key_exists($key - 1, $files)){
                    $filePrec = $files[$key - 1];
                }
                if(key_exists($key + 1, $files)){
                    $fileSuiv = $files[$key + 1];
                }
            }
        }

        $this->view->makeDetailsPage($filename, $data[0], $filePrec, $fileSuiv);
    }


// ################ List Files ################ //
    public function showListFiles(){
        $files = $this->getUploadDocuments();
        $this->view->makeListPage($files);
    }


// ################ Suppression File ################ //
    public function askSuppressionFile($id){
        $this->view->makeSuppresionPage($id);
    }

    public function suppresionFile($filename){
        $filePdf = $this->setFileExtention($filename, ".pdf");
        $fileJson = $this->setFileExtention($filename, ".json");
        $imgFirstPage = $this->setFileExtention($filename, ".jpg");

        unlink ("DevoirApp/Model/Upload/Documents/".$filePdf);
        unlink ("DevoirApp/Model/Upload/Metadata/".$fileJson);
        unlink ("DevoirApp/Model/Upload/FirstPages/".$imgFirstPage);

        // $img = $this->setFileExtention($filename, ".png");
        // unlink ("DevoirApp/Model/Upload/Images/".$img);

        $this->view->displaySuppresionFile($filename);
    }


// ################ Modification File ################ //
    public function modificationDetailsFile($filename){
        $jsonData = file_get_contents('DevoirApp/Model/Upload/Metadata/'.$filename.'.json');
        $data = json_decode($jsonData, true);

        $this->view->makeModificationDetailsPage($filename, $data[0]);
    }

    public function modification($id){
        // var_dump($this->response);
        // if(true){
        $this->view->displayModificationSucces($id);
        // }
        // else{
        //     $this->view->displayModificationFailure($id);
        // }
    }


// ################ Connexion ################ //
    public function askConnexion($currentPage = null){
        if($this->authManager->isUserConnected()){
            $this->view->makeUserConnectedPage();
        }
        else{
            $this->currentConnexionBuilder = new AccountBuilder();
            $this->view->makeConnexionPage($this->currentConnexionBuilder, $currentPage);
        }
    }

    public function connexion($currentPage = null){
        $data = $this->request->getAllPostParams();
        $this->currentConnexionBuilder = new AccountBuilder($data);

        $loginRef = $this->currentConnexionBuilder->getLoginRef();
        $passwordRef = $this->currentConnexionBuilder->getPasswordRef();

        if($data[$loginRef] ==! null){
            $login = $data[$loginRef];
            if($data[$passwordRef] ==! null){
                $password = $data[$passwordRef];
                $check = $this->authManager->checkAuth($login, $password);
                if($check === 'login'){
                    $this->currentConnexionBuilder->setError($loginRef, 'Login erroné');
                    $this->view->makeConnexionPage($this->currentConnexionBuilder, $currentPage);
                }
                else if($check === 'password'){
                    $this->currentConnexionBuilder->setError($passwordRef, 'Password erroné');
                    $this->view->makeConnexionPage($this->currentConnexionBuilder, $currentPage);
                }
                else{
                    $this->currentConnexionBuilder = null;
                    if(!empty($currentPage)){
                        $this->view->displayConnexionSuccesToCurrentPage($currentPage);
                    }
                    $this->view->displayConnexionSucces();
                }
            }
            else{
                $this->currentConnexionBuilder->setError($passwordRef, 'Password vide');
                $this->view->makeConnexionPage($this->currentConnexionBuilder, $currentPage);
            }
        }
        else{
            $this->currentConnexionBuilder->setError($loginRef, 'Login vide');
            $this->view->makeConnexionPage($this->currentConnexionBuilder, $currentPage);
        }
    }

    public function deconnexion(){
        $this->authManager->disconnectUser();
        $this->view->displayDeconnexionSucces();
    }


// ################ Utilitaire ################ //
    public function getUploadDocuments(){
        $files = scandir(__DIR__ .'/Upload/Documents');
        if(!empty($files)){
            $elemAutre = array_shift($files);
            $elemAutre = array_shift($files);
        }
        for($i = 0; $i < count($files); $i++){
            $files[$i] = $this->getFileWithoutExtention($files[$i]);
        }
        return $files;
    }

    public function getFileWithoutExtention($id){
        $filename = explode('.', $id);
        return $filename[0];
    }

    public function setFileExtention($name, $extention){
        $filename = $name . $extention;
        return $filename;
    }



}
