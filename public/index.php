<?php
declare(strict_types=1);

session_start();

require_once '../config.php';
require RACINE_PATH."/vendor/autoload.php";

spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    require RACINE_PATH.'/' .$class . '.php';

});


require_once RACINE_PATH. '/controller/routerController.php';