<?php

namespace MoriniereRobinDev\Tools\Loader;

class Autoload {
    static public function monAutoload($class){
        $arrayCheminClass = explode("\\", $class);
        array_shift($arrayCheminClass);
        $stringCheminClass = implode('/', $arrayCheminClass);
        include($stringCheminClass . '.php');
    }
}
