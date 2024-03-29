<?php

namespace MoriniereRobinDev\DevoirApp\Model;

use MoriniereRobinDev\WebFramework;

class ViewApp extends WebFramework\View\View
{

// ################ Home Page ################ //
    public function makeHomePage($files)
    {
        if (key_exists('user', $_SESSION)) {
            $title = "Bienvenue <br> " . self::htmlesc($_SESSION['user']['prenom']);
        } else {
            $title = "Bienvenue !";
        }

        $content = '<section class="homeSection">';

        foreach ($files as $key => $value) {
            $content .= '<a href="index.php?obj=pdf&action=showDetailsFile&id='.self::htmlesc($value).'">';
            $content .= '<h3>'.self::htmlesc($value).'</h3>';
            if (file_exists('DevoirApp/Model/Upload/FirstPages/'.self::htmlesc($value).'.jpg')) {
                $content .= '<img src="DevoirApp/Model/Upload/FirstPages/'.self::htmlesc($value).'.jpg" alt="Image doc pdf : '.self::htmlesc($value).'">';
            } else {
                $content .= '<img src="DevoirApp/Model/Upload/Images/default_pdf_image.jpg" alt="Image">';
            }
            $content .= '</a>';
        }

        $content .= '</section>';

        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }


    // ################ Upload ################ //
    public function makeUploadPage()
    {
        $title = "Page d'upload";

        $content = '<section class="uploadPageSection">';

        $content .= '<form id="dropFileForm"
            action="index.php?obj=pdf&action=upload" method="POST"
            enctype="multipart/form-data" onsubmit="uploadFiles(event)">';

        $content .= '<div id="dropFileDiv"
            ondragover="overrideDefault(event);fileHover();" ondragenter="overrideDefault(event);fileHover();" ondragleave="overrideDefault(event);fileHoverEnd();" ondrop="overrideDefault(event);fileHoverEnd();addFiles(event);">';

        $content .= '<label for="fileInput" id="fileLabel">';
        $content .= '<i class="fas fa-upload"></i> ';
        $content .= '<span id="fileLabelText">Choose a file </span> ';
        $content .= '<span id="uploadStatus"></span> ';
        $content .= '<i class="fas fa-upload"></i>';
        $content .= '</label>';

        $content .= '<input id="fileInput" type="file" name="pdf" multiple onchange="addFiles(event)">';

        $content .= '</div>';
        $content .= '<progress id="progressBar"></progress>';

        $content .= '<button type="submit" id="uploadButton">Envoyer</button>';

        $content .= '</form>';
        $content .= '</section>';

        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }



    // ################ Display Upload ################ //
    public function displayUploadSucces($filename)
    {
        $this->router->POSTredirect('index.php?obj=pdf&action=showDetailsFile&id='.self::htmlesc($filename), '<p class="feedback">Votre fichier '.self::htmlesc($filename).' à bien été enregistré</p>');
    }

    public function displayUploadMultipleSucces()
    {
        $this->router->POSTredirect('index.php', '<p class="feedback">Tous les fichiers ont bien été enregistré !</p>');
    }

    public function displayUploadFailure()
    {
        $this->router->POSTredirect("index.php?obj=pdf&action=makeUploadPage", "<p class='feedback'>Veuillez déposer un document</p>");
    }


    // ################ Details Page ################ //
    public function makeDetailsPage($id, $data, $metaIPTC, $metaFile, $filePrec = null, $fileSuiv = null)
    {
        $title = "Details fichier : <br> ".self::htmlesc($id);

        // Bouton suivant - précédent
        $content = '<div id="detailsButton">';
        if ($filePrec != null) {
            $content .= '<a id="navigationButton" href="index.php?obj=pdf&action=showDetailsFile&id='.self::htmlesc($filePrec).'">Fichier précédent</a>';
        }
        if ($fileSuiv != null) {
            $content .= '<a id="navigationButton" href="index.php?obj=pdf&action=showDetailsFile&id='.self::htmlesc($fileSuiv).'">Fichier suivant</a>';
        }
        $content .= '</div>';

        $content .= '<section class="detailsPageSection">';

        $content .= '<div>';
        $content .= '<h3>Metadata de type IPTC</h3>';
        $content .= '<ul>';
        foreach ($data as $key => $value) {
            if (in_array($key, $metaIPTC) && $value != null) {
                if (!is_array($data[$key])) {
                    $content .= '<li><strong>'.self::htmlesc($key).'</strong> : '.self::htmlesc($value).'</li>';
                } else {
                    $content .= '<li><strong>'.self::htmlesc($key).'</strong> : /';
                    foreach ($data[$key] as $k => $v) {
                        $content .= '/ '.self::htmlesc($v).' /';
                    }
                    $content .= '/</li>';
                }
            }
        }
        $content .= '</ul>';
        $content .= '</div>';

        // Image 1er page pdf
        $content .= '<div id="imageDetails">';
        if (file_exists('DevoirApp/Model/Upload/FirstPages/'.self::htmlesc($id).'.jpg')) {
            $content .= '<img src="DevoirApp/Model/Upload/FirstPages/'.self::htmlesc($id).'.jpg" alt="Image doc pdf : '.self::htmlesc($id).'">';
        } else {
            $content .= '<img src="DevoirApp/Model/Upload/Images/default_pdf_image.jpg" alt="Image">';
        }
        $content .= '</div>';

        $content .= '<div>';
        $content .= '<h3>Metadata de type File</h3>';
        $content .= '<ul>';
        foreach ($data as $key => $value) {
            if (in_array($key, $metaFile) && $value != null) {
                $content .= '<li><strong>'.self::htmlesc($key).'</strong> : '.self::htmlesc($value).'</li>';
            }
        }
        $content .= '</ul>';
        $content .= '</div>';

        // Bouton Paiment
        $content .= '<a id="paiement" href="index.php?obj=pdf&action=askPaiement&id='.self::htmlesc($id).'">Acheter ce Document</a>';

        $content .= '</section>';

        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }



    // ################ List Page ################ //
    public function makeListPage($files)
    {
        $title = "Page liste fichier";

        $content = '<section class="listPageSection">';

        foreach ($files as $key => $value) {
            $content .= '<div class="elem">';
            $content .= '<a href="index.php?obj=pdf&action=showDetailsFile&id='.self::htmlesc($value).'">'.self::htmlesc($value).'</a>';

            $content .= '<a id="option" href="index.php?obj=pdf&action=modificationDetailsFile&id='.self::htmlesc($value).'">Modification</a>';

            $content .= '<a id="supprimer" href="index.php?obj=pdf&action=askSuppressionFile&id='.self::htmlesc($value).'">Supprimer</a>';

            $content .= '</div>';
        }

        $content .= '</section>';

        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }


    // ################ Suppression Page ################ //
    public function makeSuppresionPage($id)
    {
        $title = "Suppression fichier : <br>".self::htmlesc($id);

        $content = '<section class="suppressionPageSection">';
        $content .= '<h3>Voulez vous vraiment supprimer ce fichier ?</h3>';
        $content .= '<div>';

        $content .= '<a id="option" href="index.php?obj=pdf&action=showListFiles">Retour</a>';
        $content .= '<a id="supprimer" href="index.php?obj=pdf&action=suppresionFile&id='.self::htmlesc($id).'">Supprimer</a>';

        $content .= '</div>';
        $content .= '</section>';

        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }

    public function displaySuppresionFile($id)
    {
        $this->router->POSTredirect("index.php?obj=pdf&action=showListFiles", '<p class="feedback">Suppression du fichier '.self::htmlesc($id).' réussi.</p>');
    }



    // ################ Modification Page ################ //
    public function makeModificationDetailsPage($id, $data, $metaIPTC, $metaFile)
    {
        $title = "Modification fichier : <br>".self::htmlesc($id);

        $content = '<section class="modificationPageSection">';
        $content .= '<form action="index.php?obj=pdf&action=modification&id='.self::htmlesc($id).'" method="POST">';

        $content .= '<div>';
        $content .= '<ul>';

        $content .= '<h3>Changer le nom du document</h3>';
        $content .= '<li>';
        $content .= '<label>Name : </label>';
        $content .= '<input type="text" name="documentNameChanged" placeholder="" value="'.self::htmlesc($id).'">';
        $content .= '</li><br>';

        $content .= '<h3>Metadata de type IPTC</h3>';
        foreach ($data as $key => $value) {
            if (in_array($key, $metaIPTC) && $value != null) {
                if (!is_array($data[$key])) {
                    $content .= '<li>';
                    $content .= '<label>'.self::htmlesc($key).' : </label>';
                    $content .= '<input type="text" name="'.self::htmlesc($key).'" placeholder="" value="'.self::htmlesc($value).'">';
                    $content .= '</li>';
                } else {
                    $content .= '<li>';
                    $content .= '<label>'.self::htmlesc($key).' : </label>';
                    $content .= '<ul>';
                    foreach ($data[$key] as $k => $v) {
                        $content .= '<input type="text" name="'.self::htmlesc($key).'['.self::htmlesc($k).']" placeholder="" value="'.self::htmlesc($v).'">';
                    }
                    $content .= '</ul>';
                    $content .= '</li>';
                }
            }
        }
        $content .= '</ul>';

        $content .= '<ul>';
        $content .= '<h3>Metadata de type File</h3>';
        foreach ($data as $key => $value) {
            if (in_array($key, $metaFile) && $value != null) {
                $content .= '<li>';
                $content .= '<label>'.self::htmlesc($key).' : </label>';
                $content .= '<input type="text" name="'.self::htmlesc($key).'" placeholder="" value="'.self::htmlesc($value).'">';
                $content .= '</li>';
            }
        }
        $content .= '</ul>';
        $content .= '</div>';

        $content .= '<button id="modifier" type="submit">Modifier</button>';

        $content .= '</form>';
        $content .= '</section>';

        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }

    public function displayModificationSucces($id)
    {
        $this->router->POSTredirect("index.php?obj=pdf&action=showDetailsFile&id=".self::htmlesc($id), "<p class='feedback'>Votre modification est enregistré.</p>");
    }

    public function displayModificationFailure($id)
    {
        $this->router->POSTredirect("index.php?obj=pdf&action=modificationDetailsFile&id=".self::htmlesc($id), "<p class='feedback'>Echec de la modification.</p>");
    }



    // ################ Paiment Page ################ //
    public function makePaiementPage($id)
    {
        $title = 'Information Paiement';

        $content = '<section class="paiementPageSection">';
        $content .= '<h3>Le montant à payer est de : '.number_format(999/100, 2, ',', ' ').' €</h3>';

        $content .= '<form action="index.php?obj=pdf&action=paiement&id='.self::htmlesc($id).'" method="POST">';

        $content .= '<label>Email : </label>';
        $content .= '<input type="text" name="email" placeholder="" value="">';

        $content .= '<a id="navigationButton" href="index.php?obj=pdf&action=showDetailsFile&id='.self::htmlesc($id).'">Retour</a>';

        $content .= '<button id="navigationButton" type="submit">Paiement</button>';

        $content .= '</form>';
        $content .= '</section>';

        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }

    public function makePaiementFinalPage($result)
    {
        $title = 'Paiement Page';

        $content = '<h3>Veuillez selectionner un moyen de paiement ci-dessous</h3>';

        $content .= '<section class="paiementLCLPageSection">';
        $content .= $result;
        $content .= '</section>';

        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }

    public function displayPaiementSucces()
    {
        $this->router->POSTredirect("index.php", "<p class='feedback'>Votre paiement à été effectué, vous devriez recevoir un mail contenant votre achat</p>");
    }

    public function displayPaiementFailure()
    {
        $this->router->POSTredirect("index.php", "<p class='feedback'>Votre tentative de paiement à échoué</p>");
    }


    // ################ Information Page ################ //
    public function makeInformationPage()
    {
        $title = "Page d'information devoir";

        $content = '<h3>Binôme</h3>';
        $content .= '<ul>';
        $content .= '<li>Moriniere Robin 21606393</li>';
        $content .= '<li>Mohamed Lamine Seck 21711412</li>';
        $content .= '</ul><br>';

        $content .= '<h3>Detail tech</h3>';
        $content .= '<ul>';
        $content .= '<li>Pour ce qui ai des détails techniques, nous avons repris le modèle MVCR couplé avec un autoload. Notre projet est décomposé en 3 dossiers : </li>';
        $content .= '<ul>';
        $content .= '<li>WebFramework : Contient le MVCR de base avec les requêtes HTTP.</li>';
        $content .= '<li>Tools : Contient l\'autoload.</li>';
        $content .= '<li>DevoirApp : Contient le reste de l\'application dont le modèle.</li>';
        $content .= '</ul>';
        $content .= '<li>Pour les métadonnées nous avons choisi de les sauvegarder dans un fichier Json, pour rendre leur manipulation plus facile et rapide.</li>';
        $content .= '<li>Nous avons aussi implémenté une fonction de paiement qui envoie un mail avec le PDF si le paiement réussi, et pour ce qui est de l\'upload il est possible d\'uploader des documents si le Javascript est désactivé mais s\'il ait activé il est aussi possible de faire un drag and drop avec les fichiers que l\'on souhaite déposer.</li>';
        $content .= '</ul><br>';

        $content .= '<h3>Information Connexion</h3>';
        $content .= '<ul>';
        $content .= '<li>Login : alex</li>';
        $content .= '<li>Login : jml</li>';
        $content .= '<br>';
        $content .= '<li>Password : toto</li>';
        $content .= '<li>Password : toto</li>';
        $content .= '</ul><br>';

        $content .= '<h3>Information Paiement</h3>';
        $content .= '<ul>';
        $content .= '<h4>Paiement Accepté</h4>';
        $content .= '<li>Carte  : ........800</li>';
        $content .= '<li>Crypto : 600</li>';
        $content .= '<li>Date   : 2021</li>';
        $content .= '<br>';
        $content .= '<h4>Paiement Refusé</h4>';
        $content .= '<li>Carte  : ........205</li>';
        $content .= '<li>Crypto : 600</li>';
        $content .= '<li>Date   : 2021</li>';
        $content .= '</ul><br>';

        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }


    // ################ Connexion Page ################ //
    public function makeConnexionPage($builder, $currentPage)
    {
        $title = "Page de connexion";

        $data = $builder->getData();

        $loginRef = $builder->getLoginRef();
        $passwordRef = $builder->getPasswordRef();

        $errLogin = $builder->getErrors($loginRef);
        $errPassword = $builder->getErrors($passwordRef);

        $content = '<section class="connexionPageSection">';

        $content .= '<form class="box" action="index.php?obj=pdf&action=connexion&id='.self::htmlesc($currentPage).'" method="POST">';
        $content .= '<input type="text" name="'.self::htmlesc($loginRef).'" placeholder="Login" value="'.self::htmlesc($data[$loginRef]).'">';
        if ($errLogin !== null) {
            $content .= '<span class="errors">'.self::htmlesc($errLogin).'</span>';
        }
        $content .= '<input type="password" name="'.self::htmlesc($passwordRef).'" placeholder="Password" value="'.self::htmlesc($data[$passwordRef]).'">';
        if ($errPassword !== null) {
            $content .= '<span class="errors">'.self::htmlesc($errPassword).'</span>';
        }
        $content .= '<button type="submit">Se connecter</button>';
        $content .= '</form>';

        $content .= '</section>';

        $this->setPart('title', $title);
        $this->setPart('content', $content);
    }

    public function makeUserConnectedPage()
    {
        $title = "Bienvenue " . self::htmlesc($_SESSION['user']['prenom']);

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
    public function displayConnexionSucces()
    {
        $this->router->POSTredirect("index.php", "<p class='feedback'>Vous êtes bien connecté en tant que ".self::htmlesc($_SESSION['user']['statut'])."</p>");
    }

    public function displayConnexionSuccesToCurrentPage($currentPage)
    {
        $this->router->POSTredirect("index.php?obj=pdf&action=".self::htmlesc($currentPage), "<p class='feedback'>Vous êtes bien connecté en tant que ".self::htmlesc($_SESSION['user']['statut'])."</p>");
    }

    public function displayRequireConnexion($action)
    {
        $this->router->POSTredirect("index.php?obj=pdf&action=askConnexion&id=".self::htmlesc($action), "<p class='feedback'>Connexion requise pour accèder à cette page</p>");
    }

    public function displayDeconnexionSucces()
    {
        $this->router->POSTredirect("index.php?obj=pdf&action=askConnexion", "<p class='feedback'>Déconnexion réussi</p>");
    }



    // ################ Unknown Page ################ //
    public function unknownPdfPage()
    {
        $title = "Poème inconnu ou non trouvé";
        $content = "Choisir un poème dans la liste.";

        $this->view->setPart('title', $title);
        $this->view->setPart('content', $content);
    }



    // ################ Utilitaire ################ //
    public static function htmlesc($str)
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }
}
