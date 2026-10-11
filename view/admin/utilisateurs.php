<?php require __DIR__ . '/inc/top.php'; ?>

<header class="admin-head">
  <div>
    <h1>Utilisateurs</h1>
    <p class="admin-sub"><?= count($users) ?> compte<?= count($users) > 1 ? 's' : '' ?> · les administrateurs ont accès à cette page</p>
  </div>
</header>

<div class="admin-card admin-table-wrap">
  <table class="admin-table">
    <thead>
      <tr>
        <th scope="col">Membre</th>
        <th scope="col">Inscrit le</th>
        <th scope="col">Activité</th>
        <th scope="col">Rôle</th>
        <th scope="col"><span class="visually-hidden">Actions</span></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user):
          $isMe = (int) $user['id'] === $myId;
          $date = date_create($user['created_at']); ?>
        <tr>
          <td data-label="Membre">
            <div class="admin-recipe-cell">
              <span class="admin-avatar" aria-hidden="true"><?= h(mb_strtoupper(mb_substr($user['username'], 0, 1))) ?></span>
              <div>
                <span class="admin-strong"><?= h(ucfirst($user['username'])) ?><?= $isMe ? ' <small>(vous)</small>' : '' ?></span>
                <small><?= h($user['email']) ?></small>
              </div>
            </div>
          </td>
          <td data-label="Inscrit le"><?= $date ? $date->format('d/m/Y') : '—' ?></td>
          <td data-label="Activité">
            <?= (int) $user['comments_count'] ?> avis<?php if ($user['recipes_count'] > 0): ?> · <?= (int) $user['recipes_count'] ?> recette<?= $user['recipes_count'] > 1 ? 's' : '' ?><?php endif; ?>
          </td>
          <td data-label="Rôle">
            <?php if ($isMe): ?>
              <span class="admin-pill admin-pill--admin">Admin</span>
            <?php else: ?>
              <form method="post" action="?pg=admin&section=utilisateurs" class="admin-role-form"
                    data-confirm-role="<?= h(ucfirst($user['username'])) ?>">
                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                <input type="hidden" name="do" value="role">
                <label class="visually-hidden" for="role-<?= (int) $user['id'] ?>">Rôle de <?= h($user['username']) ?></label>
                <select id="role-<?= (int) $user['id'] ?>" name="role">
                  <option value="user" <?= $user['role'] !== 'admin' ? 'selected' : '' ?>>Membre</option>
                  <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <noscript><button class="admin-btn admin-btn--small">OK</button></noscript>
              </form>
            <?php endif; ?>
          </td>
          <td class="admin-actions">
            <?php if (!$isMe): ?>
              <?php if ($user['recipes_count'] > 0): ?>
                <span class="admin-icon-btn is-disabled" title="Auteur de recettes : suppression impossible" aria-label="Suppression impossible : <?= h($user['username']) ?> a créé des recettes">
                  <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                </span>
              <?php else: ?>
                <form method="post" action="?pg=admin&section=utilisateurs"
                      data-confirm="Le compte de <?= h(ucfirst($user['username'])) ?> sera supprimé avec ses <?= (int) $user['comments_count'] ?> avis et ses notes.">
                  <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                  <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                  <button class="admin-icon-btn admin-icon-btn--danger" name="do" value="delete" title="Supprimer" aria-label="Supprimer le compte de <?= h($user['username']) ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                  </button>
                </form>
              <?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/inc/bottom.php'; ?>
