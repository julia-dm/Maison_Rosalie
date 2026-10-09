<?php
declare(strict_types=1);

session_start();

require_once '../config.php';
require RACINE_PATH."/vendor/autoload.php";

spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    $file = RACINE_PATH . '/' . $class . '.php';
    // seulement nos propres classes : si le fichier n'existe pas, on laisse faire
    // (Symfony Mailer teste des classes optionnelles avec class_exists())
    if (is_file($file)) {
        require $file;
    }
});


require_once RACINE_PATH. '/controller/routerController.php';