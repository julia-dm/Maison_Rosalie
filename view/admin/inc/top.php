<?php
// view/admin/inc/top.php : début de toutes les pages admin (barre latérale + titre + message flash)
$nav = [
    'dashboard' => ['Tableau de bord', '<path d="M3 3h7v9H3zM14 3h7v5h-7zM14 12h7v9h-7zM3 16h7v5H3z"/>', 0],
    'recettes' => ['Recettes', '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5z"/><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5"/>', 0],
    'avis' => ['Avis', '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>', $commentCounts['pending']],
    'messages' => ['Messages', '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>', $unreadMessages],
    'utilisateurs' => ['Utilisateurs', '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>', 0],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&family=Josefin+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/admin.css">
  <title><?= h($pageTitle) ?> – Administration Maison Rosalie</title>
</head>
<body class="admin">

<aside class="admin-side">
  <a class="admin-brand" href="?pg=admin">
    <img src="images/figma/logo-mr.png" alt="Maison Rosalie">
    <span>Administration</span>
  </a>

  <nav class="admin-nav" aria-label="Administration">
    <?php foreach ($nav as $key => [$label, $icon, $badge]): ?>
      <a href="?pg=admin<?= $key === 'dashboard' ? '' : '&section=' . $key ?>"
         class="admin-nav-link<?= $section === $key ? ' is-active' : '' ?>"
         <?= $section === $key ? 'aria-current="page"' : '' ?>>
        <svg viewBox="0 0 24 24" aria-hidden="true"><?= $icon ?></svg>
        <span><?= $label ?></span>
        <?php if ($badge > 0): ?><b class="admin-badge" title="<?= $badge ?> à traiter"><?= $badge ?></b><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="admin-side-foot">
    <a class="admin-site-link" href="?page=accueil">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/></svg>
      Voir le site
    </a>
    <div class="admin-me">
      <span class="admin-avatar" aria-hidden="true"><?= h(mb_strtoupper(mb_substr($me['username'], 0, 1))) ?></span>
      <span class="admin-me-name"><?= h(ucfirst($me['username'])) ?></span>
      <form method="post" action="?page=logout">
        <button type="submit" class="admin-icon-btn" aria-label="Déconnexion" title="Déconnexion">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m16 17 5-5-5-5M21 12H9M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/></svg>
        </button>
      </form>
    </div>
  </div>
</aside>

<main class="admin-main">
  <?php if ($flash): ?>
    <div class="admin-toast admin-toast--<?= $flash['type'] === 'success' ? 'success' : 'error' ?>" role="<?= $flash['type'] === 'success' ? 'status' : 'alert' ?>">
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <?php if ($flash['type'] === 'success'): ?><path d="M20 6 9 17l-5-5"/>
        <?php else: ?><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/><?php endif; ?>
      </svg>
      <p><?= h($flash['text']) ?></p>
      <button type="button" class="admin-toast-close" aria-label="Fermer">&times;</button>
    </div>
  <?php endif; ?>
