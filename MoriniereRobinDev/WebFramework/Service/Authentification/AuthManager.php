<?php

namespace MoriniereRobinDev\WebFramework\Service\Authentification;

class AuthManager{
    public function __construct(){
        $this->users = array(
            'jml' => array(
                'id' => 12,
                'nom' => 'Lecarpentier',
                'prenom' => 'Jean-Marc',
                'mdp' => 'toto',
                'statut' => 'admin'
            ),
            'alex' => array(
                'id' => 5,
                'nom' => 'Niveau',
                'prenom' => 'Alexandre',
                'mdp' => 'toto',
                'statut' => 'admin'
            )
        );
    }

    public function checkAuth($login, $password){
        if(key_exists($login, $this->users)){
            $user = $this->users[$login];
            if($user['mdp'] == $password){
                $_SESSION['user'] = $user;
                return true;
            }
            else{
                return 'password';
            }
        }
        return 'login';
    }

    public function isUserConnected(){
        if(key_exists('user', $_SESSION)){
            return true;
        }
        else{
            return false;
        }
    }

    public function isAdminConnected(){
        if(key_exists('user', $_SESSION)){
            if($_SESSION['user']['statut'] === 'admin'){
                return true;
            }
        }
        else{
            return false;
        }
    }

    public function disconnectUser(){
        session_destroy();
    }
}
