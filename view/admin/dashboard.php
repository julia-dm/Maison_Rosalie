<?php
require __DIR__ . '/inc/top.php';
$statusLabels = ['pending' => 'En attente', 'published' => 'Publié', 'hidden' => 'Masqué'];
?>
<header class="admin-head">
  <div>
    <h1>Bonjour <?= h(ucfirst($me['username'])) ?></h1>
    <p class="admin-sub">Voici ce qui se passe sur Maison Rosalie.</p>
  </div>
  <a class="admin-btn" href="?pg=admin&section=recettes&action=new">+ Nouvelle recette</a>
</header>

<?php if (!$messagesReady): ?>
  <div class="admin-note" role="alert">
    La table des messages n'est pas encore à jour : importez <code>data/migration_admin.sql</code> dans phpMyAdmin
    pour activer la section « Messages ».
  </div>
<?php endif; ?>

<section class="admin-stats" aria-label="Chiffres clés">
  <a class="admin-stat" href="?pg=admin&section=recettes">
    <span class="admin-stat-num"><?= $stats['recipes'] ?></span>
    <span class="admin-stat-label">Recettes</span>
  </a>
  <a class="admin-stat<?= $stats['pending'] ? ' admin-stat--alert' : '' ?>" href="?pg=admin&section=avis&statut=en-attente">
    <span class="admin-stat-num"><?= $stats['pending'] ?></span>
    <span class="admin-stat-label">Avis en attente</span>
  </a>
  <a class="admin-stat<?= $stats['unread'] ? ' admin-stat--alert' : '' ?>" href="?pg=admin&section=messages">
    <span class="admin-stat-num"><?= $stats['unread'] ?></span>
    <span class="admin-stat-label">Messages non lus</span>
  </a>
  <a class="admin-stat" href="?pg=admin&section=utilisateurs">
    <span class="admin-stat-num"><?= $stats['users'] ?></span>
    <span class="admin-stat-label">Membres</span>
  </a>
</section>

<div class="admin-grid-2">
  <section class="admin-card">
    <div class="admin-card-head">
      <h2>Les mieux notées</h2>
      <a href="?pg=admin&section=recettes">Toutes les recettes</a>
    </div>
    <?php if (empty($topRecipes)): ?>
      <p class="admin-empty">Aucune recette pour l'instant.</p>
    <?php else: ?>
      <ol class="admin-rank">
        <?php foreach ($topRecipes as $i => $recipe): ?>
          <li>
            <span class="admin-rank-num"><?= $i + 1 ?></span>
            <img src="images/cards/<?= h($recipe->getMainImage()) ?>" alt="" loading="lazy">
            <a href="?pg=admin&section=recettes&action=edit&id=<?= (int) $recipe->getId() ?>"><?= h($recipe->getTitle()) ?></a>
            <span class="admin-rating">
              <?php if ($recipe->getRatingsCount() > 0): ?>
                ★ <?= h(number_format((float) $recipe->getAverageRating(), 1, ',', '')) ?>
                <small>(<?= (int) $recipe->getRatingsCount() ?>)</small>
              <?php else: ?>
                <small>pas encore notée</small>
              <?php endif; ?>
            </span>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </section>

  <section class="admin-card">
    <div class="admin-card-head">
      <h2><?= $stats['pending'] ? 'Avis à modérer' : 'Derniers avis' ?></h2>
      <a href="?pg=admin&section=avis">Tous les avis</a>
    </div>
    <?php if (empty($latestComments)): ?>
      <p class="admin-empty">Aucun avis pour l'instant.</p>
    <?php else: ?>
      <ul class="admin-feed">
        <?php foreach ($latestComments as $comment): ?>
          <li>
            <p class="admin-feed-meta">
              <strong><?= h(ucfirst($comment['username'])) ?></strong> sur
              <em><?= h($comment['recipe_title']) ?></em>
              <?php if ($comment['rating']): ?><span class="admin-rating">★ <?= (int) $comment['rating'] ?></span><?php endif; ?>
              <span class="admin-pill admin-pill--<?= h($comment['status']) ?>"><?= $statusLabels[$comment['status']] ?? h($comment['status']) ?></span>
            </p>
            <p class="admin-feed-text"><?= h(mb_strimwidth($comment['message'], 0, 140, '…')) ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
</div>

<?php require __DIR__ . '/inc/bottom.php'; ?>
