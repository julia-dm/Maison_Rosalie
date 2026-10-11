<?php
// controller/adminController.php
// Chargé par routerController.php quand ?pg=admin et $_SESSION['role'] === 'admin'.
// Adresses : ?pg=admin&section=<dashboard|recettes|avis|messages|utilisateurs>&action=…&id=…

use model\manager\RecipeManager;
use model\manager\IngredientsManager;
use model\manager\StepsManager;
use model\manager\UserManager;
use model\manager\CommentManager;
use model\manager\ContactManager;
use model\service\ImageUpload;

// échappement pour les vues admin
function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// redirection après une action (Post/Redirect/Get), avec un message affiché une fois
function adminRedirect(string $query, ?string $type = null, ?string $text = null): void
{
    if ($type !== null) {
        $_SESSION['admin_flash'] = ['type' => $type, 'text' => $text];
    }
    header('Location: ?pg=admin' . $query);
    exit;
}

$recipeManager = new RecipeManager($connectPDO);
$ingredientsManager = new IngredientsManager($connectPDO);
$stepsManager = new StepsManager($connectPDO);
$userManager = new UserManager($connectPDO);
$commentManager = new CommentManager($connectPDO);
$contactManager = new ContactManager($connectPDO);

// le rôle est revérifié en base à chaque requête (un admin rétrogradé perd l'accès tout de suite)
$me = $userManager->getById((int) ($_SESSION['user_id'] ?? 0));
if ($me === null || $me['role'] !== 'admin') {
    $_SESSION['role'] = $me['role'] ?? 'user';
    $_SESSION['login_error'] = "Cette page est réservée aux administrateurs.";
    header('Location: ?page=accueil');
    exit;
}
$myId = (int) $me['id'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$section = $_GET['section'] ?? 'dashboard';
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$backQuery = '&section=' . urlencode($section) . ($action !== 'list' ? '&action=' . urlencode($action) : '') . ($id ? '&id=' . $id : '');

// ---------- contrôles communs à tous les formulaires ----------
if ($isPost) {
    // envoi plus lourd que post_max_size : PHP vide $_POST et $_FILES
    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        adminRedirect($backQuery, 'error', "L'envoi est trop lourd : réduisez la taille des images.");
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        adminRedirect($backQuery, 'error', "La session a expiré, veuillez réessayer.");
    }
}

// message flash (affiché une seule fois)
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

// compteurs de la barre latérale
$commentCounts = $commentManager->countByStatus();
try {
    $unreadMessages = $contactManager->countUnread();
    $messagesReady = true;
} catch (PDOException $e) {
    // colonne is_read absente : data/migration_admin.sql n'a pas encore été lancé
    $unreadMessages = 0;
    $messagesReady = false;
}

$difficulties = ['facile' => 'Facile', 'moyen' => 'Moyen', 'difficile' => 'Difficile'];

// ======================================================================
// TABLEAU DE BORD
// ======================================================================
if ($section === 'dashboard') {
    $stats = [
        'recipes' => $recipeManager->countAll(),
        'pending' => $commentCounts['pending'],
        'comments' => $commentCounts['all'],
        'unread' => $unreadMessages,
        'users' => $userManager->countAll(),
    ];
    $topRecipes = $recipeManager->getTopRecipes(3);
    $latestComments = $commentManager->getAllForAdmin($commentCounts['pending'] > 0 ? 'pending' : null, 3);
    $pageTitle = 'Tableau de bord';
    require RACINE_PATH . '/view/admin/dashboard.php';
}

// ======================================================================
// RECETTES
// ======================================================================
elseif ($section === 'recettes') {

    // ---------- suppression ----------
    if ($action === 'delete') {
        if (!$isPost) {
            adminRedirect('&section=recettes');
        }
        $recipe = $recipeManager->getRowById($id);
        if ($recipe === null) {
            adminRedirect('&section=recettes', 'error', "Cette recette n'existe plus.");
        }
        $stepImages = array_column($stepsManager->getStepRows($id), 'image');
        $recipeManager->deleteRecipe($id);
        // fichiers créés par l'admin uniquement (les images d'origine du dépôt restent)
        ImageUpload::deleteGenerated('main-recipe-img', $recipe['main_image']);
        ImageUpload::deleteGenerated('cards', $recipe['main_image']);
        foreach ($stepImages as $image) {
            ImageUpload::deleteGenerated('steps', $image);
        }
        adminRedirect('&section=recettes', 'success', "« " . $recipe['title'] . " » a été supprimée.");
    }

    // ---------- création / modification ----------
    elseif ($action === 'new' || $action === 'edit') {
        $isNew = $action === 'new';
        $recipe = $isNew ? null : $recipeManager->getRowById($id);
        if (!$isNew && $recipe === null) {
            adminRedirect('&section=recettes', 'error', "Cette recette n'existe plus.");
        }
        $tab = $isNew ? 'infos' : ($_GET['tab'] ?? 'infos');
        if (!in_array($tab, ['infos', 'ingredients', 'etapes'], true)) {
            $tab = 'infos';
        }
        $errors = [];
        $allIngredients = $ingredientsManager->getAllIngredients();

        // valeurs affichées dans le formulaire « Informations »
        $form = [
            'title' => $recipe['title'] ?? '',
            'slug' => $recipe['slug'] ?? '',
            'description' => $recipe['description'] ?? '',
            'prep_time_minutes' => $recipe['prep_time_minutes'] ?? '',
            'cook_time_minutes' => $recipe['cook_time_minutes'] ?? '',
            'portions' => $recipe['portions'] ?? 6,
            // l'ENUM accepte aussi les anciennes valeurs anglaises
            'difficulty' => ['easy' => 'facile', 'medium' => 'moyen', 'hard' => 'difficile'][$recipe['difficulty'] ?? ''] ?? ($recipe['difficulty'] ?? 'facile'),
            'main_image' => $recipe['main_image'] ?? '',
        ];
        $ingredientRows = $isNew ? [] : $ingredientsManager->getRecipeIngredientRows($id);
        $stepRows = $isNew ? [] : $stepsManager->getStepRows($id);

        $formType = $isPost ? ($_POST['form_type'] ?? '') : '';

        // ---- onglet Informations ----
        if ($formType === 'infos') {
            $form['title'] = trim($_POST['title'] ?? '');
            $form['slug'] = trim($_POST['slug'] ?? '');
            $form['description'] = trim($_POST['description'] ?? '');
            $form['prep_time_minutes'] = trim($_POST['prep_time_minutes'] ?? '');
            $form['cook_time_minutes'] = trim($_POST['cook_time_minutes'] ?? '');
            $form['portions'] = trim($_POST['portions'] ?? '');
            $form['difficulty'] = $_POST['difficulty'] ?? '';

            if ($form['slug'] === '') {
                // slug fabriqué depuis le titre : « La Praline Orangette » → la-praline-orangette
                $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $form['title']) ?: $form['title'];
                $form['slug'] = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($ascii)), '-');
            }

            $len = mb_strlen($form['title']);
            if ($len < 3 || $len > 120) {
                $errors[] = "Le titre doit faire entre 3 et 120 caractères.";
            }
            if (preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $form['slug']) !== 1 || strlen($form['slug']) > 170) {
                $errors[] = "L'adresse (slug) ne peut contenir que des minuscules, des chiffres et des tirets.";
            } elseif ($recipeManager->slugExists($form['slug'], $isNew ? null : $id)) {
                $errors[] = "Une autre recette utilise déjà l'adresse « " . $form['slug'] . " ».";
            }
            if ($form['description'] === '') {
                $errors[] = "La description est obligatoire.";
            }
            foreach (['prep_time_minutes' => 'Le temps de préparation', 'cook_time_minutes' => 'Le temps de cuisson'] as $key => $label) {
                if (filter_var($form[$key], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 10000]]) === false) {
                    $errors[] = "$label doit être un nombre de minutes (0 à 10 000).";
                }
            }
            if (filter_var($form['portions'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]) === false) {
                $errors[] = "Le nombre de portions doit être compris entre 1 et 100.";
            }
            if (!isset($difficulties[$form['difficulty']])) {
                $errors[] = "Choisissez une difficulté.";
            }

            $sentWhole = ImageUpload::wasSent($_FILES['image_entiere'] ?? null);
            $sentCut = ImageUpload::wasSent($_FILES['image_coupee'] ?? null);
            if ($isNew && !$sentWhole) {
                $errors[] = "Ajoutez l'image de la praline entière.";
            }

            if (empty($errors)) {
                $oldImage = $recipe['main_image'] ?? '';
                $newImage = $oldImage;
                $created = [];
                try {
                    if ($sentWhole || $sentCut) {
                        // même nom dans les deux dossiers : main-recipe-img/ (entière) et cards/ (coupée)
                        $newImage = ImageUpload::newName($form['slug'], 'png');
                        if ($sentWhole) {
                            ImageUpload::save($_FILES['image_entiere'], 'main-recipe-img', $form['slug'], 'png', $newImage);
                        } elseif (!ImageUpload::copy('main-recipe-img', $oldImage, 'main-recipe-img', $newImage)) {
                            throw new RuntimeException("L'image actuelle de la praline entière est introuvable : envoyez-en une nouvelle.");
                        }
                        $created[] = ['main-recipe-img', $newImage];

                        if ($sentCut) {
                            ImageUpload::save($_FILES['image_coupee'], 'cards', $form['slug'], 'png', $newImage);
                        } elseif (!ImageUpload::copy('cards', $oldImage, 'cards', $newImage)) {
                            // pas encore d'image coupée : on reprend la praline entière
                            ImageUpload::copy('main-recipe-img', $newImage, 'cards', $newImage);
                        }
                        $created[] = ['cards', $newImage];
                    }

                    $data = $form;
                    $data['main_image'] = $newImage;
                    $data['prep_time_minutes'] = (int) $form['prep_time_minutes'];
                    $data['cook_time_minutes'] = (int) $form['cook_time_minutes'];
                    $data['portions'] = (int) $form['portions'];

                    if ($isNew) {
                        $newId = $recipeManager->createRecipe($data, $myId);
                        adminRedirect('&section=recettes&action=edit&id=' . $newId . '&tab=ingredients', 'success',
                            "Recette créée. Ajoutez maintenant ses ingrédients et ses étapes.");
                    }
                    $recipeManager->updateRecipe($id, $data);
                    if ($newImage !== $oldImage) {
                        ImageUpload::deleteGenerated('main-recipe-img', $oldImage);
                        ImageUpload::deleteGenerated('cards', $oldImage);
                    }
                    adminRedirect('&section=recettes&action=edit&id=' . $id, 'success', "La recette a bien été enregistrée.");
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                } catch (PDOException $e) {
                    error_log('Admin recette : ' . $e->getMessage());
                    $errors[] = "La recette n'a pas pu être enregistrée.";
                }
                // échec : on retire les fichiers déjà créés
                foreach ($created as [$folder, $file]) {
                    ImageUpload::deleteGenerated($folder, $file);
                }
            }
        }

        // ---- onglet Ingrédients ----
        elseif ($formType === 'ingredients' && !$isNew) {
            $known = array_column($allIngredients, null, 'id');
            $posted = [];
            $ids = $_POST['ingredient_id'] ?? [];
            $quantities = $_POST['quantity'] ?? [];
            $units = $_POST['unit'] ?? [];
            foreach ((array) $ids as $i => $ingredientId) {
                $ingredientId = (int) $ingredientId;
                $quantity = str_replace(',', '.', trim((string) ($quantities[$i] ?? '')));
                $unit = trim((string) ($units[$i] ?? ''));
                if ($ingredientId === 0 && $quantity === '' && $unit === '') {
                    continue; // ligne vide
                }
                $posted[] = [
                    'ingredient_id' => $ingredientId,
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'name' => trim($known[$ingredientId]['name'] ?? ''),
                    'img_ingredient' => $known[$ingredientId]['img_ingredient'] ?? '',
                ];
            }
            $seen = [];
            foreach ($posted as $n => $row) {
                $line = $n + 1;
                if (!isset($known[$row['ingredient_id']])) {
                    $errors[] = "Ligne $line : choisissez un ingrédient.";
                } elseif (isset($seen[$row['ingredient_id']])) {
                    $errors[] = "« " . $row['name'] . " » est présent deux fois.";
                }
                $seen[$row['ingredient_id']] = true;
                if ($row['quantity'] !== '' && (!is_numeric($row['quantity']) || (float) $row['quantity'] < 0 || (float) $row['quantity'] > 999999)) {
                    $errors[] = "Ligne $line : la quantité doit être un nombre positif (ou rester vide).";
                }
                if (mb_strlen($row['unit']) > 40) {
                    $errors[] = "Ligne $line : l'unité est trop longue (40 caractères maximum).";
                }
            }
            if (count($posted) > 40) {
                $errors[] = "40 ingrédients maximum par recette.";
            }
            if (empty($errors)) {
                $ingredientsManager->replaceRecipeIngredients($id, array_map(fn ($r) => [
                    'ingredient_id' => $r['ingredient_id'],
                    'quantity' => $r['quantity'] === '' ? null : $r['quantity'],
                    'unit' => $r['unit'] === '' ? null : $r['unit'],
                ], $posted));
                adminRedirect('&section=recettes&action=edit&id=' . $id . '&tab=ingredients', 'success', "Les ingrédients ont bien été enregistrés.");
            }
            $ingredientRows = $posted;
            $tab = 'ingredients';
        }

        // ---- nouvel ingrédient (catalogue commun à toutes les recettes) ----
        elseif ($formType === 'ingredient_create' && !$isNew) {
            $name = trim($_POST['new_ingredient_name'] ?? '');
            $len = mb_strlen($name);
            if ($len < 3 || $len > 120) {
                $errors[] = "Le nom de l'ingrédient doit faire entre 3 et 120 caractères.";
            } elseif ($ingredientsManager->ingredientNameExists($name)) {
                $errors[] = "L'ingrédient « $name » existe déjà : choisissez-le dans la liste.";
            }
            if (!ImageUpload::wasSent($_FILES['new_ingredient_image'] ?? null)) {
                $errors[] = "Ajoutez une photo de l'ingrédient.";
            }
            if (empty($errors)) {
                try {
                    $file = ImageUpload::save($_FILES['new_ingredient_image'], 'ingridients', $name, 'jpg');
                    $ingredientsManager->createIngredient($name, $file);
                    adminRedirect('&section=recettes&action=edit&id=' . $id . '&tab=ingredients', 'success',
                        "« $name » a été ajouté au catalogue : vous pouvez le choisir dans la liste.");
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }
            $tab = 'ingredients';
        }

        // ---- onglet Étapes ----
        elseif ($formType === 'etapes' && !$isNew) {
            $titles = (array) ($_POST['step_title'] ?? []);
            $descs = (array) ($_POST['step_description'] ?? []);
            $olds = (array) ($_POST['step_image_old'] ?? []);
            $files = $_FILES['step_image'] ?? null;
            $previousImages = array_column($stepRows, 'image');
            $posted = [];
            $created = [];
            try {
                foreach ($titles as $i => $title) {
                    $title = trim((string) $title);
                    $desc = trim((string) ($descs[$i] ?? ''));
                    $old = basename(trim((string) ($olds[$i] ?? '')));
                    $file = $files ? [
                        'name' => $files['name'][$i] ?? '', 'type' => $files['type'][$i] ?? '',
                        'tmp_name' => $files['tmp_name'][$i] ?? '', 'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                        'size' => $files['size'][$i] ?? 0,
                    ] : null;
                    $sent = ImageUpload::wasSent($file);
                    if ($title === '' && $desc === '' && !$sent && $old === '') {
                        continue; // étape vide
                    }
                    $line = count($posted) + 1;
                    if (mb_strlen($title) < 2 || mb_strlen($title) > 120) {
                        $errors[] = "Étape $line : le titre doit faire entre 2 et 120 caractères.";
                    }
                    if ($desc === '') {
                        $errors[] = "Étape $line : la description est obligatoire.";
                    }
                    $image = $old;
                    if ($sent && empty($errors)) {
                        $image = ImageUpload::save($file, 'steps', $form['slug'] . '-etape', 'jpg');
                        $created[] = $image;
                    }
                    if ($image === '') {
                        $errors[] = "Étape $line : ajoutez une photo.";
                    }
                    $posted[] = ['title' => $title, 'description' => $desc, 'image' => $image];
                }
                if (count($posted) > 20) {
                    $errors[] = "20 étapes maximum par recette.";
                }
                if (empty($errors)) {
                    $stepsManager->replaceSteps($id, $posted);
                    // photos remplacées ou étapes supprimées : on retire les fichiers devenus inutiles
                    foreach (array_diff($previousImages, array_column($posted, 'image')) as $unused) {
                        ImageUpload::deleteGenerated('steps', $unused);
                    }
                    adminRedirect('&section=recettes&action=edit&id=' . $id . '&tab=etapes', 'success', "Les étapes ont bien été enregistrées.");
                }
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
            foreach ($created as $file) {
                ImageUpload::deleteGenerated('steps', $file);
            }
            // on réaffiche ce qui a été saisi (sans les photos refusées)
            $stepRows = array_map(fn ($s) => [
                'title' => $s['title'], 'description' => $s['description'],
                'image' => in_array($s['image'], $created, true) ? '' : $s['image'],
            ], $posted);
            $tab = 'etapes';
        }

        $pageTitle = $isNew ? 'Nouvelle recette' : 'Modifier la recette';
        require RACINE_PATH . '/view/admin/recette-form.php';
    }

    // ---------- liste ----------
    else {
        $recipes = $recipeManager->getAllForAdmin();
        $pageTitle = 'Recettes';
        require RACINE_PATH . '/view/admin/recettes.php';
    }
}

// ======================================================================
// AVIS
// ======================================================================
elseif ($section === 'avis') {
    $filter = $_GET['statut'] ?? 'tous';
    $filterQuery = '&section=avis' . ($filter !== 'tous' ? '&statut=' . urlencode($filter) : '');

    if ($isPost) {
        $commentId = (int) ($_POST['comment_id'] ?? 0);
        $do = $_POST['do'] ?? '';
        if ($do === 'delete') {
            $commentManager->deleteComment($commentId);
            adminRedirect($filterQuery, 'success', "L'avis a été supprimé.");
        }
        $status = ['publish' => 'published', 'hide' => 'hidden', 'pending' => 'pending'][$do] ?? null;
        if ($status !== null && $commentManager->setStatus($commentId, $status)) {
            $labels = ['published' => 'publié', 'hidden' => 'masqué', 'pending' => 'remis en attente'];
            adminRedirect($filterQuery, 'success', "L'avis a été " . $labels[$status] . ".");
        }
        adminRedirect($filterQuery, 'error', "Action impossible sur cet avis.");
    }

    $statusFilter = ['en-attente' => 'pending', 'publies' => 'published', 'masques' => 'hidden'][$filter] ?? null;
    $comments = $commentManager->getAllForAdmin($statusFilter);
    $pageTitle = 'Avis';
    require RACINE_PATH . '/view/admin/avis.php';
}

// ======================================================================
// MESSAGES DE CONTACT
// ======================================================================
elseif ($section === 'messages') {
    if (!$messagesReady) {
        $messages = [];
        $selected = null;
    } else {
        if ($isPost) {
            $messageId = (int) ($_POST['message_id'] ?? 0);
            $do = $_POST['do'] ?? '';
            if ($do === 'delete') {
                $contactManager->deleteMessage($messageId);
                adminRedirect('&section=messages', 'success', "Le message a été supprimé.");
            }
            if ($do === 'unread') {
                $contactManager->setRead($messageId, false);
                adminRedirect('&section=messages', 'success', "Le message est marqué comme non lu.");
            }
            adminRedirect('&section=messages', 'error', "Action impossible sur ce message.");
        }
        $messages = $contactManager->getAll();
        $selected = $id ? $contactManager->getById($id) : ($messages[0] ?? null);
        if ($selected !== null && !(int) $selected['is_read']) {
            // ouvrir un message le marque comme lu
            $contactManager->setRead((int) $selected['id'], true);
            $unreadMessages = max(0, $unreadMessages - 1);
            foreach ($messages as &$message) {
                if ((int) $message['id'] === (int) $selected['id']) {
                    $message['is_read'] = 1;
                }
            }
            unset($message);
        }
    }
    $pageTitle = 'Messages';
    require RACINE_PATH . '/view/admin/messages.php';
}

// ======================================================================
// UTILISATEURS
// ======================================================================
elseif ($section === 'utilisateurs') {
    if ($isPost) {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $target = $userManager->getById($userId);
        $do = $_POST['do'] ?? '';
        if ($target === null) {
            adminRedirect('&section=utilisateurs', 'error', "Ce compte n'existe plus.");
        }
        if ($userId === $myId) {
            adminRedirect('&section=utilisateurs', 'error', "Vous ne pouvez pas modifier ou supprimer votre propre compte ici.");
        }
        if ($do === 'role') {
            $role = $_POST['role'] ?? '';
            if ($target['role'] === 'admin' && $role !== 'admin' && $userManager->countAdmins() <= 1) {
                adminRedirect('&section=utilisateurs', 'error', "Il doit toujours rester au moins un administrateur.");
            }
            if ($userManager->setRole($userId, $role)) {
                adminRedirect('&section=utilisateurs', 'success',
                    $target['username'] . ' est maintenant ' . ($role === 'admin' ? 'administrateur' : 'membre') . '.');
            }
        }
        if ($do === 'delete') {
            if ((int) $target['recipes_count'] > 0) {
                adminRedirect('&section=utilisateurs', 'error',
                    $target['username'] . " a créé des recettes : ce compte ne peut pas être supprimé.");
            }
            if ($target['role'] === 'admin' && $userManager->countAdmins() <= 1) {
                adminRedirect('&section=utilisateurs', 'error', "Il doit toujours rester au moins un administrateur.");
            }
            $userManager->deleteUser($userId);
            adminRedirect('&section=utilisateurs', 'success', "Le compte de " . $target['username'] . " a été supprimé.");
        }
        adminRedirect('&section=utilisateurs', 'error', "Action impossible sur ce compte.");
    }
    $users = $userManager->getAllWithStats();
    $pageTitle = 'Utilisateurs';
    require RACINE_PATH . '/view/admin/utilisateurs.php';
}

else {
    adminRedirect('');
}
