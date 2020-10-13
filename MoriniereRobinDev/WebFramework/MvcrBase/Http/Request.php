<?php

namespace MoriniereRobinDev\WebFramework\MvcrBase\Http;

class Request {
    private $get;
    private $post;

    public function __construct($get, $post, $session){
        $this->get = $get;
        $this->post = $post;
        $this->session = $session;
    }
    
    public function getGetParam($key, $default = null){
        if (!isset($this->get[$key])){
            return $default;
        }
        return $this->get[$key];
    }
    
    public function getPostParam($key, $default = null){
        if(!isset($this->post[$key])){
            return $default;
        }
        return $this->post[$key];
    }

    public function getAllPostParams(){
        return $this->post;
    }
    
/*    public function getSessionParam($key, $default = null){
        if(!isset($this->session[$key])){
            return $default;
        }
        return $this->session[$key];
    }*/
    
    public function isAjaxRequest(){
    	return (!empty($this->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }
}