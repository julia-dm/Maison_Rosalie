<?php
// path: controller/routerController.php


# Connexion PDO
try {
    $connectPDO = new PDO(
        DB_TYPE . ':host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_LOGIN,
        DB_PWD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (Exception $e) {
    die($e->getMessage());
}

// partie administration (?pg=admin) : réservée aux comptes dont le rôle est 'admin'
// ($_SESSION['role'] est posé à la connexion ; adminController revérifie le rôle en base)
$wantsAdmin = ($_GET['pg'] ?? '') === 'admin';
if ($wantsAdmin && ($_SESSION['role'] ?? '') === 'admin') {
    // on charge le contrôleur admin
    require_once RACINE_PATH . "/controller/adminController.php";
} elseif ($wantsAdmin) {
    // pas admin : retour à l'accueil (le panneau de connexion s'ouvre avec le message)
    $_SESSION['login_error'] = "Cette page est réservée aux administrateurs. Connectez-vous avec un compte admin.";
    header('Location: ?page=accueil');
    exit;
} else {
    // on charge le contrôleur public
    require_once RACINE_PATH . "/controller/publicController.php";
}
// Bonne pratique
$connectPDO = null;

