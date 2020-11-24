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

        $this->view->setPart('menu', $menu);
        $this->view->setPart('feedback', $feedback);
    }

    public function __destruct(){
        $_SESSION['currentConnexionBuilder'] = $this->currentConnexionBuilder;
    }

    public function execute($action, $id){
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

        // echo $files[0];
        //
        // $pdf = $files[0];
        // $pdf_first_page = $pdf[0];
        // $jpg = str_replace("pdf", "jpg", $pdf);
        //
        // $pdf_escaped = escapeshellarg($pdf_first_page);
        // $jpg_escaped = escapeshellarg($jpg);
        // exec("convert $pdf_escaped $jpg_escaped");
        //
        // var_dump($jpg_escaped);
        //
        // file_put_contents($jpg_escaped, "DevoirApp/Model/Upload/Images/" . $jpg_escaped);
        // file_put_contents($jpg_escaped, file_get_contents($jpg_escaped));

        //exec ("convert $pdf_first_page $jpg");

        /*$pdf_file   = '/Upload/Documents/all_document1.pdf';
        $save_to    = '/Upload/images/';     //make sure that apache has permissions to write in this folder! (common problem)

        //execute ImageMagick command 'convert' and convert PDF to JPG with applied settings
        exec('convert "'.$pdf_file.'" -colorspace RGB -resize 800 "'.$save_to.'"', $output, $return_var);


        if($return_var == 0) {              //if exec successfuly converted pdf to jpg
            echo "Conversion OK";
        }
        else {
            var_dump($return_var);
            var_dump($output);
        }*/

        /*$cmd = 'export PATH="/usr/local/bin/"; convert -scale 25%x25% file1.pdf[0] file2.png 2>&1';
        echo "<pre>".shell_exec($cmd)."</pre>";*/

        $this->view->makeHomePage($files);
    }


// ################ Upload ################ //
    public function askUpload(){
        $this->view->makeUploadPage();
    }

    public function upload(){
        // Vérifier si le formulaire a été soumis
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            $filename = $_FILES["pdf"]["name"];
            // Vérifie si le fichier a été uploadé sans erreur.
            if(isset($_FILES["pdf"]) && $_FILES["pdf"]["error"] == 0){
                move_uploaded_file($_FILES["pdf"]["tmp_name"], "DevoirApp/Model/Upload/Documents/".$filename);

                // $output = shell_exec('/usr/local/bin/exiftool -G1 '.$file.'.pdf > metadata.txt 2>&1');
                // =meta.txt   ." > metadata.txt"

                // Métadonnée
                $data = shell_exec("exiftool -json DevoirApp/Model/Upload/Documents/".$filename);
                $metaData = json_decode($data, true);

                // Enleve l'extension fichier .pdf
                $name = explode('.', $filename);

                // Créer un fichier text vide;
                $metaTxt = fopen('DevoirApp/Model/Upload/Metadata/'.$name[0].'.txt', 'w');
                // Ecris dans un fichier
                fputs($metaTxt, $data);
                // Ferme le fichier
                fclose($metaTxt);

                // var_dump($metaData);
            }
            else{
                echo "Error: " . $_FILES["pdf"]["error"];
            }
        }
        $this->view->displayUploadSucces($filename);
    }


// ################ Details File ################ //
    public function showDetailsFile($id){
        $name = explode('.', $id);

        $jsonData = file_get_contents('DevoirApp/Model/Upload/Metadata/'.$name[0].'.txt');
        $data = json_decode($jsonData);

        $this->view->makeDetailsPage($id, $data, $jsonData);
    }


// ################ List Files ################ //
    public function showListFiles(){
        $files = $this->getUploadDocuments();
        $this->view->makeListPage($files);
    }


// ################ Modification File ################ //
    public function modificationDetailsFile($id){


        $this->view->makeModificationDetailsPage();
    }


// ################ Connexion ################ //
    public function askConnexion(){
        if($this->authManager->isUserConnected()){
            $this->view->makeUserConnectedPage();
        }
        else{
            $this->currentConnexionBuilder = new AccountBuilder();
            $this->view->makeConnexionPage($this->currentConnexionBuilder);
        }
    }

    public function connexion(){
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
                    $this->view->makeConnexionPage($this->currentConnexionBuilder);
                }
                else if($check === 'password'){
                    $this->currentConnexionBuilder->setError($passwordRef, 'Password erroné');
                    $this->view->makeConnexionPage($this->currentConnexionBuilder);
                }
                else{
                    $this->currentConnexionBuilder = null;
                    $this->view->displayConnexionSucces();
                }
            }
            else{
                $this->currentConnexionBuilder->setError($passwordRef, 'Password vide');
                $this->view->makeConnexionPage($this->currentConnexionBuilder);
            }
        }
        else{
            $this->currentConnexionBuilder->setError($loginRef, 'Login vide');
            $this->view->makeConnexionPage($this->currentConnexionBuilder);
        }
    }

    public function deconnexion(){
        $this->authManager->disconnectUser();
        $this->view->displayDeconnexionSucces();
    }

    public function gestionAccess(){

    }



// ################ Utilitaire ################ //
    public function getUploadDocuments(){
        $files = scandir(__DIR__ .'/Upload/Documents');
        if(!empty($files)){
            $elemAutre = array_shift($files);
            $elemAutre = array_shift($files);
        }
        return $files;
    }



}
