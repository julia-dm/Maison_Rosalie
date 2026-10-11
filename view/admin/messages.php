<?php require __DIR__ . '/inc/top.php'; ?>

<header class="admin-head">
  <div>
    <h1>Messages</h1>
    <p class="admin-sub">Messages envoyés depuis la page Contact<?= $unreadMessages ? ' · ' . $unreadMessages . ' non lu' . ($unreadMessages > 1 ? 's' : '') : '' ?></p>
  </div>
</header>

<?php if (!$messagesReady): ?>
  <div class="admin-note" role="alert">
    Pour activer cette section, importez <code>data/migration_admin.sql</code> dans phpMyAdmin
    (il ajoute la colonne <code>is_read</code> à la table <code>contact_messages</code>).
  </div>
<?php elseif (empty($messages)): ?>
  <div class="admin-card admin-empty-state"><p>Aucun message pour l'instant.</p></div>
<?php else: ?>
  <div class="admin-inbox">
    <ul class="admin-card admin-inbox-list" aria-label="Liste des messages">
      <?php foreach ($messages as $message):
          $isSelected = $selected && (int) $selected['id'] === (int) $message['id'];
          $date = date_create($message['created_at']); ?>
        <li>
          <a href="?pg=admin&section=messages&id=<?= (int) $message['id'] ?>#lecture"
             class="admin-inbox-item<?= $isSelected ? ' is-active' : '' ?><?= !(int) $message['is_read'] ? ' is-unread' : '' ?>"
             <?= $isSelected ? 'aria-current="true"' : '' ?>>
            <span class="admin-inbox-from">
              <?php if (!(int) $message['is_read']): ?><span class="admin-dot" title="Non lu"></span><?php endif; ?>
              <?= h($message['name']) ?>
            </span>
            <?php if ($date): ?><time datetime="<?= $date->format('c') ?>"><?= $date->format('d/m/Y') ?></time><?php endif; ?>
            <span class="admin-inbox-subject"><?= h($message['subject']) ?></span>
            <span class="admin-inbox-preview"><?= h(mb_strimwidth($message['message'], 0, 90, '…')) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <?php if ($selected):
        $date = date_create($selected['created_at']); ?>
      <article class="admin-card admin-inbox-read" id="lecture">
        <header>
          <h2><?= h($selected['subject'] ?: 'Message') ?></h2>
          <p><strong><?= h($selected['name']) ?></strong></p>
          <p class="admin-sub">
            <a href="mailto:<?= h($selected['email']) ?>"><?= h($selected['email']) ?></a>
            <?php if ($date): ?> · <time datetime="<?= $date->format('c') ?>"><?= $date->format('d/m/Y à H\hi') ?></time><?php endif; ?>
          </p>
        </header>
        <div class="admin-inbox-body"><?= nl2br(h($selected['message'])) ?></div>
        <div class="admin-review-actions">
          <a class="admin-btn admin-btn--small" href="mailto:<?= h($selected['email']) ?>?subject=<?= rawurlencode('Re : ' . ($selected['subject'] ?: 'votre message à Maison Rosalie')) ?>">Répondre par e-mail</a>
          <form method="post" action="?pg=admin&section=messages">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="message_id" value="<?= (int) $selected['id'] ?>">
            <button class="admin-btn admin-btn--small admin-btn--ghost" name="do" value="unread">Marquer comme non lu</button>
          </form>
          <form method="post" action="?pg=admin&section=messages"
                data-confirm="Le message de <?= h($selected['name']) ?> sera supprimé définitivement.">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="message_id" value="<?= (int) $selected['id'] ?>">
            <button class="admin-btn admin-btn--small admin-btn--danger-ghost" name="do" value="delete">Supprimer</button>
          </form>
        </div>
      </article>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/inc/bottom.php'; ?>
