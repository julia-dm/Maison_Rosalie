<?php require __DIR__ . '/inc/top.php'; ?>

<header class="admin-head">
  <div>
    <h1>Recettes</h1>
    <p class="admin-sub"><?= count($recipes) ?> recette<?= count($recipes) > 1 ? 's' : '' ?> sur le site</p>
  </div>
  <a class="admin-btn" href="?pg=admin&section=recettes&action=new">+ Nouvelle recette</a>
</header>

<?php if (empty($recipes)): ?>
  <div class="admin-card admin-empty-state">
    <p>Aucune recette pour l'instant.</p>
    <a class="admin-btn" href="?pg=admin&section=recettes&action=new">Créer la première recette</a>
  </div>
<?php else: ?>
  <div class="admin-card admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th scope="col">Recette</th>
          <th scope="col">Difficulté</th>
          <th scope="col">Note</th>
          <th scope="col">Contenu</th>
          <th scope="col"><span class="visually-hidden">Actions</span></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recipes as $recipe):
            $incomplete = !$recipe['ingredients_count'] || !$recipe['steps_count']; ?>
          <tr>
            <td data-label="Recette">
              <div class="admin-recipe-cell">
                <img src="images/cards/<?= h($recipe['main_image']) ?>" alt="" loading="lazy">
                <div>
                  <a class="admin-strong" href="?pg=admin&section=recettes&action=edit&id=<?= (int) $recipe['id'] ?>"><?= h($recipe['title']) ?></a>
                  <small>/<?= h($recipe['slug']) ?></small>
                </div>
              </div>
            </td>
            <td data-label="Difficulté"><?= h($difficulties[$recipe['difficulty']] ?? $recipe['difficulty']) ?></td>
            <td data-label="Note">
              <?php if ($recipe['ratings_count'] > 0): ?>
                <span><span class="admin-rating">★ <?= h(number_format((float) $recipe['average_rating'], 1, ',', '')) ?></span>
                <small>(<?= (int) $recipe['ratings_count'] ?>)</small></span>
              <?php else: ?><small>—</small><?php endif; ?>
            </td>
            <td data-label="Contenu">
              <span><?= (int) $recipe['ingredients_count'] ?> ingr. · <?= (int) $recipe['steps_count'] ?> étapes
              <?php if ($incomplete): ?><span class="admin-pill admin-pill--pending">À compléter</span><?php endif; ?></span>
            </td>
            <td class="admin-actions">
              <a class="admin-icon-btn" href="?page=recetteDetails&slug=<?= urlencode($recipe['slug']) ?>" target="_blank" rel="noopener" title="Voir sur le site" aria-label="Voir « <?= h($recipe['title']) ?> » sur le site">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
              </a>
              <a class="admin-icon-btn" href="?pg=admin&section=recettes&action=edit&id=<?= (int) $recipe['id'] ?>" title="Modifier" aria-label="Modifier « <?= h($recipe['title']) ?> »">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
              </a>
              <form method="post" action="?pg=admin&section=recettes&action=delete&id=<?= (int) $recipe['id'] ?>"
                    data-confirm="La recette « <?= h($recipe['title']) ?> » sera supprimée avec ses ingrédients, ses étapes et ses <?= (int) $recipe['ratings_count'] ?> avis. Cette action est définitive.">
                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                <button type="submit" class="admin-icon-btn admin-icon-btn--danger" title="Supprimer" aria-label="Supprimer « <?= h($recipe['title']) ?> »">
                  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/inc/bottom.php'; ?>
