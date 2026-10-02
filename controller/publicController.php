<?php
// chemin vers les dépendances
use model\manager\RecipeManager;
$recipeManager = new RecipeManager($connectPDO);
$menuRecipes = $recipeManager->getRecipesForMenu();

$page = $_GET['page'] ?? 'accueil';
if ($page === 'accueil') {

    require_once RACINE_PATH . '/view/accueil.php';

} elseif ($page === 'apropos') {
    require_once RACINE_PATH. '/view/apropos.php';
}
elseif ($page === 'contact') {
    require_once RACINE_PATH. '/view/contact.php';
}
elseif ($page === 'recettes') {
    require_once RACINE_PATH. '/view/recettes.php';
}
elseif ($page === 'detailsRecet') {
    require_once RACINE_PATH. '/view/recetteDetails.php';
}
elseif ($page === 'inscription') {
    $errors = [];
    $success = false;

    // traitement formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // TODO
    }

    require_once RACINE_PATH. '/view/inscription.php';
}
