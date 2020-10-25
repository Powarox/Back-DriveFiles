<?php

namespace MoriniereRobinDev\DevoirApp\Model;

class Account{
    public function __construct($login, $password){
        $this->login = $login;
        $this->password = $password;
    }
    
    public function getLogin(){
        return $this->login;
    }
    
    public function getPassword(){
        return $this->password;
    }
}