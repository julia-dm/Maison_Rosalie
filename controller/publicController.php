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
use model\manager\ContactManager;
use model\mapping\ContactMapping;
$recipeManager = new RecipeManager($connectPDO);
$ingredientsManager = new IngredientsManager($connectPDO);
$stepsPrepManager=new StepsManager($connectPDO);
$userManager = new UserManager($connectPDO);
$commentManager = new CommentManager($connectPDO);
$ratingManager = new RatingManager($connectPDO);

// jeton anti-CSRF partagé par les formulaires (connexion, avis…)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ---------- Connexion / déconnexion (panneau du header, sur toutes les pages) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['login_submit']) || isset($_POST['logout_submit']))) {
    // on revient sur la page d'où vient le formulaire (URL relative uniquement)
    $back = '?' . http_build_query($_GET);
    $back = $back === '?' ? '?page=accueil' : $back;

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $_SESSION['login_error'] = "La session a expiré, veuillez réessayer.";
    } elseif (isset($_POST['logout_submit'])) {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role_name']);
        session_regenerate_id(true);
    } else {
        $loginEmail = trim($_POST['email'] ?? '');
        $loginUser = $userManager->getUserByEmail($loginEmail);

        if ($loginUser !== null && password_verify($_POST['password'] ?? '', $loginUser->getPasswordHash())) {
            session_regenerate_id(true); // évite la fixation de session
            $_SESSION['user_id'] = $loginUser->getId();
            $_SESSION['username'] = $loginUser->getUsername();
            // le routeur attend 'Admin' pour ouvrir l'administration
            $_SESSION['role_name'] = $loginUser->getRole() === 'admin' ? 'Admin' : 'User';
        } else {
            $_SESSION['login_error'] = "E-mail ou mot de passe incorrect.";
            $_SESSION['login_email'] = $loginEmail;
        }
    }
    header('Location: ' . $back);
    exit;
}

$page = $_GET['page']?? 'accueil';
if ($page === 'accueil') {

    $recipes = $recipeManager->getAllRecipes();
    $topRecipes = $recipeManager->getTopRecipes(3);
    require_once RACINE_PATH . '/view/accueil.php';

} elseif ($page === 'apropos') {
    require_once RACINE_PATH. '/view/apropos.php';
}
elseif ($page === 'contact') {
    $contactErrors = [];
    $contactValues = ['fullname' => '', 'email' => '', 'message' => ''];
    $contactSent = isset($_GET['sent']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $contactValues['fullname'] = trim($_POST['fullname'] ?? '');
        $contactValues['email'] = trim($_POST['email'] ?? '');
        $contactValues['message'] = trim($_POST['message'] ?? '');

        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            $contactErrors[] = "La session a expiré, veuillez réessayer.";
        }
        if ($contactValues['fullname'] === '' || mb_strlen($contactValues['fullname']) > 100) {
            $contactErrors[] = "Veuillez indiquer votre nom (100 caractères maximum).";
        }
        if (!filter_var($contactValues['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($contactValues['email']) > 254) {
            $contactErrors[] = "Veuillez indiquer une adresse e-mail valide.";
        }
        if ($contactValues['message'] === '' || mb_strlen($contactValues['message']) > 2000) {
            $contactErrors[] = "Votre message est obligatoire (2000 caractères maximum).";
        }

        if (empty($contactErrors)) {
            // 1) on garde une trace du message en base
            $saved = false;
            try {
                $contactManager = new ContactManager($connectPDO);
                $saved = $contactManager->addMessage(new ContactMapping([
                    'name' => $contactValues['fullname'],
                    'email' => $contactValues['email'],
                    'subject' => 'Message depuis le formulaire de contact',
                    'message' => $contactValues['message'],
                ]));
            } catch (Throwable $e) {
                error_log('Contact (base de données) : ' . $e->getMessage());
            }

            // 2) on prévient Maison Rosalie par e-mail (Symfony Mailer)
            $mailed = false;
            try {
                $mailManager = new MailManager();
                $mailManager->sendContactMessage($contactValues['fullname'], $contactValues['email'], $contactValues['message']);
                $mailed = true;
            } catch (Throwable $e) {
                error_log('Contact (envoi du mail) : ' . $e->getMessage());
            }

            // le message n'est perdu que si les deux ont échoué
            if ($saved || $mailed) {
                header('Location: ?page=contact&sent=1');
                exit;
            }
            $contactErrors[] = "Votre message n'a pas pu être envoyé, veuillez réessayer plus tard.";
        }
    }

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
    $passwordConfirm = $_POST['password_confirm'] ?? '';
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
    if ($password !== $passwordConfirm) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }
    if (empty($errors)) {

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user = new UserMapping([
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'generated_key' => ''
        ]);
        $userManager->createUser($user);
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
                
