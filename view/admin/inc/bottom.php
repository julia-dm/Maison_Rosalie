</main>

<!-- fenêtre de confirmation, utilisée par tous les formulaires qui ont data-confirm -->
<dialog class="admin-dialog" id="confirm-dialog" aria-labelledby="confirm-title">
  <form method="dialog">
    <div class="admin-dialog-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
    </div>
    <h2 id="confirm-title">Êtes-vous sûr ?</h2>
    <p id="confirm-text"></p>
    <div class="admin-dialog-actions">
      <button class="admin-btn admin-btn--ghost" value="cancel" autofocus>Annuler</button>
      <button class="admin-btn admin-btn--danger" value="ok" id="confirm-ok">Supprimer</button>
    </div>
  </form>
</dialog>

<script src="script/admin.js"></script>
</body>
</html>
