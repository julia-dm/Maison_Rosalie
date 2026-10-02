<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="css/styles.css">
  <!-- Google-fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Abyssinica+SIL&display=swap" rel="stylesheet">


  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&display=swap"
    rel="stylesheet">

  <!-- ============================ -->

  <title>Document</title>
</head>

<body>
  <header class="header">
    <div class="container">

      <div class="logo-wrap">
        <a class="logo" href="/"><img src="images/logo/horiz-logo.png" alt="Maison Rasolie"></a>
      </div>

      <nav class="navbar">
        <div class="nav-top">
          <div class="nav-brand">
            <div class="login-wrap">
            <button class="login-open" type="button" aria-label="Connexion" aria-expanded="false" aria-controls="login-panel">
              <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="1.25em" height="1em" viewBox="0 0 640 512">
                <path
                  d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128m89.6 32h-16.7c-22.2 10.2-46.9 16-72.9 16s-50.6-5.8-72.9-16h-16.7C60.2 288 0 348.2 0 422.4V464c0 26.5 21.5 48 48 48h274.9c-2.4-6.8-3.4-14-2.6-21.3l6.8-60.9 1.2-11.1 7.9-7.9 77.3-77.3c-24.5-27.7-60-45.5-99.9-45.5m45.3 145.3-6.8 61c-1.1 10.2 7.5 18.8 17.6 17.6l60.9-6.8 137.9-137.9-71.7-71.7zM633 268.9 595.1 231c-9.3-9.3-24.5-9.3-33.8 0l-37.8 37.8-4.1 4.1 71.8 71.7 41.8-41.8c9.3-9.4 9.3-24.5 0-33.9" />
              </svg>
            </button>

            <!-- Panneau connexion -->
            <div class="login-panel" id="login-panel">
              <p class="login-title">Connexion</p>
              <form class="login-form" method="post" action="">
                <label for="login-email">E-mail</label>
                <input type="email" id="login-email" name="email" required>
                <label for="login-password">Mot de passe</label>
                <input type="password" id="login-password" name="password" required>

                <div class="login-options">
                  <label class="login-remember">
                    <input type="checkbox" name="remember"> Enregistrer
                  </label>
                  <a href="#">Mot De Passe Oublié</a>
                </div>

                <button class="login-submit" type="submit">Login</button>
              </form>

              <div class="login-social">
                <a href="#">
                  <img src="images/icons/google.svg" alt="" width="16" height="16">
                  Google
                </a>
                <a href="#">
                  <img src="images/icons/facebook.svg" alt="" width="16" height="16">
                  Facebook
                </a>
              </div>

              <p class="login-or">ou</p>
              <a class="login-register" href="?page=inscription">Crée un compte</a>
            </div>
            </div>
            <a href="">
              <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                <path
                  d="M8 2h8a3 3 0 0 1 3 3v14a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3V5a3 3 0 0 1 3-3zm0 2a1 1 0 0 0-1 1v3h4V4H8zm5 0v4h4V5a1 1 0 0 0-1-1h-3zM7 10v4h4v-4H7zm6 0v4h4v-4h-4zM7 16v3a1 1 0 0 0 1 1h3v-4H7zm6 0v4h3a1 1 0 0 0 1-1v-3h-4z" />
              </svg>
            </a>
          </div>

          <button class="menu-toggle" type="button" aria-label="Open menu" aria-expanded="false"
            aria-controls="main-menu">

            <span></span>
            <span></span>
            <span></span>
          </button>
        </div>
        <ul class="nav-list" id="main-menu">
          <li class="nav-item">
            <a class="nav-link" href="?page=accueil">Accueil</a>
          </li>
        
          <li class="nav_item"><a class="nav-link" href="?page=recettes">Recettes</a></li>
          </li>
          <li class="nav_item"><a class="nav-link" href="?page=apropos">À propos</a></li>

          <li class="nav_item"><a class="nav-link" href="?page=contact">Contact</a></li>
        </ul>
      </nav>
    </div>
  </header>