<?php
require_once __DIR__ . "/inc/header.php";
?>

<main class="register">
    <h1 class="register-title">Créer un compte</h1>

    <?php if ($success): ?>
        <p class="register-success">Votre compte a bien été créé.</p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <ul class="register-errors">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form class="register-form" method="post" action="?page=inscription">
        <label for="register-username">Nom d'utilisateur</label>
        <input type="text" id="register-username" name="username" maxlength="50"
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>

        <label for="register-email">E-mail</label>
        <input type="email" id="register-email" name="email" maxlength="254"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

        <label for="register-password">Mot de passe</label>
        <input type="password" id="register-password" name="password" required>

        <label for="register-confirm">Confirmer le mot de passe</label>
        <input type="password" id="register-confirm" name="password_confirm" required>

        <button class="register-submit" type="submit">Créer mon compte</button>
    </form>
</main>

<?php
require_once __DIR__ . "/inc/footer.php";
?>
