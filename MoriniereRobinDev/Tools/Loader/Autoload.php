<?php

namespace MoriniereRobinDev\Tools\Loader;

class Autoload {
    function monAutoload($class){
        $arrayCheminClass = explode("\\", $class);

        array_shift($arrayCheminClass);

        $stringCheminClass = implode('/', $arrayCheminClass);

        include($stringCheminClass . '.php');
    }
}