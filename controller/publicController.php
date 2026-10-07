<?php
// chemin vers les dépendances
use model\manager\RecipeManager;
use model\manager\IngredientsManager;
use model\manager\StepsManager;
$recipeManager = new RecipeManager($connectPDO);
$ingredientsManager = new IngredientsManager($connectPDO);
$stepsPrepManager=new StepsManager($connectPDO);


$page = $_GET['page']?? 'accueil';
if ($page === 'accueil') {

    $recipes = $recipeManager->getAllRecipes();
    require_once RACINE_PATH . '/view/accueil.php';

} elseif ($page === 'apropos') {
    require_once RACINE_PATH. '/view/apropos.php';
}
elseif ($page === 'contact') {
    require_once RACINE_PATH. '/view/contact.php';
}
elseif ($page === 'recettes') {

    $recipes = $recipeManager->getAllRecipes();
    require RACINE_PATH . '/view/recettes.php';
    exit;
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
elseif ($page === 'recetteDetails') {

    if (!isset($_GET['slug'])) {
        http_response_code(404);
        require RACINE_PATH . '/view/404.php';
        exit;
    }

    $recetteDetails = $recipeManager->getRecipeBySlug($_GET['slug']);

    if ($recetteDetails === null) {
        http_response_code(404);
        require RACINE_PATH . '/view/404.php';
        exit;
    }
        $id = $recetteDetails->getId();
        $ingredients = $ingredientsManager->getIngredientsByRecipeId($id);
        $steps = $stepsPrepManager->getStepsById($id);


    require RACINE_PATH . '/view/recetteDetails.php';
    exit;

            }
                
