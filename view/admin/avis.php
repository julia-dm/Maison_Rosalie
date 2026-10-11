<?php
require __DIR__ . '/inc/top.php';
$filters = [
    'tous' => ['Tous', $commentCounts['all']],
    'en-attente' => ['En attente', $commentCounts['pending']],
    'publies' => ['Publiés', $commentCounts['published']],
    'masques' => ['Masqués', $commentCounts['hidden']],
];
$statusLabels = ['pending' => 'En attente', 'published' => 'Publié', 'hidden' => 'Masqué'];
$action = '?pg=admin&section=avis' . ($filter !== 'tous' ? '&statut=' . urlencode($filter) : '');
?>

<header class="admin-head">
  <div>
    <h1>Avis</h1>
    <p class="admin-sub">Seuls les avis publiés apparaissent sur les pages des recettes.</p>
  </div>
</header>

<nav class="admin-tabs admin-tabs--pills" aria-label="Filtrer les avis">
  <?php foreach ($filters as $key => [$label, $count]): ?>
    <a class="admin-tab<?= $filter === $key ? ' is-active' : '' ?>" href="?pg=admin&section=avis<?= $key !== 'tous' ? '&statut=' . $key : '' ?>"
       <?= $filter === $key ? 'aria-current="page"' : '' ?>><?= $label ?> <small>(<?= $count ?>)</small></a>
  <?php endforeach; ?>
</nav>

<?php if (empty($comments)): ?>
  <div class="admin-card admin-empty-state"><p>Aucun avis ici.</p></div>
<?php else: ?>
  <ul class="admin-reviews">
    <?php foreach ($comments as $comment):
        $date = date_create($comment['created_at']); ?>
      <li class="admin-card admin-review">
        <div class="admin-review-head">
          <span class="admin-avatar" aria-hidden="true"><?= h(mb_strtoupper(mb_substr($comment['username'], 0, 1))) ?></span>
          <div>
            <p><strong><?= h(ucfirst($comment['username'])) ?></strong>
              sur <a href="?page=recetteDetails&slug=<?= urlencode($comment['recipe_slug']) ?>#avis" target="_blank" rel="noopener"><?= h($comment['recipe_title']) ?></a></p>
            <p class="admin-review-meta">
              <?php if ($comment['rating']): ?>
                <span class="admin-stars" aria-label="<?= (int) $comment['rating'] ?> sur 5"><?= str_repeat('★', (int) $comment['rating']) ?><span><?= str_repeat('★', 5 - (int) $comment['rating']) ?></span></span>
              <?php endif; ?>
              <?php if ($date): ?><time datetime="<?= $date->format('c') ?>"><?= $date->format('d/m/Y à H\hi') ?></time><?php endif; ?>
            </p>
          </div>
          <span class="admin-pill admin-pill--<?= h($comment['status']) ?>"><?= $statusLabels[$comment['status']] ?? h($comment['status']) ?></span>
        </div>

        <p class="admin-review-text"><?= nl2br(h($comment['message'])) ?></p>

        <div class="admin-review-actions">
          <?php foreach (['publish' => ['Publier', 'published'], 'hide' => ['Masquer', 'hidden'], 'pending' => ['Remettre en attente', 'pending']] as $do => [$label, $status]):
              if ($comment['status'] === $status || ($do === 'pending' && $comment['status'] !== 'hidden')) continue; ?>
            <form method="post" action="<?= $action ?>">
              <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
              <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
              <button class="admin-btn admin-btn--small<?= $do === 'publish' ? '' : ' admin-btn--ghost' ?>" name="do" value="<?= $do ?>"><?= $label ?></button>
            </form>
          <?php endforeach; ?>
          <form method="post" action="<?= $action ?>"
                data-confirm="L'avis de <?= h(ucfirst($comment['username'])) ?> sera supprimé<?= $comment['rating'] ? ', ainsi que sa note' : '' ?>. Pour le retirer du site sans le perdre, choisissez plutôt « Masquer ».">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
            <button class="admin-btn admin-btn--small admin-btn--danger-ghost" name="do" value="delete">Supprimer</button>
          </form>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<?php require __DIR__ . '/inc/bottom.php'; ?>
