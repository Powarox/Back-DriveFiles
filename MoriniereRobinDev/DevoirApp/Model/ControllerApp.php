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
        // Vérifier si le formulaire a été soumis
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            // Upload vide
            if($_FILES['pdf']['error'] != 0){
                $this->view->displayUploadFailure();
            }
            foreach($_FILES as $file){
                $filename = $file['name'];
                $_SESSION[$filename] = $file;

                // Enregistre le pdf dans Upload/Documents
                move_uploaded_file($file["tmp_name"], "DevoirApp/Model/Upload/Documents/".$filename);

                // Enleve l'extension fichier .pdf
                $name = $this->getFileWithoutExtention($filename);

                // Créer une image du pdf et save dans Upload/Images
                exec('convert  DevoirApp/Model/Upload/Documents/'.$filename.'[0]  DevoirApp/Model/Upload/FirstPages/'.$name.'.jpg');

                // Extraction Métadonnée
                $data = shell_exec("exiftool -json DevoirApp/Model/Upload/Documents/".$filename);
                $metaData = json_decode($data, true);

                // Créer un fichier contenant les métadata
                $metaTxt = fopen('DevoirApp/Model/Upload/Metadata/'.$name.'.json', 'w');
                fputs($metaTxt, $data);
                fclose($metaTxt);
            }
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

        unlink("DevoirApp/Model/Upload/Documents/".$filePdf);
        unlink("DevoirApp/Model/Upload/Metadata/".$fileJson);
        unlink("DevoirApp/Model/Upload/FirstPages/".$imgFirstPage);

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
            if($newData['documentNameChanged'] != $id && $newData['documentNameChanged'] != null){
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
        $this->view->makePaiementPage($id);
    }

    public function paiement($id){
        $data = $this->request->getAllPostParams();
        $email = $data['email'];
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
            'normal_return_url' => 'https://dev-21606393.users.info.unicaen.fr/M1/Tw4/Projet/MoriniereRobinDev/index.php?action=paiementRetourManuel',
            'cancel_return_url' => 'https://dev-21606393.users.info.unicaen.fr/M1/Tw4/Projet/MoriniereRobinDev/index.php?action=paiementRetourCancel',
            'automatic_response_url' => 'https://dev-21606393.users.info.unicaen.fr/M1/Tw4/Projet/MoriniereRobinDev/index.php?action=paiementRetourAuto',
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

        // Request Paiement
        $path_req = "/users/21606393/www-dev/paiement/Sherlocks/bin/static/request";
        $resultRequest = exec("$path_req $script");
        $resultRequestTab = explode('<BR>', $resultRequest);
        $result = $resultRequestTab[7];

        // Fichier log avec info paiement
        $logs = array(
            "prix"            =>  $prixEuro,
            "email"           =>  $email,
            "idDocument"      =>  $id,
            "idTransaction"   =>  $idTransaction,
        );

        $jsonLogs = json_encode($logs, true);
        $pathLog = 'DevoirApp/Model/Paiement/Logs/';
        file_put_contents($pathLog.'Log.json', $jsonLogs);

        // Fichier log avec information tout les paiement
        file_put_contents($pathLog.'LogsGeneral.txt', "// --------- Transaction ID: $idTransaction --------- //\n", FILE_APPEND);
        file_put_contents($pathLog.'LogsGeneral.txt', "Montant : ".$prixEuro."\n", FILE_APPEND);
        file_put_contents($pathLog.'LogsGeneral.txt', "Email : ".$email."\n", FILE_APPEND);
        file_put_contents($pathLog.'LogsGeneral.txt', "Document : ".$id."\n", FILE_APPEND);
        file_put_contents($pathLog.'LogsGeneral.txt', "\n", FILE_APPEND);

        $this->view->makePaiementFinalPage($result);
    }

    // Succes paiement
    public function paiementRetourAuto(){
        $this->paiementRetourManuel();
    }

    // Succes paiement Send Mail
    public function paiementRetourManuel(){
        $jsonLogs = file_get_contents('DevoirApp/Model/Paiement/Logs/Log.json');
        $logs = json_decode($jsonLogs, true);

        $prix = $logs['prix'];
        $idDocument = $logs['idDocument'];
        $idTransaction = $logs['idTransaction'];

        // Recipient
        $to = $logs['email'];

        // Sender
        $from = 'apiSenderMail@example.com';
        $fromName = 'CodexWorld';

        // Email subject
        $subject = 'Merci pour votre achat!';

        // Attachment file
        $file = "DevoirApp/Model/Upload/Documents/".$idDocument.".pdf";

        // Email body content
        $htmlContent = '
            <h3>Email suite à l\'achat effectué</h3>
            <p>Vous trouverez ci-joint le document : '.$idDocument.'</p>
            <p>Résultant de la transaction n°'.$idTransaction.'</p>
            <p>Pour un montant total de : '.$prix.' €</p>
            <br>
            <p>Cordialement,</p>
            <p>Moriniere Robin 21606393</p>
            <p>Mohamed Lamine Seck 21711412</p>
        ';

        // Header for sender info
        $headers = "From: $fromName"." <".$from.">";

        // Boundary
        $semi_rand = md5(time());
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

        // Headers for attachment
        $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

        // Multipart boundary
        $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" .
        "Content-Transfer-Encoding: 7bit\n\n" . $htmlContent . "\n\n";

        // Preparing attachment
        if(!empty($file) > 0){
            if(is_file($file)){
                $message .= "--{$mime_boundary}\n";
                $fp =    @fopen($file,"rb");
                $data =  @fread($fp,filesize($file));

                @fclose($fp);
                $data = chunk_split(base64_encode($data));
                $message .= "Content-Type: application/octet-stream; name=\"".basename($file)."\"\n" .
                "Content-Description: ".basename($file)."\n" .
                "Content-Disposition: attachment;\n" . " filename=\"".basename($file)."\"; size=".filesize($file).";\n" .
                "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
            }
        }
        $message .= "--{$mime_boundary}--";
        $returnpath = "-f" . $from;

        // Send email
        $mail = @mail($to, $subject, $message, $headers, $returnpath);

        $this->view->displayPaiementSucces();
    }

    // Echec paiement
    public function paiementRetourCancel(){
        $this->view->displayPaiementFailure();
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
