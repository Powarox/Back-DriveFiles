<?php

namespace MoriniereRobinDev;

require_once('Tools/Loader/Autoload.php');

session_name("monSiteID"); //--> rename cookie PHPSESSID
session_start();

// function monAutoload($class){
//     $arrayCheminClass = explode("\\", $class);
//     //var_dump($arrayCheminClass);
//     array_shift($arrayCheminClass);
//     $stringCheminClass = implode('/', $arrayCheminClass);
//     include($stringCheminClass . '.php');
// }

spl_autoload_register('MoriniereRobinDev\Tools\Loader\Autoload::monAutoload');

//set_include_path("./MoriniereRobinDev");

use MoriniereRobinDev\WebFramework\MvcrBase\Http;
use MoriniereRobinDev\WebFramework\MvcrBase;


$request = new WebFramework\MvcrBase\Http\Request($_GET, $_POST, $_SESSION);
$response = new WebFramework\MvcrBase\Http\Response();
$template = 'DevoirApp/Template.php';

$frontController = new WebFramework\MvcrBase\FrontController($request, $response, $template);
$frontController->execute();
