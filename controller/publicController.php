<?php
// chemin vers les dépendances
use model\mapping\UserMapping;
use model\manager\RecipeManager;
use model\manager\IngredientsManager;
use model\manager\StepsManager;
use model\manager\UserManager;
use model\manager\MailManager;
use model\manager\CommentManager;
use model\manager\RatingManager;
use model\mapping\CommentMapping;
$recipeManager = new RecipeManager($connectPDO);
$ingredientsManager = new IngredientsManager($connectPDO);
$stepsPrepManager=new StepsManager($connectPDO);
$userManager = new UserManager($connectPDO);
$commentManager = new CommentManager($connectPDO);
$ratingManager = new RatingManager($connectPDO);

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

    // ---------- Avis (note + commentaire) ----------
    // l'utilisateur connecté est attendu dans $_SESSION['user_id'] et $_SESSION['username']
    $currentUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

    // jeton anti-CSRF pour le formulaire d'avis
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $reviewErrors = [];
    $reviewValues = ['rating' => 0, 'message' => ''];
    $reviewSent = isset($_GET['avis']) && $_GET['avis'] === 'ok';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
        $reviewValues['rating'] = (int) ($_POST['rating'] ?? 0);
        $reviewValues['message'] = trim($_POST['message'] ?? '');

        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            $reviewErrors[] = "La session a expiré, veuillez réessayer.";
        }
        if ($currentUserId === null) {
            $reviewErrors[] = "Vous devez être connecté pour laisser un avis.";
        }
        if ($reviewValues['rating'] < 1 || $reviewValues['rating'] > 5) {
            $reviewErrors[] = "Choisissez une note entre 1 et 5 étoiles.";
        }
        if ($reviewValues['message'] === '' || mb_strlen($reviewValues['message']) > 500) {
            $reviewErrors[] = "Votre commentaire est obligatoire (500 caractères maximum).";
        }

        if (empty($reviewErrors)) {
            $comment = new CommentMapping([
                'author_id' => $currentUserId,
                'recipe_id' => $id,
                'message' => $reviewValues['message'],
            ]);
            try {
                $connectPDO->beginTransaction();
                $ratingManager->saveRating($currentUserId, $id, $reviewValues['rating']);
                $commentManager->addComment($comment);
                $connectPDO->commit();

                // Post/Redirect/Get : évite le double envoi au rafraîchissement
                header('Location: ?page=recetteDetails&slug=' . urlencode($recetteDetails->getSlug()) . '&avis=ok#avis');
                exit;
            } catch (Exception $e) {
                $connectPDO->rollBack();
                $reviewErrors[] = "Une erreur est survenue, votre avis n'a pas été enregistré.";
            }
        }
    }

    $comments = $commentManager->getPublishedByRecipeId($id);
    $ratingSummary = $ratingManager->getSummary($id);


    require RACINE_PATH . '/view/recetteDetails.php';
    exit;

            }
                
