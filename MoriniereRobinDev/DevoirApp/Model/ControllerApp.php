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


// Send Mail
    // $to      = 'nobody@example.com';
    // $subject = 'the subject';
    // $message = 'hello';
    // $headers = array(
    //     'From' => 'webmaster@example.com',
    //     'Reply-To' => 'webmaster@example.com',
    //     'X-Mailer' => 'PHP/' . phpversion()
    // );
    //
    // mail($to, $subject, $message, $headers);



// ################ Accueil ################ //
    public function showFiles(){
        // var_dump($_SESSION);
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
        // Vérifier si le formulaire a été soumis
        if($_SERVER["REQUEST_METHOD"] == "POST"){

            foreach($_FILES as $file){
                $filename = $file['name'];
                $_SESSION[$filename] = $file;

                // Enregistre le pdf dans Upload/Documents
                move_uploaded_file($file["tmp_name"], "DevoirApp/Model/Upload/Documents/".$filename);

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
            // // Vérifie si le fichier a été uploadé sans erreur.
            // if(isset($_FILES["pdf"]) && $_FILES["pdf"]["error"] == 0){
            //
            // }
            // else{
            //     $this->view->displayUploadFailure($_FILES["pdf"]["error"]);
            // }
        }
        $this->view->displayUploadSucces($name);
    }

    public function ajaxUploadSucces($filename){
        $this->view->displayUploadSucces($filename);
    }

    public function ajaxUploadMultipleSucces(){
        $this->view->displayUploadMultipleSucces();
    }


// ################ Details File ################ //
    public function showDetailsFile($filename){
        $jsonData = file_get_contents('DevoirApp/Model/Upload/Metadata/'.$filename.'.json');
        $data = json_decode($jsonData, true);

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

        // Metadata IPTC
        $metaIPTC = array('Author', 'Title', 'Language', 'Format', 'Date', 'Creator', 'Producer',  'Contributor', 'Description');

        // Metadata de type File
        $metaFile = array('FileName', 'FileSize', 'FileModifyDate', 'FileAccessDate', 'FileInodeChangeDate', 'FilePermissions', 'FileType', 'FileTypeExtension');

        $this->view->makeDetailsPage($filename, $data[0], $metaIPTC, $metaFile, $filePrec, $fileSuiv);
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

        // Metadata IPTC
        $metaIPTC = array('Author', 'Title', 'Language', 'Format', 'Date', 'Creator', 'Producer',  'Contributor', 'Description');

        // Metadata de type File
        $metaFile = array('FileName', 'FileSize', 'FileModifyDate', 'FileAccessDate', 'FileInodeChangeDate', 'FilePermissions', 'FileType', 'FileTypeExtension');

        $this->view->makeModificationDetailsPage($filename, $data[0], $metaIPTC, $metaFile);
    }

    public function modification($id){
        $jsonData = file_get_contents('DevoirApp/Model/Upload/Metadata/'.$id.'.json');
        $data = json_decode($jsonData, true);
        $newData = $this->request->getAllPostParams();

        // var_dump($data);
        // var_dump($newData);

        foreach($data[0] as $key => $value){
            foreach($newData as $k => $v){
                if($key == $k){
                    if(is_array($key)){
                        $data[0][$key] = $newData[$k];
                    }
                    else{
                        $data[0][$key] = $v;
                    }
                }
            }
        }

        $jsonData = json_encode($data);
        $metaTxt = fopen('DevoirApp/Model/Upload/Metadata/'.$id.'.json', 'w');
        fputs($metaTxt, $jsonData);
        fclose($metaTxt);

        if(key_exists('documentNameChanged', $newData)){
            if($newData['documentNameChanged'] != $id){
                $idPdf = $this->setFileExtention($id, '.pdf');
                $idJson = $this->setFileExtention($id, '.json');
                $idFirstPage = $this->setFileExtention($id, '.jpg');

                $filePdf = $this->setFileExtention($newData['documentNameChanged'], '.pdf');
                $fileJson = $this->setFileExtention($newData['documentNameChanged'], '.json');
                $fileFirstPage = $this->setFileExtention($newData['documentNameChanged'], '.jpg');

                rename("DevoirApp/Model/Upload/Documents/".$idPdf, "DevoirApp/Model/Upload/Documents/".$filePdf);
                rename("DevoirApp/Model/Upload/Metadata/".$idJson, "DevoirApp/Model/Upload/Metadata/".$fileJson);
                rename("DevoirApp/Model/Upload/FirstPages/".$idFirstPage, "DevoirApp/Model/Upload/FirstPages/".$fileFirstPage);

                $id = $newData['documentNameChanged'];
            }
        }

        $this->view->displayModificationSucces($id);
    }



// ################ Paiement ################ //
    public function askPaiement($id){
        $idTransaction = mt_rand(1, 999);
        $prixEuro = number_format(999/100, 2, ',', ' ');
        $pathfile = '/users/21606393/www-dev/M1/Tw4/Projet/MoriniereRobinDev/DevoirApp/Model/Paiement/Sherlocks/param_demo/pathfile';

        $data = array(
            'amount' => '999',
            'merchant_id' => '014295303911111',
            'merchant_country' => 'fr',
            'currency_code' => '978',
            'pathfile' => $pathfile,
            'transaction_id' => $idTransaction,
            'normal_return_url' => 'https://dev-21606393.users.info.unicaen.fr/M1/Tw4/Projet/MoriniereRobinDev/index.php?obj=pdf&action=paiementRetourAuto',
            'cancel_return_url' => 'https://dev-21606393.users.info.unicaen.fr/M1/Tw4/Projet/MoriniereRobinDev/index.php?obj=pdf&action=paiementRetourCancel',
            'automatic_response_url' => 'https://dev-21606393.users.info.unicaen.fr/M1/Tw4/Projet/MoriniereRobinDev/index.php?obj=pdf&action=paiementRetourManuel',
            'language' => 'fr',
            'payment_means' => 'CB,2,VISA,2,MASTERCARD,2',
            'header_flag' => 'no',
            'capture_day' => '',
            'capture_mode' => '',
            'background_id' => '',
            'bgcolor' => 'EEEEEE',
            'block_align' => '',
            'block_order' => '',
            'textcolor' => '',
            'textfont' => '',
            'templatefile' => '',
            'logo_id' => '',
            'receipt_complement' => '',
            'caddie' => '',
            'customer_id' => '',
            'customer_email' => '',
            'customer_ip_address' => '',
            'data' => '',
            'return_context' => '',
            'target' => '',
            'order_id' => '766'
        );

        $script = "";
        foreach($data as $key => $value){
            if($value){
                $script .= " " . $key . "=" . $value;
            }
        }

        // Request
        $path_req = "/users/21606393/www-dev/paiement/Sherlocks/bin/static/request";
        $resultRequest = exec("$path_req $script");
        $resultRequestTab = explode('<BR>', $resultRequest);
        $result = $resultRequestTab[7];

        // $logs = array(
        //     "idDocument"      =>  $id,
        //     "idTransaction"   =>  $idTransaction,
        //     "email"   =>  $email,
        // );
        // $jsonLogs = json_encode($logs, true);
        // file_put_contents('Log.txt', $jsonLogs);

        // // Ecriture reponse dans Log.txt
        // file_put_contents('LogsGeneral.txt', "// --------- Transaction ID: $idTransaction --------- //\n", FILE_APPEND);
        // file_put_contents('LogsGeneral.txt', "Montant : ".$prixEuro."\n", FILE_APPEND);
        // file_put_contents('LogsGeneral.txt', "Email : ".$email."\n", FILE_APPEND);
        // file_put_contents('LogsGeneral.txt', "Document : ".$id."\n", FILE_APPEND);
        // file_put_contents('LogsGeneral.txt', "\n", FILE_APPEND);

        $this->view->makePaiementPage($id, $result);
    }

    public function paiementRetourAuto(){

    }

    public function paiementRetourCancel(){

    }

    public function paiementRetourManuel(){

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
