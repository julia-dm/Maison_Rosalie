<?php
// chemin vers les dépendances
use model\mapping\UserMapping;
use model\manager\RecipeManager;
use model\manager\IngredientsManager;
use model\manager\StepsManager;
use model\manager\UserManager;
use model\manager\MailManager;
$recipeManager = new RecipeManager($connectPDO);
$ingredientsManager = new IngredientsManager($connectPDO);
$stepsPrepManager=new StepsManager($connectPDO);
$userManager = new UserManager($connectPDO);

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
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($userManager->emailOrUsernameExists($email, $username)) {
        $errors[] = "Email ou nom d'utilisateur existe déjà";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide";
    }
    if (empty($username) || strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Le nom d'utilisateur n'est pas valide";
    }
    if (empty($password) || strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    }
    if (empty($errors)) {

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user = new UserMapping([
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'generated_key' => ''
        ]);
       /*  if ($userManager->createUser($user)) {
                $mailManager = new MailManager();
            $mailManager->sendVerificationEmail(
                $email,
                $user->getGeneratedKey()
            ); */
    
            $success = true;
        }
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
                
