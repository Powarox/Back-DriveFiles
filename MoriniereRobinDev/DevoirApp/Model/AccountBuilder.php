<?php 
    
namespace MoriniereRobinDev\DevoirApp\Model;

class AccountBuilder{
    const LOGIN_REF = "login";
    const PASSWORD_REF = "password";
     
    public function __construct($data = null){
        if($data === null){
            $data = array(
                self::LOGIN_REF => "",
                self::PASSWORD_REF => ""
            );
        }
        $this->data = $data;
        $this->errors = array();
    }
     
    public function getData(){
       return $this->data;
    }
    
    public function getErrors($ref){
        return key_exists($ref, $this->errors)? $this->errors[$ref]: null;
    }
    
    public function getLoginRef(){
        return self::LOGIN_REF;
    }
    
    public function getPasswordRef(){
        return self::PASSWORD_REF;
    }
    
    public function setError($ref, $error){
        $this->errors[$ref] = $error;
    }
          
    public function isValidInscription(){
        $this->mbstrlen(self::LOGIN_REF);
        if($this->data[self::LOGIN_REF] === ""){
            $this->errors[self::LOGIN_REF] = "Vous devez entrer un login ";
        }
        $this->mbstrlen(self::PASSWORD_REF);
        if($this->data[self::PASSWORD_REF] === ""){
            $this->errors[self::PASSWORD_REF] = "Vous devez entrer une password ";
        }
        return count($this->errors) === 0;
    }
     
    public function mbstrlen($ref){
        if(mb_strlen($this->data[$ref], 'UTF-8') >= 30){
            $this->errors[$ref] = "Le nom doit faire moins de 30 caractères";
        }
    }
}